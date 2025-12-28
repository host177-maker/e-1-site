import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Check if promo code is available
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const promoCode = searchParams.get('code');
    const designerId = searchParams.get('designer_id');

    if (!promoCode || promoCode.trim() === '') {
      return NextResponse.json(
        { success: false, error: 'Промокод не указан' },
        { status: 400 }
      );
    }

    const normalizedPromoCode = promoCode.trim().toUpperCase();

    // Validate promo code format (only letters and numbers)
    if (!/^[A-Z0-9]+$/.test(normalizedPromoCode)) {
      return NextResponse.json({
        success: true,
        available: false,
        normalized: normalizedPromoCode,
        error: 'Промокод может содержать только буквы английского алфавита и цифры'
      });
    }

    // Check minimum length
    if (normalizedPromoCode.length < 3) {
      return NextResponse.json({
        success: true,
        available: false,
        normalized: normalizedPromoCode,
        error: 'Промокод должен содержать минимум 3 символа'
      });
    }

    // Check maximum length
    if (normalizedPromoCode.length > 20) {
      return NextResponse.json({
        success: true,
        available: false,
        normalized: normalizedPromoCode,
        error: 'Промокод не должен превышать 20 символов'
      });
    }

    const pool = getPool();

    // Build query to check if promo code exists (excluding current designer if provided)
    let query = `SELECT id FROM designers WHERE UPPER(promo_code) = $1`;
    const params: (string | number)[] = [normalizedPromoCode];

    if (designerId) {
      query += ` AND id != $2`;
      params.push(parseInt(designerId));
    }

    const result = await pool.query(query, params);

    const isAvailable = result.rows.length === 0;

    return NextResponse.json({
      success: true,
      available: isAvailable,
      normalized: normalizedPromoCode,
      message: isAvailable
        ? 'Промокод свободен и доступен для использования!'
        : 'Этот промокод уже занят. Пожалуйста, выберите другой.'
    });

  } catch (error) {
    console.error('Error checking promo code:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
