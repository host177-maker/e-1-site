#!/usr/bin/env node

/**
 * Скрипт для применения миграций PostgreSQL
 * Использование: node scripts/apply-migration.js [файл_миграции]
 * Пример: node scripts/apply-migration.js 006_catalog.sql
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
  console.error('❌ POSTGRES_HOST не установлен');
  process.exit(1);
}

const pool = new Pool({
  host: process.env.POSTGRES_HOST,
  port: parseInt(process.env.POSTGRES_PORT || '5432'),
  database: process.env.POSTGRES_DB,
  user: process.env.POSTGRES_USER,
  password: process.env.POSTGRES_PASSWORD,
});

async function applyMigration() {
  const migrationFile = process.argv[2] || '006_catalog.sql';
  const migrationPath = path.join(__dirname, '..', 'src', 'lib', 'migrations', migrationFile);

  console.log(`Применение миграции: ${migrationFile}`);
  console.log(`Подключение к: ${process.env.POSTGRES_HOST}:${process.env.POSTGRES_PORT}/${process.env.POSTGRES_DB}`);

  try {
    const sql = fs.readFileSync(migrationPath, 'utf8');
    await pool.query(sql);
    console.log('✅ Миграция применена успешно!');
  } catch (error) {
    console.error('❌ Ошибка миграции:', error.message);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

applyMigration();
