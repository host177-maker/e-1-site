import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

interface CartItem {
  name: string;
  slug: string;
  price: number;
  quantity: number;
  width?: number;
  height?: number;
  depth?: number;
  bodyColor?: string;
  profileColor?: string;
  filling?: string;
  includeAssembly: boolean;
  assemblyPrice: number;
}

// GET: List all orders
export async function GET(request: NextRequest) {
  try {
    const pool = getPool();
    const { searchParams } = new URL(request.url);

    const search = searchParams.get('search') || '';
    const status = searchParams.get('status') || '';
    const dateFrom = searchParams.get('dateFrom') || '';
    const dateTo = searchParams.get('dateTo') || '';

    let query = `
      SELECT * FROM cart_orders
      WHERE 1=1
    `;
    const params: (string | number)[] = [];
    let paramIndex = 1;

    if (search) {
      query += ` AND (
        customer_name ILIKE $${paramIndex} OR
        customer_phone ILIKE $${paramIndex} OR
        customer_email ILIKE $${paramIndex} OR
        CAST(id AS TEXT) = $${paramIndex + 1}
      )`;
      params.push(`%${search}%`, search);
      paramIndex += 2;
    }

    if (status) {
      query += ` AND status = $${paramIndex}`;
      params.push(status);
      paramIndex++;
    }

    if (dateFrom) {
      query += ` AND created_at >= $${paramIndex}::date`;
      params.push(dateFrom);
      paramIndex++;
    }

    if (dateTo) {
      query += ` AND created_at < ($${paramIndex}::date + INTERVAL '1 day')`;
      params.push(dateTo);
      paramIndex++;
    }

    query += ` ORDER BY created_at DESC`;

    const result = await pool.query(query, params);

    // Get counts by status
    const countsResult = await pool.query(`
      SELECT
        COUNT(*) as total,
        COUNT(*) FILTER (WHERE status = 'new') as new,
        COUNT(*) FILTER (WHERE status = 'processing') as processing,
        COUNT(*) FILTER (WHERE status = 'completed') as completed,
        COUNT(*) FILTER (WHERE status = 'cancelled') as cancelled
      FROM cart_orders
    `);

    return NextResponse.json({
      success: true,
      data: result.rows.map(row => ({
        ...row,
        items: typeof row.items === 'string' ? JSON.parse(row.items) : row.items,
        total_price: parseFloat(row.total_price) || 0,
        delivery_cost: parseFloat(row.delivery_cost) || 0,
        lift_cost: parseFloat(row.lift_cost) || 0,
        assembly_cost: parseFloat(row.assembly_cost) || 0,
      })),
      counts: countsResult.rows[0],
    });
  } catch (error) {
    console.error('Error fetching orders:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update order status
export async function PATCH(request: NextRequest) {
  try {
    const pool = getPool();
    const body = await request.json();
    const { id, status } = body;

    if (!id || !status) {
      return NextResponse.json(
        { success: false, error: 'ID и статус обязательны' },
        { status: 400 }
      );
    }

    const validStatuses = ['new', 'processing', 'completed', 'cancelled'];
    if (!validStatuses.includes(status)) {
      return NextResponse.json(
        { success: false, error: 'Неверный статус' },
        { status: 400 }
      );
    }

    await pool.query(
      `UPDATE cart_orders SET status = $1, updated_at = NOW() WHERE id = $2`,
      [status, id]
    );

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete order
export async function DELETE(request: NextRequest) {
  try {
    const pool = getPool();
    const body = await request.json();

    if (body.ids && Array.isArray(body.ids)) {
      // Bulk delete
      await pool.query(
        `DELETE FROM cart_orders WHERE id = ANY($1)`,
        [body.ids]
      );
    } else if (body.id) {
      // Single delete
      await pool.query(
        `DELETE FROM cart_orders WHERE id = $1`,
        [body.id]
      );
    } else {
      return NextResponse.json(
        { success: false, error: 'ID не указан' },
        { status: 400 }
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error deleting order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
