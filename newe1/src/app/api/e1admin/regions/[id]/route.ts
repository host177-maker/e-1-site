import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single region
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      `SELECT r.*,
              COUNT(DISTINCT c.id) as city_count,
              COUNT(DISTINCT s.id) as salon_count
       FROM regions r
       LEFT JOIN cities c ON c.region_id = r.id
       LEFT JOIN salons s ON s.region_id = r.id
       WHERE r.id = $1
       GROUP BY r.id`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Регион не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: {
        ...result.rows[0],
        city_count: parseInt(result.rows[0].city_count) || 0,
        salon_count: parseInt(result.rows[0].salon_count) || 0,
      },
    });
  } catch (error) {
    console.error('Error fetching region:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update region
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();
    const body = await request.json();

    // Check if region exists
    const existing = await pool.query(
      `SELECT * FROM regions WHERE id = $1`,
      [id]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Регион не найден' },
        { status: 404 }
      );
    }

    // Check for duplicate name if name is being updated
    if (body.name) {
      const duplicate = await pool.query(
        `SELECT id FROM regions WHERE LOWER(name) = LOWER($1) AND id != $2`,
        [body.name.trim(), id]
      );
      if (duplicate.rows.length > 0) {
        return NextResponse.json(
          { success: false, error: 'Регион с таким названием уже существует' },
          { status: 400 }
        );
      }
    }

    // Build update query dynamically
    const updates: string[] = [];
    const values: (string | boolean | number | null)[] = [];
    let paramIndex = 1;

    const allowedFields = ['name', 'sort_order', 'is_active'];

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
      `UPDATE regions SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating region:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete region
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    // Check if region has cities or salons
    const citiesCount = await pool.query(
      `SELECT COUNT(*) as count FROM cities WHERE region_id = $1`,
      [id]
    );

    const salonsCount = await pool.query(
      `SELECT COUNT(*) as count FROM salons WHERE region_id = $1`,
      [id]
    );

    if (parseInt(citiesCount.rows[0].count) > 0) {
      return NextResponse.json(
        { success: false, error: `Нельзя удалить регион: есть ${citiesCount.rows[0].count} связанных городов` },
        { status: 400 }
      );
    }

    if (parseInt(salonsCount.rows[0].count) > 0) {
      return NextResponse.json(
        { success: false, error: `Нельзя удалить регион: есть ${salonsCount.rows[0].count} связанных салонов` },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `DELETE FROM regions WHERE id = $1 RETURNING *`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Регион не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: 'Регион удалён',
    });
  } catch (error) {
    console.error('Error deleting region:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
