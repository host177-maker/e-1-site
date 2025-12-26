import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';

interface ContactRequest {
  name: string;
  phone: string;
  message: string;
}

async function sendEmail(data: ContactRequest): Promise<boolean> {
  const smtpServer = process.env.SMTP_SERVER;
  const smtpPort = parseInt(process.env.SMTP_PORT || '25', 10);
  const smtpUser = process.env.SMTP_USER;
  const smtpPassword = process.env.SMTP_PASSWORD;
  const smtpFrom = process.env.SMTP_FROM || 'robot@e-1.ru';
  const smtpFromName = process.env.SMTP_FROM_NAME || 'Мебельная компания Е1';
  const dirMail = process.env.DIR_MAIL;

  // Debug logging
  console.log('ENV DEBUG:', {
    SMTP_SERVER: smtpServer ? `${smtpServer.substring(0, 5)}...` : 'undefined',
    SMTP_PORT: smtpPort,
    SMTP_USER: smtpUser ? 'set' : 'undefined',
    SMTP_PASSWORD: smtpPassword ? 'set' : 'undefined',
    SMTP_FROM: smtpFrom,
    DIR_MAIL: dirMail ? `${dirMail.substring(0, 5)}...` : 'undefined',
    NODE_ENV: process.env.NODE_ENV,
  });

  if (!smtpServer || !dirMail) {
    console.error('Email not configured: missing SMTP_SERVER or DIR_MAIL');
    console.error('SMTP_SERVER value:', smtpServer);
    console.error('DIR_MAIL value:', dirMail);
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
      <title>Обращение к директору</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #62bb46; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">
          Обращение к директору с сайта
        </h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">Имя:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">${data.name}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Телефон:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee;">
              <a href="tel:${data.phone}" style="color: #62bb46;">${data.phone}</a>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; vertical-align: top;">Сообщение:</td>
            <td style="padding: 10px; border-bottom: 1px solid #eee; white-space: pre-wrap;">${data.message}</td>
          </tr>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Дата обращения:</td>
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

  const emailText = `
Обращение к директору с сайта

Имя: ${data.name}
Телефон: ${data.phone}
Сообщение: ${data.message}
Дата обращения: ${currentDate}
  `.trim();

  try {
    await transporter.sendMail({
      from: `"${smtpFromName}" <${smtpFrom}>`,
      to: dirMail,
      subject: `Обращение к директору от ${data.name}`,
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
    const body: ContactRequest = await request.json();

    // Validate required fields
    if (!body.name || !body.phone || !body.message) {
      return NextResponse.json(
        { success: false, message: 'Все поля обязательны для заполнения' },
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

    const emailSent = await sendEmail(body);

    if (emailSent) {
      return NextResponse.json({
        success: true,
        message: 'Ваше обращение успешно отправлено директору.',
      });
    }

    return NextResponse.json(
      {
        success: false,
        message: 'Не удалось отправить обращение. Пожалуйста, позвоните нам по телефону 8-800-100-12-11',
      },
      { status: 500 }
    );

  } catch (error) {
    console.error('Contact director error:', error);
    return NextResponse.json(
      {
        success: false,
        message: 'Произошла ошибка при обработке обращения',
      },
      { status: 500 }
    );
  }
}
