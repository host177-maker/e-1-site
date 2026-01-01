#!/usr/bin/env node

/**
 * Скрипт для выполнения миграций PostgreSQL напрямую
 * ВНИМАНИЕ: Этот скрипт использует переменные окружения из .env файла
 *
 * Использование: node scripts/run-migration-direct.js [номер_миграции]
 * Пример: node scripts/run-migration-direct.js 007
 */

const { Pool } = require('pg');
const fs = require('fs');
const path = require('path');

// Загружаем переменные окружения из .env и .env.local
const envFiles = [
  path.join(__dirname, '../.env'),
  path.join(__dirname, '../.env.local')
];

envFiles.forEach(envPath => {
  if (fs.existsSync(envPath)) {
    const envContent = fs.readFileSync(envPath, 'utf8');
    envContent.split('\n').forEach(line => {
      const match = line.match(/^([^#=]+)=(.*)$/);
      if (match) {
        const key = match[1].trim();
        const value = match[2].trim().replace(/^["']|["']$/g, '');
        if (!process.env[key]) {
          process.env[key] = value;
        }
      }
    });
  }
});

// Проверяем наличие обязательных переменных
if (!process.env.POSTGRES_HOST) {
  console.error('❌ POSTGRES_HOST не установлен. Проверьте .env файл.');
  process.exit(1);
}

const migrationsDir = path.join(__dirname, '../src/lib/migrations');

async function runMigration() {
  const migrationNumber = process.argv[2];

  if (!migrationNumber) {
    console.log('Доступные миграции:');
    const files = fs.readdirSync(migrationsDir).filter(f => f.endsWith('.sql'));
    files.forEach(f => console.log(`  - ${f}`));
    console.log('\nИспользование: node scripts/run-migration-direct.js 007');
    process.exit(1);
  }

  const files = fs.readdirSync(migrationsDir);
  const migrationFile = files.find(f => f.startsWith(migrationNumber) && f.endsWith('.sql'));

  if (!migrationFile) {
    console.error(`Миграция ${migrationNumber} не найдена`);
    process.exit(1);
  }

  const sql = fs.readFileSync(path.join(migrationsDir, migrationFile), 'utf8');

  console.log(`Выполняется миграция: ${migrationFile}`);
  console.log(`Подключение к: ${process.env.POSTGRES_HOST}:${process.env.POSTGRES_PORT}/${process.env.POSTGRES_DB}`);
  console.log('---');
  console.log(sql);
  console.log('---');

  const pool = new Pool({
    host: process.env.POSTGRES_HOST,
    port: parseInt(process.env.POSTGRES_PORT || '5432'),
    database: process.env.POSTGRES_DB,
    user: process.env.POSTGRES_USER,
    password: process.env.POSTGRES_PASSWORD,
  });

  try {
    const client = await pool.connect();
    try {
      await client.query(sql);
      console.log('✅ Миграция выполнена успешно!');
    } finally {
      client.release();
    }
  } catch (error) {
    console.error('❌ Ошибка миграции:');
    console.error('  Сообщение:', error.message || 'нет');
    console.error('  Код:', error.code || 'нет');
    console.error('  Детали:', error.detail || 'нет');
    process.exit(1);
  } finally {
    await pool.end();
  }
}

runMigration();
