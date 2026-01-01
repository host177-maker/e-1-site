import { Pool, PoolConfig, QueryResult, QueryResultRow } from 'pg';

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
      max: 20,                      // Увеличили пул
      min: 2,                       // Минимум соединений
      idleTimeoutMillis: 60000,     // 60 секунд простоя
      connectionTimeoutMillis: 10000, // 10 секунд на подключение
      keepAlive: true,              // Поддерживать соединения
      keepAliveInitialDelayMillis: 10000,
    };
    pool = new Pool(config);

    // Обработка ошибок пула
    pool.on('error', (err) => {
      console.error('Unexpected database pool error:', err);
    });

    // Попытка установить соединение при создании пула
    pool.connect().then(client => {
      console.log('Database pool initialized successfully');
      client.release();
    }).catch(err => {
      console.error('Failed to initialize database pool:', err.message);
    });
  }
  return pool;
}

export default getPool;

// Выполнение запроса с повторными попытками
export async function queryWithRetry<T extends QueryResultRow = QueryResultRow>(
  sql: string,
  params?: unknown[],
  maxRetries = 3
): Promise<QueryResult<T>> {
  const pool = getPool();
  let lastError: Error | null = null;

  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      return await pool.query<T>(sql, params);
    } catch (err) {
      lastError = err as Error;
      const isConnectionError = lastError.message.includes('Connection terminated') ||
                                 lastError.message.includes('timeout') ||
                                 lastError.message.includes('ECONNREFUSED');

      if (isConnectionError && attempt < maxRetries) {
        console.warn(`Database query attempt ${attempt} failed, retrying...`, lastError.message);
        // Ждём перед повторной попыткой (exponential backoff)
        await new Promise(resolve => setTimeout(resolve, attempt * 500));
        continue;
      }
      throw lastError;
    }
  }

  throw lastError;
}

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
