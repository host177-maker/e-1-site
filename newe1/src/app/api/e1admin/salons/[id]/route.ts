import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single salon
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      `SELECT s.*,
              r.name as region_name,
              c.name as city_name_ref
       FROM salons s
       LEFT JOIN regions r ON r.id = s.region_id
       LEFT JOIN cities c ON c.id = s.city_id
       WHERE s.id = $1`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Салон не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error fetching salon:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update salon
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();
    const body = await request.json();

    // Check if salon exists
    const existing = await pool.query(
      `SELECT * FROM salons WHERE id = $1`,
      [id]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Салон не найден' },
        { status: 404 }
      );
    }

    // Check if region exists
    if (body.region_id !== undefined && body.region_id !== null) {
      const regionExists = await pool.query(
        `SELECT id FROM regions WHERE id = $1`,
        [body.region_id]
      );
      if (regionExists.rows.length === 0) {
        return NextResponse.json(
          { success: false, error: 'Регион не найден' },
          { status: 400 }
        );
      }
    }

    // Check if city exists
    if (body.city_id !== undefined && body.city_id !== null) {
      const cityExists = await pool.query(
        `SELECT id FROM cities WHERE id = $1`,
        [body.city_id]
      );
      if (cityExists.rows.length === 0) {
        return NextResponse.json(
          { success: false, error: 'Город не найден' },
          { status: 400 }
        );
      }
    }

    // Build update query dynamically
    const updates: string[] = [];
    const values: (string | boolean | number | null)[] = [];
    let paramIndex = 1;

    const allowedFields = [
      'name', 'city', 'region', 'address', 'email', 'phone',
      'working_hours', 'latitude', 'longitude', 'external_code',
      'slug', 'region_id', 'city_id', 'is_active'
    ];

    for (const field of allowedFields) {
      if (body[field] !== undefined) {
        updates.push(`${field} = $${paramIndex}`);
        // Trim string values
        const value = typeof body[field] === 'string' ? body[field].trim() : body[field];
        values.push(value === '' ? null : value);
        paramIndex++;
      }
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    values.push(id);

    const result = await pool.query(
      `UPDATE salons SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
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
    console.error('Error updating salon:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete salon
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      `DELETE FROM salons WHERE id = $1 RETURNING *`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Салон не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: 'Салон удалён',
    });
  } catch (error) {
    console.error('Error deleting salon:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
