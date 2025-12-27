import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single review
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
    const reviewId = parseInt(id);

    if (isNaN(reviewId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID отзыва' },
        { status: 400 }
      );
    }

    const pool = getPool();
    const result = await pool.query(
      `SELECT id, name, phone, order_number, review_text, company_response,
              rating, photos, is_active, show_on_main, created_at
       FROM reviews WHERE id = $1`,
      [reviewId]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Отзыв не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error fetching review:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update review
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
    const reviewId = parseInt(id);

    if (isNaN(reviewId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID отзыва' },
        { status: 400 }
      );
    }

    const body = await request.json();
    const { review_text, company_response, is_active, show_on_main } = body;

    const pool = getPool();

    // Check if review exists
    const existing = await pool.query(
      'SELECT id FROM reviews WHERE id = $1',
      [reviewId]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Отзыв не найден' },
        { status: 404 }
      );
    }

    // Build update query
    const updates: string[] = [];
    const values: (string | boolean | number | null)[] = [];
    let paramIndex = 1;

    if (typeof review_text === 'string') {
      updates.push(`review_text = $${paramIndex}`);
      values.push(review_text);
      paramIndex++;
    }

    if (typeof company_response === 'string' || company_response === null) {
      updates.push(`company_response = $${paramIndex}`);
      values.push(company_response);
      paramIndex++;
    }

    if (typeof is_active === 'boolean') {
      updates.push(`is_active = $${paramIndex}`);
      values.push(is_active);
      paramIndex++;
    }

    if (typeof show_on_main === 'boolean') {
      updates.push(`show_on_main = $${paramIndex}`);
      values.push(show_on_main);
      paramIndex++;
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    values.push(reviewId);

    const result = await pool.query(
      `UPDATE reviews SET ${updates.join(', ')} WHERE id = $${paramIndex}
       RETURNING id, name, phone, order_number, review_text, company_response,
                 rating, photos, is_active, show_on_main, created_at`,
      values
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating review:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete review
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
    const reviewId = parseInt(id);

    if (isNaN(reviewId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID отзыва' },
        { status: 400 }
      );
    }

    const pool = getPool();

    // Check if review exists
    const existing = await pool.query(
      'SELECT id FROM reviews WHERE id = $1',
      [reviewId]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Отзыв не найден' },
        { status: 404 }
      );
    }

    await pool.query('DELETE FROM reviews WHERE id = $1', [reviewId]);

    return NextResponse.json({
      success: true,
      message: 'Отзыв удалён',
    });
  } catch (error) {
    console.error('Error deleting review:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
