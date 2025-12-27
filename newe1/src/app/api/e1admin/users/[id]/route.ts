import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin, hashPassword } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// PATCH: Update user (deactivate, change password)
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
    const userId = parseInt(id);

    if (isNaN(userId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID пользователя' },
        { status: 400 }
      );
    }

    const body = await request.json();
    const { is_active, password } = body;

    const pool = getPool();

    // Check if user exists
    const existing = await pool.query(
      'SELECT id, username FROM admin_users WHERE id = $1',
      [userId]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Пользователь не найден' },
        { status: 404 }
      );
    }

    // Build update query
    const updates: string[] = [];
    const values: (string | boolean | number)[] = [];
    let paramIndex = 1;

    if (typeof is_active === 'boolean') {
      // Prevent self-deactivation
      if (userId === currentAdmin.id && !is_active) {
        return NextResponse.json(
          { success: false, error: 'Нельзя деактивировать свою учётную запись' },
          { status: 400 }
        );
      }
      updates.push(`is_active = $${paramIndex}`);
      values.push(is_active);
      paramIndex++;
    }

    if (password) {
      if (password.length < 6) {
        return NextResponse.json(
          { success: false, error: 'Пароль должен содержать минимум 6 символов' },
          { status: 400 }
        );
      }
      updates.push(`password_hash = $${paramIndex}`);
      values.push(hashPassword(password));
      paramIndex++;
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    updates.push(`updated_at = NOW()`);
    values.push(userId);

    const result = await pool.query(
      `UPDATE admin_users SET ${updates.join(', ')} WHERE id = $${paramIndex}
       RETURNING id, username, is_active, created_at, updated_at, last_login`,
      values
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating user:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete user (only if deactivated or never logged in)
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
    const userId = parseInt(id);

    if (isNaN(userId)) {
      return NextResponse.json(
        { success: false, error: 'Неверный ID пользователя' },
        { status: 400 }
      );
    }

    // Prevent self-deletion
    if (userId === currentAdmin.id) {
      return NextResponse.json(
        { success: false, error: 'Нельзя удалить свою учётную запись' },
        { status: 400 }
      );
    }

    const pool = getPool();

    // Check if user exists and can be deleted
    const existing = await pool.query(
      'SELECT id, is_active, last_login FROM admin_users WHERE id = $1',
      [userId]
    );

    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Пользователь не найден' },
        { status: 404 }
      );
    }

    const user = existing.rows[0];

    // Only allow deletion of deactivated users or those who never logged in
    if (user.is_active && user.last_login) {
      return NextResponse.json(
        { success: false, error: 'Сначала деактивируйте пользователя' },
        { status: 400 }
      );
    }

    await pool.query('DELETE FROM admin_users WHERE id = $1', [userId]);

    return NextResponse.json({
      success: true,
      message: 'Пользователь удалён',
    });
  } catch (error) {
    console.error('Error deleting user:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
