import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin, hashPassword } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List all admin users
export async function GET() {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const pool = getPool();
    const result = await pool.query(
      `SELECT id, username, is_active, created_at, updated_at, last_login, created_by
       FROM admin_users
       ORDER BY created_at DESC`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
    });
  } catch (error) {
    console.error('Error fetching users:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new admin user
export async function POST(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const body = await request.json();
    const { username, password } = body;

    if (!username || !password) {
      return NextResponse.json(
        { success: false, error: 'Укажите имя пользователя и пароль' },
        { status: 400 }
      );
    }

    if (username.length < 3) {
      return NextResponse.json(
        { success: false, error: 'Имя пользователя должно содержать минимум 3 символа' },
        { status: 400 }
      );
    }

    if (password.length < 6) {
      return NextResponse.json(
        { success: false, error: 'Пароль должен содержать минимум 6 символов' },
        { status: 400 }
      );
    }

    const pool = getPool();

    // Check if username already exists
    const existing = await pool.query(
      'SELECT id FROM admin_users WHERE username = $1',
      [username]
    );

    if (existing.rows.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Пользователь с таким именем уже существует' },
        { status: 400 }
      );
    }

    const passwordHash = hashPassword(password);

    const result = await pool.query(
      `INSERT INTO admin_users (username, password_hash, is_active, created_by)
       VALUES ($1, $2, true, $3)
       RETURNING id, username, is_active, created_at`,
      [username, passwordHash, currentAdmin.id]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating user:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
