import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getEmailByKey, EMAIL_KEYS } from '@/lib/emailSettings';
import { createB2BLead } from '@/lib/b2bLeads';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

interface WholesaleRequest {
  full_name: string;
  city: string;
  company_name: string;
  website?: string;
  email: string;
}

async function sendWholesaleNotification(data: WholesaleRequest): Promise<boolean> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';

  // Get email from database, fallback to env
  const optMail = await getEmailByKey(EMAIL_KEYS.WHOLESALE);

  if (!smtpServer || !optMail) {
    console.error('Email not configured: missing SMTP_SERVER or wholesale email');
    return false;
  }

  try {
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

    const mailOptions = {
      from: `"${smtpFromName}" <${smtpFrom}>`,
      to: optMail,
      subject: `Заявка на оптовое сотрудничество от ${data.company_name}`,
      html: `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Заявка на оптовое сотрудничество</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
          <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
              Новая заявка на оптовое сотрудничество
            </h2>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">ФИО:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.full_name}</td>
              </tr>
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Город:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.city}</td>
              </tr>
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Компания:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.company_name}</td>
              </tr>
              ${data.website ? `
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Сайт:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">
                  <a href="${data.website}" style="color: #62bb46;">${data.website}</a>
                </td>
              </tr>
              ` : ''}
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Email:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">
                  <a href="mailto:${data.email}" style="color: #62bb46;">${data.email}</a>
                </td>
              </tr>
            </table>

            <p style="color: #888; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
              Заявка отправлена ${currentDate} с сайта e-1.ru
            </p>
          </div>
        </body>
        </html>
      `,
    };

    await transporter.sendMail(mailOptions);
    console.log('Wholesale notification email sent successfully to', optMail);
    return true;
  } catch (error) {
    console.error('Error sending wholesale notification email:', error);
    return false;
  }
}

export async function POST(request: NextRequest) {
  try {
    const body: WholesaleRequest = await request.json();

    // Validate required fields
    if (!body.full_name || !body.city || !body.company_name || !body.email) {
      return NextResponse.json(
        { success: false, error: 'Заполните все обязательные поля' },
        { status: 400 }
      );
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(body.email)) {
      return NextResponse.json(
        { success: false, error: 'Некорректный email' },
        { status: 400 }
      );
    }

    // Save to database
    try {
      await createB2BLead({
        lead_type: 'wholesale',
        full_name: body.full_name,
        phone: '', // Wholesale form doesn't have phone
        email: body.email,
        city: body.city,
        company_name: body.company_name,
        website: body.website,
      });
    } catch (dbError) {
      console.error('Failed to save wholesale lead to DB:', dbError);
    }

    // Send email notification (async, don't wait)
    sendWholesaleNotification(body).catch(err => {
      console.error('Failed to send wholesale notification:', err);
    });

    return NextResponse.json({
      success: true,
      message: 'Заявка успешно отправлена',
    });
  } catch (error) {
    console.error('Wholesale API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
