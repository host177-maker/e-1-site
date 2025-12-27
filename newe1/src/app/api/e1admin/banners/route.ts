import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

async function ensureBannersTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS banners (
      id SERIAL PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      desktop_image VARCHAR(500) NOT NULL,
      mobile_image VARCHAR(500) NOT NULL,
      link VARCHAR(500),
      sort_order INTEGER DEFAULT 0,
      is_active BOOLEAN DEFAULT true,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )
  `);
}

// GET: List all banners
export async function GET() {
  try {
    await ensureBannersTable();
    const pool = getPool();

    const result = await pool.query(
      `SELECT * FROM banners ORDER BY sort_order ASC, created_at DESC`
    );

    // Get counts
    const activeCount = result.rows.filter(b => b.is_active).length;
    const inactiveCount = result.rows.filter(b => !b.is_active).length;

    return NextResponse.json({
      success: true,
      data: result.rows,
      counts: {
        active: activeCount,
        inactive: inactiveCount,
        total: result.rows.length,
      },
    });
  } catch (error) {
    console.error('Error fetching banners:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new banner
export async function POST(request: NextRequest) {
  try {
    await ensureBannersTable();
    const pool = getPool();
    const body = await request.json();

    const { name, desktop_image, mobile_image, link, is_active } = body;

    if (!name || !desktop_image || !mobile_image) {
      return NextResponse.json(
        { success: false, error: 'Название и изображения обязательны' },
        { status: 400 }
      );
    }

    // Get max sort_order
    const maxOrderResult = await pool.query(
      `SELECT COALESCE(MAX(sort_order), 0) as max_order FROM banners`
    );
    const nextOrder = maxOrderResult.rows[0].max_order + 1;

    const result = await pool.query(
      `INSERT INTO banners (name, desktop_image, mobile_image, link, sort_order, is_active)
       VALUES ($1, $2, $3, $4, $5, $6)
       RETURNING *`,
      [name, desktop_image, mobile_image, link || null, nextOrder, is_active !== false]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating banner:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update banner order (bulk update)
export async function PATCH(request: NextRequest) {
  try {
    await ensureBannersTable();
    const pool = getPool();
    const body = await request.json();

    const { order } = body; // Array of { id, sort_order }

    if (!Array.isArray(order)) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат данных' },
        { status: 400 }
      );
    }

    // Update each banner's sort_order
    for (const item of order) {
      await pool.query(
        `UPDATE banners SET sort_order = $1, updated_at = NOW() WHERE id = $2`,
        [item.sort_order, item.id]
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating banner order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
