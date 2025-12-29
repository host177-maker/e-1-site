import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single designer
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
    const pool = getPool();

    const result = await pool.query(
      `SELECT * FROM designers WHERE id = $1`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Дизайнер не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error fetching designer:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update designer
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
    const body = await request.json();

    const pool = getPool();

    // Check if designer exists
    const existing = await pool.query(
      `SELECT * FROM designers WHERE id = $1`,
      [id]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Дизайнер не найден' },
        { status: 404 }
      );
    }

    // Build dynamic update query
    const updates: string[] = [];
    const values: (string | boolean | number | null)[] = [];
    let paramIndex = 1;

    const allowedFields = [
      'business_type',
      'full_name',
      'phone',
      'email',
      'portfolio_link',
      'promo_code',
      'is_active',
      'step_completed'
    ];

    for (const field of allowedFields) {
      if (body[field] !== undefined) {
        let value = body[field];

        // Normalize promo_code to uppercase
        if (field === 'promo_code' && value) {
          value = value.trim().toUpperCase();

          // Check uniqueness
          const existingPromo = await pool.query(
            `SELECT id FROM designers WHERE UPPER(promo_code) = $1 AND id != $2`,
            [value, id]
          );

          if (existingPromo.rows.length > 0) {
            return NextResponse.json(
              { success: false, error: 'Этот промокод уже занят' },
              { status: 400 }
            );
          }
        }

        // Normalize email to lowercase
        if (field === 'email' && value) {
          value = value.trim().toLowerCase();

          // Check uniqueness
          const existingEmail = await pool.query(
            `SELECT id FROM designers WHERE LOWER(email) = $1 AND id != $2`,
            [value, id]
          );

          if (existingEmail.rows.length > 0) {
            return NextResponse.json(
              { success: false, error: 'Этот email уже зарегистрирован' },
              { status: 400 }
            );
          }
        }

        updates.push(`${field} = $${paramIndex}`);
        values.push(value);
        paramIndex++;
      }
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    // Add updated_at
    updates.push(`updated_at = CURRENT_TIMESTAMP`);

    values.push(parseInt(id));

    const result = await pool.query(
      `UPDATE designers SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating designer:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete designer
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
    const pool = getPool();

    const result = await pool.query(
      `DELETE FROM designers WHERE id = $1 RETURNING *`,
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Дизайнер не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: 'Дизайнер удален',
    });
  } catch (error) {
    console.error('Error deleting designer:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
