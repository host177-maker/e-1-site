import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Ensure designers table exists
async function ensureDesignersTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS designers (
      id SERIAL PRIMARY KEY,
      business_type VARCHAR(50) NOT NULL,
      full_name VARCHAR(255) NOT NULL,
      phone VARCHAR(50) NOT NULL,
      email VARCHAR(255) NOT NULL,
      portfolio_link TEXT,
      promo_code VARCHAR(50),
      is_active BOOLEAN DEFAULT false,
      step_completed INTEGER DEFAULT 1,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Migration: Add discount_percent column if it doesn't exist (default 0%)
  await pool.query(`
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'designers' AND column_name = 'discount_percent') THEN
        ALTER TABLE designers ADD COLUMN discount_percent DECIMAL(5, 2) DEFAULT 0;
      END IF;
    END $$;
  `);
}

// GET: List all designers
export async function GET(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    await ensureDesignersTable();

    const { searchParams } = new URL(request.url);
    const limit = parseInt(searchParams.get('limit') || '50');
    const offset = parseInt(searchParams.get('offset') || '0');
    const status = searchParams.get('status'); // 'active', 'inactive', 'pending', 'all'
    const search = searchParams.get('search') || '';

    const pool = getPool();

    let whereClause = '';
    const conditions: string[] = [];

    if (status === 'active') {
      conditions.push('is_active = true');
    } else if (status === 'inactive') {
      conditions.push('is_active = false AND step_completed = 2');
    } else if (status === 'pending') {
      conditions.push('step_completed = 1');
    }

    if (search) {
      conditions.push(`(
        full_name ILIKE '%' || $3 || '%' OR
        email ILIKE '%' || $3 || '%' OR
        phone ILIKE '%' || $3 || '%' OR
        promo_code ILIKE '%' || $3 || '%'
      )`);
    }

    if (conditions.length > 0) {
      whereClause = 'WHERE ' + conditions.join(' AND ');
    }

    const queryParams: (number | string)[] = [limit, offset];
    if (search) {
      queryParams.push(search);
    }

    const result = await pool.query(
      `SELECT *
       FROM designers
       ${whereClause}
       ORDER BY created_at DESC
       LIMIT $1 OFFSET $2`,
      queryParams
    );

    // Get counts
    const countResult = await pool.query(
      `SELECT
        COUNT(*) FILTER (WHERE is_active = true) as active_count,
        COUNT(*) FILTER (WHERE is_active = false AND step_completed = 2) as inactive_count,
        COUNT(*) FILTER (WHERE step_completed = 1) as pending_count,
        COUNT(*) as total_count
       FROM designers`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
      counts: {
        active: parseInt(countResult.rows[0].active_count),
        inactive: parseInt(countResult.rows[0].inactive_count),
        pending: parseInt(countResult.rows[0].pending_count),
        total: parseInt(countResult.rows[0].total_count),
      },
      pagination: {
        limit,
        offset,
        hasMore: result.rows.length === limit,
      },
    });
  } catch (error) {
    console.error('Error fetching designers:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new designer (from admin)
export async function POST(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    await ensureDesignersTable();

    const body = await request.json();
    const {
      business_type,
      full_name,
      phone,
      email,
      portfolio_link,
      promo_code,
      is_active,
      discount_percent
    } = body;

    if (!business_type || !full_name || !phone || !email) {
      return NextResponse.json(
        { success: false, error: 'Заполните все обязательные поля' },
        { status: 400 }
      );
    }

    const pool = getPool();

    // Check if email already exists
    const existingEmail = await pool.query(
      `SELECT id FROM designers WHERE LOWER(email) = LOWER($1)`,
      [email]
    );

    if (existingEmail.rows.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Этот email уже зарегистрирован' },
        { status: 400 }
      );
    }

    // Check if promo code is unique
    if (promo_code) {
      const normalizedPromoCode = promo_code.trim().toUpperCase();
      const existingPromo = await pool.query(
        `SELECT id FROM designers WHERE UPPER(promo_code) = $1`,
        [normalizedPromoCode]
      );

      if (existingPromo.rows.length > 0) {
        return NextResponse.json(
          { success: false, error: 'Этот промокод уже занят' },
          { status: 400 }
        );
      }
    }

    const result = await pool.query(
      `INSERT INTO designers (business_type, full_name, phone, email, portfolio_link, promo_code, is_active, step_completed, discount_percent)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
       RETURNING *`,
      [
        business_type,
        full_name.trim(),
        phone.trim(),
        email.trim().toLowerCase(),
        portfolio_link?.trim() || null,
        promo_code ? promo_code.trim().toUpperCase() : null,
        is_active || false,
        promo_code ? 2 : 1,
        discount_percent || 0
      ]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating designer:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
