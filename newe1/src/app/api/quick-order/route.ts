import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getEmailByKey, EMAIL_KEYS } from '@/lib/emailSettings';

interface QuickOrderRequest {
  name: string;
  phone: string;
  email?: string;
  comment?: string;
  productName: string;
  productUrl: string;
  selectedParams: {
    width: number | null;
    height: number | null;
    depth: number | null;
    bodyColor: string | null;
    profileColor: string | null;
    filling: string | null;
  };
}

async function sendEmail(data: QuickOrderRequest): Promise<boolean> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';

  // Get sales email from database
  const salesMail = await getEmailByKey(EMAIL_KEYS.SALES);

  if (!smtpServer || !salesMail) {
    console.error('Email not configured: missing SMTP_SERVER or sales email');
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

  // Build parameters string
  const params = data.selectedParams;
  const paramsString = [
    params.width && params.height && params.depth
      ? `Размер: ${params.width}×${params.height}×${params.depth} мм`
      : null,
    params.bodyColor ? `Цвет корпуса: ${params.bodyColor}` : null,
    params.profileColor ? `Цвет профиля: ${params.profileColor}` : null,
    params.filling ? `Наполнение: ${params.filling}` : null,
  ].filter(Boolean).join('<br>');

  const emailHtml = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Быстрый заказ</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
          Новый быстрый заказ с сайта
        </h2>

        <h3 style="color: #333; margin-top: 20px;">Данные клиента</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">ФИО:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.name}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Телефон:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="tel:${data.phone}" style="color: #62bb46;">${data.phone}</a>
            </td>
          </tr>
          ${data.email ? `
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">E-mail:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="mailto:${data.email}" style="color: #62bb46;">${data.email}</a>
            </td>
          </tr>
          ` : ''}
          ${data.comment ? `
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; vertical-align: top;">Комментарий:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee; white-space: pre-wrap;">${data.comment}</td>
          </tr>
          ` : ''}
        </table>

        <h3 style="color: #333; margin-top: 20px;">Информация о товаре</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">Наименование:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.productName}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; vertical-align: top;">Параметры:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${paramsString || 'Не указаны'}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Ссылка:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="${data.productUrl}" style="color: #62bb46;">${data.productUrl}</a>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Дата заявки:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${currentDate}</td>
          </tr>
        </table>

        <p style="color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
          Это автоматическое сообщение от сайта e-1.ru
        </p>
      </div>
    </body>
    </html>
  `;

  const paramsText = [
    params.width && params.height && params.depth
      ? `Размер: ${params.width}×${params.height}×${params.depth} мм`
      : null,
    params.bodyColor ? `Цвет корпуса: ${params.bodyColor}` : null,
    params.profileColor ? `Цвет профиля: ${params.profileColor}` : null,
    params.filling ? `Наполнение: ${params.filling}` : null,
  ].filter(Boolean).join('\n');

  const emailText = `
Новый быстрый заказ с сайта

Данные клиента:
ФИО: ${data.name}
Телефон: ${data.phone}
${data.email ? `E-mail: ${data.email}` : ''}
${data.comment ? `Комментарий: ${data.comment}` : ''}

Информация о товаре:
Наименование: ${data.productName}
Параметры:
${paramsText || 'Не указаны'}
Ссылка: ${data.productUrl}
Дата заявки: ${currentDate}
  `.trim();

  try {
    await transporter.sendMail({
      from: `"${smtpFromName}" <${smtpFrom}>`,
      to: salesMail,
      subject: `Быстрый заказ: ${data.productName}`,
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
    const body: QuickOrderRequest = await request.json();

    // Validate required fields
    if (!body.name || !body.phone) {
      return NextResponse.json(
        { success: false, error: 'ФИО и телефон обязательны для заполнения' },
        { status: 400 }
      );
    }

    // Validate phone format (basic check)
    const phoneClean = body.phone.replace(/\D/g, '');
    if (phoneClean.length < 10) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат телефона' },
        { status: 400 }
      );
    }

    const emailSent = await sendEmail(body);

    if (emailSent) {
      return NextResponse.json({
        success: true,
        message: 'Ваша заявка успешно отправлена. Менеджер свяжется с вами в ближайшее время.',
      });
    }

    return NextResponse.json(
      {
        success: false,
        error: 'Не удалось отправить заявку. Пожалуйста, позвоните нам по телефону 8-800-100-12-11',
      },
      { status: 500 }
    );

  } catch (error) {
    console.error('Quick order error:', error);
    return NextResponse.json(
      {
        success: false,
        error: 'Произошла ошибка при обработке заявки',
      },
      { status: 500 }
    );
  }
}
