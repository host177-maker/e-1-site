import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single city
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      `SELECT c.*,
              r.name as region_name,
              COUNT(DISTINCT s.id) as salon_count
       FROM cities c
       LEFT JOIN regions r ON r.id = c.region_id
       LEFT JOIN salons s ON s.city_id = c.id
       WHERE c.id = $1
       GROUP BY c.id, r.name`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Город не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: {
        ...result.rows[0],
        salon_count: parseInt(result.rows[0].salon_count) || 0,
      },
    });
  } catch (error) {
    console.error('Error fetching city:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update city
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();
    const body = await request.json();

    // Check if city exists
    const existing = await pool.query(
      `SELECT * FROM cities WHERE id = $1`,
      [id]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Город не найден' },
        { status: 404 }
      );
    }

    const currentCity = existing.rows[0];

    // Check for duplicate name if name or region is being updated
    const newName = body.name !== undefined ? body.name.trim() : currentCity.name;
    const newRegionId = body.region_id !== undefined ? body.region_id : currentCity.region_id;

    if (body.name !== undefined || body.region_id !== undefined) {
      const duplicate = await pool.query(
        `SELECT id FROM cities WHERE LOWER(name) = LOWER($1) AND region_id = $2 AND id != $3`,
        [newName, newRegionId, id]
      );
      if (duplicate.rows.length > 0) {
        return NextResponse.json(
          { success: false, error: 'Город с таким названием уже существует в этом регионе' },
          { status: 400 }
        );
      }
    }

    // Check if region exists
    if (body.region_id !== undefined) {
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

    // Build update query dynamically
    const updates: string[] = [];
    const values: (string | boolean | number | null)[] = [];
    let paramIndex = 1;

    const allowedFields = ['name', 'name_prepositional', 'region_id', 'external_code', 'sort_order', 'is_active'];

    for (const field of allowedFields) {
      if (body[field] !== undefined) {
        updates.push(`${field} = $${paramIndex}`);
        values.push(field === 'name' ? body[field].trim() : body[field]);
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
      `UPDATE cities SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    // Get region name for response
    const regionResult = await pool.query(
      `SELECT name FROM regions WHERE id = $1`,
      [result.rows[0].region_id]
    );

    return NextResponse.json({
      success: true,
      data: {
        ...result.rows[0],
        region_name: regionResult.rows[0]?.name || null,
      },
    });
  } catch (error) {
    console.error('Error updating city:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete city
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    // Check if city has salons
    const salonsCount = await pool.query(
      `SELECT COUNT(*) as count FROM salons WHERE city_id = $1`,
      [id]
    );

    if (parseInt(salonsCount.rows[0].count) > 0) {
      return NextResponse.json(
        { success: false, error: `Нельзя удалить город: есть ${salonsCount.rows[0].count} связанных салонов` },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `DELETE FROM cities WHERE id = $1 RETURNING *`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Город не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: 'Город удалён',
    });
  } catch (error) {
    console.error('Error deleting city:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
