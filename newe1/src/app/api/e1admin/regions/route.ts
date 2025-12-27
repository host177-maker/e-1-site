import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

async function ensureIsActiveColumn(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    ALTER TABLE regions ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT true
  `);
}

// GET: List all regions
export async function GET() {
  try {
    await ensureIsActiveColumn();
    const pool = getPool();

    const result = await pool.query(`
      SELECT r.*,
             COUNT(DISTINCT c.id) as city_count,
             COUNT(DISTINCT s.id) as salon_count
      FROM regions r
      LEFT JOIN cities c ON c.region_id = r.id
      LEFT JOIN salons s ON s.region_id = r.id
      GROUP BY r.id
      ORDER BY r.sort_order ASC, r.name ASC
    `);

    // Get counts
    const activeCount = result.rows.filter(r => r.is_active).length;
    const inactiveCount = result.rows.filter(r => !r.is_active).length;

    return NextResponse.json({
      success: true,
      data: result.rows.map(r => ({
        ...r,
        city_count: parseInt(r.city_count) || 0,
        salon_count: parseInt(r.salon_count) || 0,
      })),
      counts: {
        active: activeCount,
        inactive: inactiveCount,
        total: result.rows.length,
      },
    });
  } catch (error) {
    console.error('Error fetching regions:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new region
export async function POST(request: NextRequest) {
  try {
    await ensureIsActiveColumn();
    const pool = getPool();
    const body = await request.json();

    const { name, sort_order, is_active } = body;

    if (!name || !name.trim()) {
      return NextResponse.json(
        { success: false, error: 'Название обязательно' },
        { status: 400 }
      );
    }

    // Check if region with this name already exists
    const existing = await pool.query(
      `SELECT id FROM regions WHERE LOWER(name) = LOWER($1)`,
      [name.trim()]
    );

    if (existing.rows.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Регион с таким названием уже существует' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `INSERT INTO regions (name, sort_order, is_active)
       VALUES ($1, $2, $3)
       RETURNING *`,
      [name.trim(), sort_order || 500, is_active !== false]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating region:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update regions order (bulk update)
export async function PATCH(request: NextRequest) {
  try {
    await ensureIsActiveColumn();
    const pool = getPool();
    const body = await request.json();

    const { order } = body; // Array of { id, sort_order }

    if (!Array.isArray(order)) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат данных' },
        { status: 400 }
      );
    }

    // Update each region's sort_order
    for (const item of order) {
      await pool.query(
        `UPDATE regions SET sort_order = $1 WHERE id = $2`,
        [item.sort_order, item.id]
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating region order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
