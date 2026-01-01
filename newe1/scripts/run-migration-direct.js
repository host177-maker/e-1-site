#!/usr/bin/env node

const { Pool } = require('pg');
const fs = require('fs');
const path = require('path');

// Прямые настройки подключения
const dbConfig = {
  host: '85.175.99.16',
  port: 8732,
  database: 'e1_site',
  user: 'e1_site',
  password: 'Kd10CvTQguAD'
};

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
  console.log(`Подключение к: ${dbConfig.host}:${dbConfig.port}/${dbConfig.database}`);
  console.log('---');
  console.log(sql);
  console.log('---');

  const pool = new Pool(dbConfig);

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
