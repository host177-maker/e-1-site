import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single promotion
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const { id } = await params;
    const promotionId = parseInt(id);

    if (isNaN(promotionId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID акции' },
        { status: 400 }
      );
    }

    const pool = getPool();
    const result = await pool.query(
      `SELECT * FROM promotions WHERE id = $1`,
      [promotionId]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Акция не найдена' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error fetching promotion:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update promotion
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const { id } = await params;
    const promotionId = parseInt(id);

    if (isNaN(promotionId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID акции' },
        { status: 400 }
      );
    }

    const body = await request.json();
    const { title, content, images, start_date, end_date, published_at, is_active } = body;

    const pool = getPool();

    // Check if promotion exists
    const existing = await pool.query(
      'SELECT id FROM promotions WHERE id = $1',
      [promotionId]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Акция не найдена' },
        { status: 404 }
      );
    }

    // Build update query
    const updates: string[] = [];
    const values: (string | boolean | number | string[] | null)[] = [];
    let paramIndex = 1;

    if (typeof title === 'string') {
      updates.push(`title = $${paramIndex}`);
      values.push(title);
      paramIndex++;
    }

    if (typeof content === 'string') {
      updates.push(`content = $${paramIndex}`);
      values.push(content);
      paramIndex++;
    }

    if (Array.isArray(images)) {
      updates.push(`images = $${paramIndex}`);
      values.push(images);
      paramIndex++;
    }

    if (start_date) {
      updates.push(`start_date = $${paramIndex}`);
      values.push(start_date);
      paramIndex++;
    }

    if (end_date) {
      updates.push(`end_date = $${paramIndex}`);
      values.push(end_date);
      paramIndex++;
    }

    if (published_at !== undefined) {
      updates.push(`published_at = $${paramIndex}`);
      values.push(published_at);
      paramIndex++;
    }

    if (typeof is_active === 'boolean') {
      updates.push(`is_active = $${paramIndex}`);
      values.push(is_active);
      paramIndex++;
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    updates.push(`updated_at = NOW()`);
    values.push(promotionId);

    const result = await pool.query(
      `UPDATE promotions SET ${updates.join(', ')} WHERE id = $${paramIndex}
       RETURNING *`,
      values
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating promotion:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete promotion
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const { id } = await params;
    const promotionId = parseInt(id);

    if (isNaN(promotionId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID акции' },
        { status: 400 }
      );
    }

    const pool = getPool();

    // Check if promotion exists
    const existing = await pool.query(
      'SELECT id FROM promotions WHERE id = $1',
      [promotionId]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Акция не найдена' },
        { status: 404 }
      );
    }

    await pool.query('DELETE FROM promotions WHERE id = $1', [promotionId]);

    return NextResponse.json({
      success: true,
      message: 'Акция удалена',
    });
  } catch (error) {
    console.error('Error deleting promotion:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
