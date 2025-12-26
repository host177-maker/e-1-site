import { Pool, PoolConfig } from 'pg';

// Singleton pattern for database pool
let pool: Pool | null = null;

export function getPool(): Pool {
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
