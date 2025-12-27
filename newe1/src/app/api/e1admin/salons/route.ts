import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

async function ensureIsActiveColumn(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    ALTER TABLE salons ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT true
  `);
}

// GET: List all salons
export async function GET(request: NextRequest) {
  try {
    await ensureIsActiveColumn();
    const pool = getPool();
    const { searchParams } = new URL(request.url);
    const regionId = searchParams.get('region_id');
    const cityId = searchParams.get('city_id');
    const cityName = searchParams.get('city');

    let query = `
      SELECT s.*,
             r.name as region_name,
             c.name as city_name_ref
      FROM salons s
      LEFT JOIN regions r ON r.id = s.region_id
      LEFT JOIN cities c ON c.id = s.city_id
    `;

    const conditions: string[] = [];
    const params: (string | number)[] = [];
    let paramIndex = 1;

    if (regionId) {
      conditions.push(`s.region_id = $${paramIndex}`);
      params.push(parseInt(regionId));
      paramIndex++;
    }

    if (cityId) {
      conditions.push(`s.city_id = $${paramIndex}`);
      params.push(parseInt(cityId));
      paramIndex++;
    }

    if (cityName) {
      conditions.push(`LOWER(s.city) = LOWER($${paramIndex})`);
      params.push(cityName);
      paramIndex++;
    }

    if (conditions.length > 0) {
      query += ` WHERE ${conditions.join(' AND ')}`;
    }

    query += ` ORDER BY r.name ASC, s.city ASC, s.name ASC`;

    const result = await pool.query(query, params);

    // Get counts
    const activeCount = result.rows.filter(s => s.is_active).length;
    const inactiveCount = result.rows.filter(s => !s.is_active).length;

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
    console.error('Error fetching salons:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new salon
export async function POST(request: NextRequest) {
  try {
    await ensureIsActiveColumn();
    const pool = getPool();
    const body = await request.json();

    const {
      name,
      city,
      region,
      address,
      email,
      phone,
      working_hours,
      latitude,
      longitude,
      external_code,
      slug,
      region_id,
      city_id,
      is_active
    } = body;

    if (!name || !name.trim()) {
      return NextResponse.json(
        { success: false, error: 'Название обязательно' },
        { status: 400 }
      );
    }

    if (!city || !city.trim()) {
      return NextResponse.json(
        { success: false, error: 'Город обязателен' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `INSERT INTO salons (name, city, region, address, email, phone, working_hours,
                          latitude, longitude, external_code, slug, region_id, city_id, is_active)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)
       RETURNING *`,
      [
        name.trim(),
        city.trim(),
        region?.trim() || null,
        address?.trim() || null,
        email?.trim() || null,
        phone?.trim() || null,
        working_hours?.trim() || null,
        latitude || null,
        longitude || null,
        external_code?.trim() || null,
        slug?.trim() || null,
        region_id || null,
        city_id || null,
        is_active !== false
      ]
    );

    // Get region and city names for response
    let region_name = null;
    let city_name_ref = null;

    if (result.rows[0].region_id) {
      const regionResult = await pool.query(
        `SELECT name FROM regions WHERE id = $1`,
        [result.rows[0].region_id]
      );
      region_name = regionResult.rows[0]?.name || null;
    }

    if (result.rows[0].city_id) {
      const cityResult = await pool.query(
        `SELECT name FROM cities WHERE id = $1`,
        [result.rows[0].city_id]
      );
      city_name_ref = cityResult.rows[0]?.name || null;
    }

    return NextResponse.json({
      success: true,
      data: {
        ...result.rows[0],
        region_name,
        city_name_ref,
      },
    });
  } catch (error) {
    console.error('Error creating salon:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
