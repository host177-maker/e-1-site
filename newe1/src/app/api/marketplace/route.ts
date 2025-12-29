import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getEmailByKey, EMAIL_KEYS } from '@/lib/emailSettings';
import { createB2BLead } from '@/lib/b2bLeads';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

interface MarketplaceRequest {
  full_name: string;
  phone: string;
  company_name?: string;
  email: string;
}

async function sendMarketplaceNotification(data: MarketplaceRequest): Promise<boolean> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';

  // Get email from database, fallback to env
  const marketplaceMail = await getEmailByKey(EMAIL_KEYS.MARKETPLACE);

  if (!smtpServer || !marketplaceMail) {
    console.error('Email not configured: missing SMTP_SERVER or marketplace email');
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
      to: marketplaceMail,
      subject: `Заявка от селлера маркетплейса: ${data.full_name}`,
      html: `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Заявка от селлера</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
          <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
              Новая заявка от селлера маркетплейса
            </h2>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">ФИО:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.full_name}</td>
              </tr>
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Телефон:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">
                  <a href="tel:${data.phone}" style="color: #62bb46;">${data.phone}</a>
                </td>
              </tr>
              ${data.company_name ? `
              <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Компания:</td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.company_name}</td>
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
    console.log('Marketplace notification email sent successfully to', marketplaceMail);
    return true;
  } catch (error) {
    console.error('Error sending marketplace notification email:', error);
    return false;
  }
}

export async function POST(request: NextRequest) {
  try {
    const body: MarketplaceRequest = await request.json();

    // Validate required fields
    if (!body.full_name || !body.phone || !body.email) {
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

    // Validate phone
    const phoneDigits = body.phone.replace(/\D/g, '');
    if (phoneDigits.length < 11) {
      return NextResponse.json(
        { success: false, error: 'Некорректный номер телефона' },
        { status: 400 }
      );
    }

    // Save to database
    try {
      await createB2BLead({
        lead_type: 'marketplace',
        full_name: body.full_name,
        phone: body.phone,
        email: body.email,
        company_name: body.company_name,
      });
    } catch (dbError) {
      console.error('Failed to save marketplace lead to DB:', dbError);
    }

    // Send email notification (async, don't wait)
    sendMarketplaceNotification(body).catch(err => {
      console.error('Failed to send marketplace notification:', err);
    });

    return NextResponse.json({
      success: true,
      message: 'Заявка успешно отправлена',
    });
  } catch (error) {
    console.error('Marketplace API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
