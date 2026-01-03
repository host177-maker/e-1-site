import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List all cities
export async function GET(request: NextRequest) {
  try {
    const pool = getPool();
    const { searchParams } = new URL(request.url);
    const regionId = searchParams.get('region_id');

    let query = `
      SELECT c.*,
             r.name as region_name,
             COUNT(DISTINCT s.id) as salon_count
      FROM cities c
      LEFT JOIN regions r ON r.id = c.region_id
      LEFT JOIN salons s ON s.city_id = c.id
    `;

    const params: (string | number)[] = [];

    if (regionId) {
      query += ` WHERE c.region_id = $1`;
      params.push(parseInt(regionId));
    }

    query += `
      GROUP BY c.id, r.name
      ORDER BY r.name ASC, c.sort_order ASC, c.name ASC
    `;

    const result = await pool.query(query, params);

    // Get counts
    const activeCount = result.rows.filter(c => c.is_active).length;
    const inactiveCount = result.rows.filter(c => !c.is_active).length;

    return NextResponse.json({
      success: true,
      data: result.rows.map(c => ({
        ...c,
        salon_count: parseInt(c.salon_count) || 0,
      })),
      counts: {
        active: activeCount,
        inactive: inactiveCount,
        total: result.rows.length,
      },
    });
  } catch (error) {
    console.error('Error fetching cities:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new city
export async function POST(request: NextRequest) {
  try {
    const pool = getPool();
    const body = await request.json();

    const { name, name_prepositional, region_id, external_code, sort_order, is_active } = body;

    if (!name || !name.trim()) {
      return NextResponse.json(
        { success: false, error: 'Название обязательно' },
        { status: 400 }
      );
    }

    if (!region_id) {
      return NextResponse.json(
        { success: false, error: 'Регион обязателен' },
        { status: 400 }
      );
    }

    // Check if region exists
    const regionExists = await pool.query(
      `SELECT id FROM regions WHERE id = $1`,
      [region_id]
    );

    if (regionExists.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Регион не найден' },
        { status: 400 }
      );
    }

    // Check if city with this name already exists in this region
    const existing = await pool.query(
      `SELECT id FROM cities WHERE LOWER(name) = LOWER($1) AND region_id = $2`,
      [name.trim(), region_id]
    );

    if (existing.rows.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Город с таким названием уже существует в этом регионе' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `INSERT INTO cities (name, name_prepositional, region_id, external_code, sort_order, is_active)
       VALUES ($1, $2, $3, $4, $5, $6)
       RETURNING *`,
      [name.trim(), name_prepositional?.trim() || null, region_id, external_code || null, sort_order || 500, is_active !== false]
    );

    // Get region name for response
    const regionResult = await pool.query(
      `SELECT name FROM regions WHERE id = $1`,
      [region_id]
    );

    return NextResponse.json({
      success: true,
      data: {
        ...result.rows[0],
        region_name: regionResult.rows[0]?.name || null,
        salon_count: 0,
      },
    });
  } catch (error) {
    console.error('Error creating city:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update cities order (bulk update)
export async function PATCH(request: NextRequest) {
  try {
    const pool = getPool();
    const body = await request.json();

    const { order } = body; // Array of { id, sort_order }

    if (!Array.isArray(order)) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат данных' },
        { status: 400 }
      );
    }

    // Update each city's sort_order
    for (const item of order) {
      await pool.query(
        `UPDATE cities SET sort_order = $1 WHERE id = $2`,
        [item.sort_order, item.id]
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating city order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
