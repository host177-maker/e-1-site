import { Pool, PoolConfig } from 'pg';

// Singleton pattern for database pool
let pool: Pool | null = null;

export function getPool(): Pool {
  if (!pool) {
    // Проверяем наличие обязательных переменных окружения
    if (!process.env.POSTGRES_HOST) {
      throw new Error('POSTGRES_HOST environment variable is not set');
    }

    const config: PoolConfig = {
      host: process.env.POSTGRES_HOST,
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB || 'e1_site',
      user: process.env.POSTGRES_USER || 'e1_site',
      password: process.env.POSTGRES_PASSWORD,
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 5000,
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
