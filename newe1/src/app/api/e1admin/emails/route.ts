import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';

// Email setting keys
export const EMAIL_KEYS = {
  DIRECTOR: 'director_email',      // Почта для директора (форма обратной связи)
  SALES: 'sales_email',            // Отдел продаж интернет-магазина
  DESIGNERS: 'designers_email',    // Отдел работы с дизайнерами (+ франшиза)
  WHOLESALE: 'wholesale_email',    // Отдел оптовых продаж
  MARKETPLACE: 'marketplace_email', // Отдел по ЧИМ (маркетплейсы)
} as const;

export type EmailKey = typeof EMAIL_KEYS[keyof typeof EMAIL_KEYS];

// Create table if not exists
async function ensureTable() {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS email_settings (
      id SERIAL PRIMARY KEY,
      key VARCHAR(50) UNIQUE NOT NULL,
      email VARCHAR(255) NOT NULL,
      description TEXT,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Insert default values if not exist
  const defaults = [
    { key: EMAIL_KEYS.DIRECTOR, email: '', description: 'Почта сообщений для директора (форма обратной связи в футере)' },
    { key: EMAIL_KEYS.SALES, email: '', description: 'Отдел продаж интернет-магазина (лиды, если сломалась CRM)' },
    { key: EMAIL_KEYS.DESIGNERS, email: '', description: 'Отдел работы с дизайнерами (заявки от дизайнеров и франшизы)' },
    { key: EMAIL_KEYS.WHOLESALE, email: '', description: 'Отдел оптовых продаж (заявки по оптовому сотрудничеству)' },
    { key: EMAIL_KEYS.MARKETPLACE, email: '', description: 'Отдел по ЧИМ (заявки от селлеров маркетплейсов)' },
  ];

  for (const item of defaults) {
    await pool.query(`
      INSERT INTO email_settings (key, email, description)
      VALUES ($1, $2, $3)
      ON CONFLICT (key) DO NOTHING
    `, [item.key, item.email, item.description]);
  }
}

// GET - получить все email настройки
export async function GET() {
  try {
    await ensureTable();
    const pool = getPool();

    const result = await pool.query(`
      SELECT key, email, description, updated_at
      FROM email_settings
      ORDER BY id
    `);

    return NextResponse.json({
      success: true,
      data: result.rows,
    });
  } catch (error) {
    console.error('Error fetching email settings:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка получения настроек' },
      { status: 500 }
    );
  }
}

// PUT - обновить email настройки
export async function PUT(request: NextRequest) {
  try {
    await ensureTable();
    const pool = getPool();
    const body = await request.json();

    // Expect body to be an array of { key, email } objects
    if (!Array.isArray(body.emails)) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат данных' },
        { status: 400 }
      );
    }

    for (const item of body.emails) {
      if (!item.key || typeof item.email !== 'string') continue;

      // Validate email format if not empty
      if (item.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(item.email)) {
        return NextResponse.json(
          { success: false, error: `Некорректный email для ${item.key}` },
          { status: 400 }
        );
      }

      await pool.query(`
        UPDATE email_settings
        SET email = $1, updated_at = CURRENT_TIMESTAMP
        WHERE key = $2
      `, [item.email, item.key]);
    }

    return NextResponse.json({
      success: true,
      message: 'Настройки сохранены',
    });
  } catch (error) {
    console.error('Error updating email settings:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сохранения настроек' },
      { status: 500 }
    );
  }
}
