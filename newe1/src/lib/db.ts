import { Pool, PoolConfig } from 'pg';

// Singleton pattern for database pool
let pool: Pool | null = null;

function getPool(): Pool {
  if (!pool) {
    const config: PoolConfig = {
      host: process.env.POSTGRES_HOST || '192.168.40.41',
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB || 'newe1',
      user: process.env.POSTGRES_USER || 'newe1',
      password: process.env.POSTGRES_PASSWORD || 'newe1pass',
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 2000,
    };
    pool = new Pool(config);
  }
  return pool;
}

export default getPool;

export interface Salon {
  id: number;
  name: string;
  city: string;
  region: string;
  address: string;
  email: string;
  phone: string;
  working_hours: string;
  latitude: number | null;
  longitude: number | null;
  external_code: string;
  slug: string;
  created_at: Date;
}

export async function getSalons(city?: string): Promise<Salon[]> {
  const db = getPool();
  let query = 'SELECT * FROM salons ORDER BY region, city, name';
  const params: string[] = [];

  if (city) {
    query = 'SELECT * FROM salons WHERE city = $1 ORDER BY name';
    params.push(city);
  }

  const result = await db.query(query, params);
  return result.rows;
}

export async function getCities(): Promise<{ city: string; region: string; count: number }[]> {
  const db = getPool();
  const result = await db.query(`
    SELECT city, region, COUNT(*) as count
    FROM salons
    GROUP BY city, region
    ORDER BY region, city
  `);
  return result.rows;
}

export async function getRegions(): Promise<{ region: string; count: number }[]> {
  const db = getPool();
  const result = await db.query(`
    SELECT region, COUNT(*) as count
    FROM salons
    GROUP BY region
    ORDER BY region
  `);
  return result.rows;
}
