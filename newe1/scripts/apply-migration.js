const { Pool } = require('pg');
const fs = require('fs');
const path = require('path');

const pool = new Pool({
  host: process.env.POSTGRES_HOST || '85.175.99.16',
  port: parseInt(process.env.POSTGRES_PORT || '8732'),
  database: process.env.POSTGRES_DB || 'e1_site',
  user: process.env.POSTGRES_USER || 'e1_site',
  password: process.env.POSTGRES_PASSWORD || 'Kd10CvTQguAD',
});

async function applyMigration() {
  const migrationFile = process.argv[2] || '006_catalog.sql';
  const migrationPath = path.join(__dirname, '..', 'src', 'lib', 'migrations', migrationFile);

  console.log(`Applying migration: ${migrationFile}`);

  try {
    const sql = fs.readFileSync(migrationPath, 'utf8');
    await pool.query(sql);
    console.log('Migration applied successfully!');
  } catch (error) {
    console.error('Error applying migration:', error.message);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

applyMigration();
