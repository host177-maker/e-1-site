import { Pool } from 'pg';

const pool = new Pool({
  host: process.env.POSTGRES_HOST || '192.168.40.41',
  port: parseInt(process.env.POSTGRES_PORT || '5432'),
  database: process.env.POSTGRES_DB || 'newe1',
  user: process.env.POSTGRES_USER || 'newe1',
  password: process.env.POSTGRES_PASSWORD || 'newe1pass',
});

export default pool;

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
  let query = 'SELECT * FROM salons ORDER BY region, city, name';
  const params: string[] = [];

  if (city) {
    query = 'SELECT * FROM salons WHERE city = $1 ORDER BY name';
    params.push(city);
  }

  const result = await pool.query(query, params);
  return result.rows;
}

export async function getCities(): Promise<{ city: string; region: string; count: number }[]> {
  const result = await pool.query(`
    SELECT city, region, COUNT(*) as count
    FROM salons
    GROUP BY city, region
    ORDER BY region, city
  `);
  return result.rows;
}

export async function getRegions(): Promise<{ region: string; count: number }[]> {
  const result = await pool.query(`
    SELECT region, COUNT(*) as count
    FROM salons
    GROUP BY region
    ORDER BY region
  `);
  return result.rows;
}
