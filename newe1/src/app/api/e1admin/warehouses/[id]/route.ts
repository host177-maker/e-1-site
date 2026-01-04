import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single warehouse
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      `SELECT w.*,
              COUNT(DISTINCT c.id) as city_count
       FROM warehouses w
       LEFT JOIN cities c ON c.warehouse_id = w.id
       WHERE w.id = $1
       GROUP BY w.id`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Склад не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: {
        ...result.rows[0],
        city_count: parseInt(result.rows[0].city_count) || 0,
      },
    });
  } catch (error) {
    console.error('Error fetching warehouse:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update warehouse
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();
    const body = await request.json();

    const { name, display_name, address, latitude, longitude, phone, working_hours, sort_order, is_active } = body;

    // Build dynamic update query
    const updates: string[] = [];
    const values: unknown[] = [];
    let paramIndex = 1;

    if (name !== undefined) {
      updates.push(`name = $${paramIndex++}`);
      values.push(name.trim());
    }
    if (display_name !== undefined) {
      updates.push(`display_name = $${paramIndex++}`);
      values.push(display_name?.trim() || null);
    }
    if (address !== undefined) {
      updates.push(`address = $${paramIndex++}`);
      values.push(address?.trim() || null);
    }
    if (latitude !== undefined) {
      updates.push(`latitude = $${paramIndex++}`);
      values.push(latitude || null);
    }
    if (longitude !== undefined) {
      updates.push(`longitude = $${paramIndex++}`);
      values.push(longitude || null);
    }
    if (phone !== undefined) {
      updates.push(`phone = $${paramIndex++}`);
      values.push(phone?.trim() || null);
    }
    if (working_hours !== undefined) {
      updates.push(`working_hours = $${paramIndex++}`);
      values.push(working_hours?.trim() || null);
    }
    if (sort_order !== undefined) {
      updates.push(`sort_order = $${paramIndex++}`);
      values.push(sort_order);
    }
    if (is_active !== undefined) {
      updates.push(`is_active = $${paramIndex++}`);
      values.push(is_active);
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    updates.push(`updated_at = NOW()`);
    values.push(id);

    const result = await pool.query(
      `UPDATE warehouses SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Склад не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating warehouse:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete warehouse
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    // Check if warehouse is used by any cities
    const citiesResult = await pool.query(
      `SELECT COUNT(*) as count FROM cities WHERE warehouse_id = $1`,
      [id]
    );

    if (parseInt(citiesResult.rows[0].count) > 0) {
      return NextResponse.json(
        { success: false, error: 'Нельзя удалить склад, к которому привязаны города' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `DELETE FROM warehouses WHERE id = $1 RETURNING id`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Склад не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting warehouse:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
