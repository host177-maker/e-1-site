import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// POST: Verify promo code and return discount
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { promo_code } = body;

    if (!promo_code || !promo_code.trim()) {
      return NextResponse.json(
        { success: false, error: 'Введите промокод' },
        { status: 400 }
      );
    }

    const pool = getPool();
    const normalizedCode = promo_code.trim().toUpperCase();

    // Look for active designer with this promo code
    const result = await pool.query(
      `SELECT id, full_name, promo_code, discount_percent
       FROM designers
       WHERE UPPER(promo_code) = $1 AND is_active = true`,
      [normalizedCode]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Промокод не найден или не активен' },
        { status: 404 }
      );
    }

    const designer = result.rows[0];
    const discountPercent = parseFloat(designer.discount_percent) || 0;

    if (discountPercent <= 0) {
      return NextResponse.json(
        { success: false, error: 'Промокод не активен' },
        { status: 400 }
      );
    }

    return NextResponse.json({
      success: true,
      data: {
        promo_code: designer.promo_code,
        discount_percent: discountPercent,
        designer_name: designer.full_name,
      },
    });
  } catch (error) {
    console.error('Error verifying promo code:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
