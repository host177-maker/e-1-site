import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single service price
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      `SELECT * FROM service_prices WHERE id = $1`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Группа прайса не найдена' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error fetching service price:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update service price
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();
    const body = await request.json();

    const {
      region_group,
      delivery_base_price,
      delivery_per_km,
      floor_lift_price,
      elevator_lift_price,
      assembly_per_km,
      sort_order,
      is_active
    } = body;

    // Build dynamic update query
    const updates: string[] = [];
    const values: unknown[] = [];
    let paramIndex = 1;

    if (region_group !== undefined) {
      // Check for duplicate
      const existing = await pool.query(
        `SELECT id FROM service_prices WHERE LOWER(region_group) = LOWER($1) AND id != $2`,
        [region_group.trim(), id]
      );
      if (existing.rows.length > 0) {
        return NextResponse.json(
          { success: false, error: 'Группа с таким названием уже существует' },
          { status: 400 }
        );
      }
      updates.push(`region_group = $${paramIndex++}`);
      values.push(region_group.trim());
    }
    if (delivery_base_price !== undefined) {
      updates.push(`delivery_base_price = $${paramIndex++}`);
      values.push(delivery_base_price);
    }
    if (delivery_per_km !== undefined) {
      updates.push(`delivery_per_km = $${paramIndex++}`);
      values.push(delivery_per_km);
    }
    if (floor_lift_price !== undefined) {
      updates.push(`floor_lift_price = $${paramIndex++}`);
      values.push(floor_lift_price);
    }
    if (elevator_lift_price !== undefined) {
      updates.push(`elevator_lift_price = $${paramIndex++}`);
      values.push(elevator_lift_price);
    }
    if (assembly_per_km !== undefined) {
      updates.push(`assembly_per_km = $${paramIndex++}`);
      values.push(assembly_per_km);
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
      `UPDATE service_prices SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Группа прайса не найдена' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating service price:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete service price
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    // Check if price group is used by any cities
    const citiesResult = await pool.query(
      `SELECT COUNT(*) as count FROM cities WHERE price_group = (SELECT region_group FROM service_prices WHERE id = $1)`,
      [id]
    );

    if (parseInt(citiesResult.rows[0].count) > 0) {
      return NextResponse.json(
        { success: false, error: 'Нельзя удалить группу прайса, к которой привязаны города' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `DELETE FROM service_prices WHERE id = $1 RETURNING id`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Группа прайса не найдена' },
        { status: 404 }
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting service price:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
