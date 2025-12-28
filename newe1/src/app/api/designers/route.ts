import { NextRequest, NextResponse } from 'next/server';
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

  // Add index for promo_code uniqueness check
  await pool.query(`
    CREATE UNIQUE INDEX IF NOT EXISTS designers_promo_code_unique
    ON designers (UPPER(promo_code))
    WHERE promo_code IS NOT NULL AND promo_code != ''
  `);
}

// POST: Create new designer (Step 1) or update with promo code (Step 2)
export async function POST(request: NextRequest) {
  try {
    await ensureDesignersTable();

    const body = await request.json();
    const {
      business_type,
      full_name,
      phone,
      email,
      portfolio_link,
      promo_code,
      designer_id,
      step
    } = body;

    const pool = getPool();

    // Step 2: Update existing designer with promo code
    if (step === 2 && designer_id) {
      if (!promo_code || promo_code.trim() === '') {
        return NextResponse.json(
          { success: false, error: 'Промокод обязателен' },
          { status: 400 }
        );
      }

      const normalizedPromoCode = promo_code.trim().toUpperCase();

      // Validate promo code format (only letters and numbers)
      if (!/^[A-Z0-9]+$/.test(normalizedPromoCode)) {
        return NextResponse.json(
          { success: false, error: 'Промокод может содержать только буквы английского алфавита и цифры' },
          { status: 400 }
        );
      }

      // Check if promo code is unique (excluding current designer)
      const existingPromo = await pool.query(
        `SELECT id FROM designers WHERE UPPER(promo_code) = $1 AND id != $2`,
        [normalizedPromoCode, designer_id]
      );

      if (existingPromo.rows.length > 0) {
        return NextResponse.json(
          { success: false, error: 'Этот промокод уже занят. Пожалуйста, выберите другой.' },
          { status: 400 }
        );
      }

      // Update designer with promo code
      const result = await pool.query(
        `UPDATE designers
         SET promo_code = $1, step_completed = 2, updated_at = CURRENT_TIMESTAMP
         WHERE id = $2
         RETURNING *`,
        [normalizedPromoCode, designer_id]
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
        message: 'Регистрация успешно завершена! Мы свяжемся с вами для активации промокода после проверки анкеты.'
      });
    }

    // Step 1: Create new designer
    if (!business_type || !full_name || !phone || !email) {
      return NextResponse.json(
        { success: false, error: 'Заполните все обязательные поля' },
        { status: 400 }
      );
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат email' },
        { status: 400 }
      );
    }

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

    // Insert new designer
    const result = await pool.query(
      `INSERT INTO designers (business_type, full_name, phone, email, portfolio_link, step_completed)
       VALUES ($1, $2, $3, $4, $5, 1)
       RETURNING *`,
      [business_type, full_name.trim(), phone.trim(), email.trim().toLowerCase(), portfolio_link?.trim() || null]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
      message: 'Данные сохранены. Перейдите к выбору промокода.'
    });

  } catch (error) {
    console.error('Error in designers API:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
