import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';
import nodemailer from 'nodemailer';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

const businessTypeLabels: Record<string, string> = {
  individual: 'Физическое лицо',
  self_employed: 'Самозанятый',
  individual_entrepreneur: 'Индивидуальный предприниматель',
  llc: 'ООО',
};

// Send email notification about new designer registration
async function sendDesignerNotification(designer: {
  id: number;
  business_type: string;
  full_name: string;
  phone: string;
  email: string;
  portfolio_link: string | null;
  promo_code: string;
}): Promise<boolean> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';
  const dizMail = process.env.DIZMAIL;

  if (!smtpServer || !dizMail) {
    console.error('Email not configured: missing SMTP_SERVER or DIZMAIL');
    return false;
  }

  const transporter = nodemailer.createTransport({
    host: smtpServer,
    port: smtpPort,
    secure: smtpPort === 465,
    auth: smtpUser && smtpPassword ? {
      user: smtpUser,
      pass: smtpPassword,
    } : undefined,
  });

  const currentDate = new Date().toLocaleString('ru-RU', {
    timeZone: 'Europe/Moscow',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });

  const businessTypeLabel = businessTypeLabels[designer.business_type] || designer.business_type;

  const emailHtml = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Новая регистрация дизайнера</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
          Новая заявка от дизайнера
        </h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 180px;">ID:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${designer.id}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">ФИО:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${designer.full_name}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Форма деятельности:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${businessTypeLabel}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Телефон:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="tel:${designer.phone}" style="color: #62bb46;">${designer.phone}</a>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Email:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="mailto:${designer.email}" style="color: #62bb46;">${designer.email}</a>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Портфолио:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              ${designer.portfolio_link
                ? `<a href="${designer.portfolio_link}" style="color: #62bb46;" target="_blank">${designer.portfolio_link}</a>`
                : '<span style="color: #999;">Не указано</span>'}
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Желаемый промокод:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <strong style="color: #62bb46; font-size: 16px;">${designer.promo_code}</strong>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Дата регистрации:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${currentDate}</td>
          </tr>
        </table>

        <p style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
          <strong>Следующий шаг:</strong> Проверьте портфолио дизайнера и активируйте промокод в
          <a href="${process.env.NEXT_PUBLIC_SITE_URL || 'https://e-1.ru'}/e1admin/designers" style="color: #62bb46;">админ-панели</a>.
        </p>

        <p style="color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
          Это автоматическое сообщение от сайта e-1.ru
        </p>
      </div>
    </body>
    </html>
  `;

  const emailText = `
Новая заявка от дизайнера

ID: ${designer.id}
ФИО: ${designer.full_name}
Форма деятельности: ${businessTypeLabel}
Телефон: ${designer.phone}
Email: ${designer.email}
Портфолио: ${designer.portfolio_link || 'Не указано'}
Желаемый промокод: ${designer.promo_code}
Дата регистрации: ${currentDate}

Проверьте портфолио дизайнера и активируйте промокод в админ-панели.
  `.trim();

  try {
    await transporter.sendMail({
      from: `"${smtpFromName}" <${smtpFrom}>`,
      to: dizMail,
      subject: `Новая заявка от дизайнера: ${designer.full_name}`,
      text: emailText,
      html: emailHtml,
    });
    console.log(`Designer notification email sent to ${dizMail}`);
    return true;
  } catch (error) {
    console.error('Designer notification email failed:', error);
    return false;
  }
}

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

      const designer = result.rows[0];

      // Send email notification (don't wait for it, don't fail if it fails)
      sendDesignerNotification({
        id: designer.id,
        business_type: designer.business_type,
        full_name: designer.full_name,
        phone: designer.phone,
        email: designer.email,
        portfolio_link: designer.portfolio_link,
        promo_code: normalizedPromoCode,
      }).catch(err => console.error('Failed to send designer notification:', err));

      return NextResponse.json({
        success: true,
        data: designer,
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
