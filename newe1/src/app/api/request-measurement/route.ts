import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getEmailByKey, EMAIL_KEYS } from '@/lib/emailSettings';

interface MeasurementRequest {
  name: string;
  phone: string;
  message?: string;
}

interface WebhookResponse {
  success: boolean;
  deal_id?: number;
  assigned_to?: string;
  message?: string;
}

async function sendToWebhook(data: MeasurementRequest): Promise<{ success: boolean; response?: WebhookResponse; error?: string }> {
  try {
    const response = await fetch('https://booking.e-1.ru/crm/webhook', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        phone1: data.phone,
        source: 'Форма на сайте',
        fio: data.name,
        description: data.message || '',
      }),
    });

    if (!response.ok) {
      return {
        success: false,
        error: `Webhook HTTP error: ${response.status} ${response.statusText}`,
      };
    }

    const result: WebhookResponse = await response.json();

    if (result.success) {
      return { success: true, response: result };
    } else {
      return {
        success: false,
        error: result.message || 'Webhook returned unsuccessful response',
      };
    }
  } catch (error) {
    return {
      success: false,
      error: `Webhook error: ${error instanceof Error ? error.message : 'Unknown error'}`,
    };
  }
}

async function sendEmailFallback(data: MeasurementRequest, webhookError: string): Promise<boolean> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';

  // Get email from database, fallback to env
  const saleMail = await getEmailByKey(EMAIL_KEYS.SALES);

  if (!smtpServer || !saleMail) {
    console.error('Email fallback not configured: missing SMTP_SERVER or sales email');
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

  const emailHtml = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Новая заявка на замер</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
          Новая заявка на замер с сайта
        </h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">Имя клиента:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.name}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Телефон:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="tel:${data.phone}" style="color: #62bb46;">${data.phone}</a>
            </td>
          </tr>
          ${data.message ? `
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; vertical-align: top;">Сообщение:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.message}</td>
          </tr>
          ` : ''}
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Дата заявки:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${currentDate}</td>
          </tr>
        </table>

        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-top: 20px;">
          <strong style="color: #856404;">Внимание!</strong>
          <p style="margin: 10px 0 0 0; color: #856404;">
            Заявка отправлена по email, так как CRM webhook не принял данные.<br>
            Причина: ${webhookError}
          </p>
        </div>

        <p style="color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
          Это автоматическое сообщение от сайта e-1.ru
        </p>
      </div>
    </body>
    </html>
  `;

  const emailText = `
Новая заявка на замер с сайта

Имя клиента: ${data.name}
Телефон: ${data.phone}
${data.message ? `Сообщение: ${data.message}` : ''}
Дата заявки: ${currentDate}

ВНИМАНИЕ: Заявка отправлена по email, так как CRM webhook не принял данные.
Причина: ${webhookError}
  `.trim();

  try {
    await transporter.sendMail({
      from: `"${smtpFromName}" <${smtpFrom}>`,
      to: saleMail,
      subject: `Заявка на замер от ${data.name} - ${data.phone}`,
      text: emailText,
      html: emailHtml,
    });
    return true;
  } catch (error) {
    console.error('Email sending failed:', error);
    return false;
  }
}

export async function POST(request: NextRequest) {
  try {
    const body: MeasurementRequest = await request.json();

    // Validate required fields
    if (!body.name || !body.phone) {
      return NextResponse.json(
        { success: false, message: 'Имя и телефон обязательны для заполнения' },
        { status: 400 }
      );
    }

    // Validate phone format (basic check)
    const phoneClean = body.phone.replace(/\D/g, '');
    if (phoneClean.length < 10) {
      return NextResponse.json(
        { success: false, message: 'Неверный формат телефона' },
        { status: 400 }
      );
    }

    // Try sending to webhook first
    const webhookResult = await sendToWebhook(body);

    if (webhookResult.success) {
      return NextResponse.json({
        success: true,
        message: 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.',
        deal_id: webhookResult.response?.deal_id,
      });
    }

    // Webhook failed, try email fallback
    console.log('Webhook failed, trying email fallback:', webhookResult.error);

    const emailSent = await sendEmailFallback(body, webhookResult.error || 'Unknown error');

    if (emailSent) {
      return NextResponse.json({
        success: true,
        message: 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.',
        fallback: true,
      });
    }

    // Both webhook and email failed
    return NextResponse.json(
      {
        success: false,
        message: 'Не удалось отправить заявку. Пожалуйста, позвоните нам по телефону 8-800-100-12-11',
      },
      { status: 500 }
    );

  } catch (error) {
    console.error('Request measurement error:', error);
    return NextResponse.json(
      {
        success: false,
        message: 'Произошла ошибка при обработке заявки',
      },
      { status: 500 }
    );
  }
}
