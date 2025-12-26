import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

interface Review {
  id: number;
  name: string;
  review_text: string;
  company_response: string | null;
  rating: number | null;
  photos: string[] | null;
  created_at: string;
}

interface NewReviewRequest {
  name: string;
  phone: string;
  order_number?: string;
  review_text: string;
  rating: number;
  photos?: string[];
}

// GET: Fetch reviews
export async function GET(request: NextRequest) {
  try {
    const { Pool } = await import('pg');

    const pool = new Pool({
      host: process.env.POSTGRES_HOST || '192.168.40.41',
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB || 'newe1',
      user: process.env.POSTGRES_USER || 'newe1',
      password: process.env.POSTGRES_PASSWORD || 'newe1pass',
    });

    const { searchParams } = new URL(request.url);
    const limit = parseInt(searchParams.get('limit') || '50');
    const offset = parseInt(searchParams.get('offset') || '0');
    const onlyMain = searchParams.get('main') === 'true';

    let query = `
      SELECT id, name, review_text, company_response, rating, photos, created_at
      FROM reviews
      WHERE is_active = true
    `;
    const params: (number | boolean)[] = [];

    if (onlyMain) {
      query += ` AND show_on_main = true`;
    }

    query += ` ORDER BY created_at DESC`;
    query += ` LIMIT $${params.length + 1} OFFSET $${params.length + 2}`;
    params.push(limit, offset);

    const result = await pool.query(query, params);

    // Get total count
    let countQuery = `SELECT COUNT(*) FROM reviews WHERE is_active = true`;
    if (onlyMain) {
      countQuery += ` AND show_on_main = true`;
    }
    const countResult = await pool.query(countQuery);
    const total = parseInt(countResult.rows[0].count);

    // Get average rating
    const avgResult = await pool.query(
      `SELECT AVG(rating)::numeric(2,1) as avg_rating, COUNT(*) as rated_count
       FROM reviews WHERE is_active = true AND rating IS NOT NULL`
    );

    await pool.end();

    return NextResponse.json({
      success: true,
      data: result.rows as Review[],
      pagination: {
        total,
        limit,
        offset,
        hasMore: offset + limit < total,
      },
      stats: {
        averageRating: avgResult.rows[0].avg_rating ? parseFloat(avgResult.rows[0].avg_rating) : null,
        totalRated: parseInt(avgResult.rows[0].rated_count),
      },
    });
  } catch (error) {
    console.error('Error fetching reviews:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to fetch reviews' },
      { status: 500 }
    );
  }
}

// POST: Submit a new review
export async function POST(request: NextRequest) {
  try {
    const body: NewReviewRequest = await request.json();

    // Validate required fields
    if (!body.name || !body.phone || !body.review_text || !body.rating || !body.order_number) {
      return NextResponse.json(
        { success: false, message: 'Все обязательные поля должны быть заполнены' },
        { status: 400 }
      );
    }

    // Validate rating
    if (body.rating < 1 || body.rating > 5) {
      return NextResponse.json(
        { success: false, message: 'Оценка должна быть от 1 до 5' },
        { status: 400 }
      );
    }

    // Validate phone format
    const phoneClean = body.phone.replace(/\D/g, '');
    if (phoneClean.length < 10) {
      return NextResponse.json(
        { success: false, message: 'Неверный формат телефона' },
        { status: 400 }
      );
    }

    // Validate photos count
    if (body.photos && body.photos.length > 5) {
      return NextResponse.json(
        { success: false, message: 'Можно прикрепить не более 5 фотографий' },
        { status: 400 }
      );
    }

    const { Pool } = await import('pg');

    const pool = new Pool({
      host: process.env.POSTGRES_HOST || '192.168.40.41',
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB || 'newe1',
      user: process.env.POSTGRES_USER || 'newe1',
      password: process.env.POSTGRES_PASSWORD || 'newe1pass',
    });

    // Insert review with is_active = false (needs moderation)
    const result = await pool.query(
      `INSERT INTO reviews (name, phone, order_number, review_text, rating, photos, is_active, created_at)
       VALUES ($1, $2, $3, $4, $5, $6, false, NOW())
       RETURNING id`,
      [
        body.name,
        body.phone,
        body.order_number || null,
        body.review_text,
        body.rating,
        body.photos && body.photos.length > 0 ? body.photos : null,
      ]
    );

    await pool.end();

    // Send email notification
    await sendReviewNotification(body);

    return NextResponse.json({
      success: true,
      message: 'Спасибо за ваш отзыв! После модерации он появится на сайте.',
      reviewId: result.rows[0].id,
    });
  } catch (error) {
    console.error('Error submitting review:', error);
    return NextResponse.json(
      { success: false, message: 'Произошла ошибка при отправке отзыва' },
      { status: 500 }
    );
  }
}

async function sendReviewNotification(review: NewReviewRequest): Promise<void> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';
  const saleMail = process.env.SALE_MAIL;

  if (!smtpServer || !saleMail) {
    console.log('Email notification not configured');
    return;
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

  const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);

  const emailHtml = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Новый отзыв на сайте</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
          Новый отзыв на сайте
        </h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">Имя:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${review.name}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Телефон:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="tel:${review.phone}" style="color: #62bb46;">${review.phone}</a>
            </td>
          </tr>
          ${review.order_number ? `
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Номер заказа:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${review.order_number}</td>
          </tr>
          ` : ''}
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Оценка:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-size: 18px; color: #ffc107;">${stars}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; vertical-align: top;">Отзыв:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${review.review_text}</td>
          </tr>
          ${review.photos && review.photos.length > 0 ? `
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Фотографий:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${review.photos.length} шт.</td>
          </tr>
          ` : ''}
        </table>

        <p style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px;">
          <strong>Отзыв требует модерации.</strong> Войдите в админ-панель для публикации.
        </p>
      </div>
    </body>
    </html>
  `;

  try {
    await transporter.sendMail({
      from: `"${smtpFromName}" <${smtpFrom}>`,
      to: saleMail,
      subject: `Новый отзыв от ${review.name} (${review.rating}/5)`,
      html: emailHtml,
    });
  } catch (error) {
    console.error('Failed to send review notification:', error);
  }
}
