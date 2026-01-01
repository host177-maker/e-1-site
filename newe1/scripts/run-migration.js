#!/usr/bin/env node

/**
 * Скрипт для выполнения миграций PostgreSQL через Node.js
 * Использование: node scripts/run-migration.js [номер_миграции]
 * Пример: node scripts/run-migration.js 007
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

  // Поддержка DATABASE_URL или отдельных POSTGRES_* переменных
  let pool;
  if (process.env.DATABASE_URL) {
    const maskedUrl = process.env.DATABASE_URL.replace(/:([^:@]+)@/, ':***@');
    console.log('Подключение к:', maskedUrl);
    pool = new Pool({ connectionString: process.env.DATABASE_URL });
  } else if (process.env.POSTGRES_HOST) {
    console.log(`Подключение к: ${process.env.POSTGRES_HOST}:${process.env.POSTGRES_PORT}/${process.env.POSTGRES_DB}`);
    pool = new Pool({
      host: process.env.POSTGRES_HOST,
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB,
      user: process.env.POSTGRES_USER,
      password: process.env.POSTGRES_PASSWORD,
    });
  } else {
    console.error('❌ Не найдены настройки базы данных (DATABASE_URL или POSTGRES_*)');
    process.exit(1);
  }

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
    if (error.hint) console.error('  Подсказка:', error.hint);
    if (error.position) console.error('  Позиция:', error.position);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

runMigration();
