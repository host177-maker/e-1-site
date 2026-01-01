#!/usr/bin/env node

/**
 * Скрипт для выполнения миграций PostgreSQL через Node.js
 * Использование: node scripts/run-migration.js [номер_миграции]
 * Пример: node scripts/run-migration.js 007
 */

const { Pool } = require('pg');
const fs = require('fs');
const path = require('path');

// Загружаем переменные окружения из .env.local
const envPath = path.join(__dirname, '../.env.local');
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

// Путь к папке с миграциями
const migrationsDir = path.join(__dirname, '../src/lib/migrations');

async function runMigration() {
  const migrationNumber = process.argv[2];

  if (!migrationNumber) {
    console.log('Доступные миграции:');
    const files = fs.readdirSync(migrationsDir).filter(f => f.endsWith('.sql'));
    files.forEach(f => console.log(`  - ${f}`));
    console.log('\nИспользование: node scripts/run-migration.js 007');
    process.exit(1);
  }

  // Найти файл миграции
  const files = fs.readdirSync(migrationsDir);
  const migrationFile = files.find(f => f.startsWith(migrationNumber) && f.endsWith('.sql'));

  if (!migrationFile) {
    console.error(`Миграция ${migrationNumber} не найдена`);
    process.exit(1);
  }

  const migrationPath = path.join(migrationsDir, migrationFile);
  const sql = fs.readFileSync(migrationPath, 'utf8');

  console.log(`Выполняется миграция: ${migrationFile}`);
  console.log('---');
  console.log(sql);
  console.log('---');

  const pool = new Pool({
    connectionString: process.env.DATABASE_URL,
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
    console.error('❌ Ошибка миграции:', error.message);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

runMigration();
