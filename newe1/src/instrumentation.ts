import { Pool } from 'pg';
import * as fs from 'fs';
import * as path from 'path';

async function runMigrations() {
  if (!process.env.POSTGRES_HOST) {
    console.log('Skipping migrations: POSTGRES_HOST not set');
    return;
  }

  const pool = new Pool({
    host: process.env.POSTGRES_HOST,
    port: parseInt(process.env.POSTGRES_PORT || '5432'),
    database: process.env.POSTGRES_DB || 'e1_site',
    user: process.env.POSTGRES_USER || 'e1_site',
    password: process.env.POSTGRES_PASSWORD,
  });

  try {
    // Create migrations tracking table if not exists
    await pool.query(`
      CREATE TABLE IF NOT EXISTS _migrations (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT NOW()
      )
    `);

    // Get list of executed migrations
    const { rows: executed } = await pool.query('SELECT name FROM _migrations');
    const executedNames = new Set(executed.map(r => r.name));

    // Get all migration files
    const migrationsDir = path.join(process.cwd(), 'src/lib/migrations');

    if (!fs.existsSync(migrationsDir)) {
      console.log('Migrations directory not found:', migrationsDir);
      return;
    }

    const files = fs.readdirSync(migrationsDir)
      .filter(f => f.endsWith('.sql'))
      .sort();

    // Run pending migrations
    for (const file of files) {
      if (executedNames.has(file)) {
        continue;
      }

      console.log(`Running migration: ${file}`);
      const sql = fs.readFileSync(path.join(migrationsDir, file), 'utf-8');

      try {
        await pool.query(sql);
        await pool.query('INSERT INTO _migrations (name) VALUES ($1)', [file]);
        console.log(`Migration completed: ${file}`);
      } catch (err) {
        console.error(`Migration failed: ${file}`, err);
        throw err;
      }
    }

    console.log('All migrations completed');
  } catch (err) {
    console.error('Migration error:', err);
  } finally {
    await pool.end();
  }
}

export async function register() {
  // Only run on server side
  if (process.env.NEXT_RUNTIME === 'nodejs') {
    await runMigrations();
  }
}
