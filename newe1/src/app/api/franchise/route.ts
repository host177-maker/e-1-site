import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';

interface FranchiseRequest {
  full_name: string;
  city: string;
  phone: string;
  email: string;
}

async function sendFranchiseNotification(data: FranchiseRequest): Promise<boolean> {
  const dizMail = process.env.DIZMAIL;

  if (!dizMail) {
    console.error('DIZMAIL environment variable is not set');
    return false;
  }

  try {
    const transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST || 'smtp.mail.ru',
      port: parseInt(process.env.SMTP_PORT || '465'),
      secure: true,
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
      },
    });

    const mailOptions = {
      from: process.env.SMTP_USER,
      to: dizMail,
      subject: `Новая заявка на франшизу от ${data.full_name}`,
      html: `
        <h2>Новая заявка на франшизу Е1</h2>
        <table style="border-collapse: collapse; width: 100%; max-width: 500px;">
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">ФИО:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${data.full_name}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Город:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${data.city}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Телефон:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${data.phone}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Email:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${data.email}</td>
          </tr>
        </table>
        <p style="margin-top: 20px; color: #666;">
          Заявка отправлена с сайта e-1.ru
        </p>
      `,
    };

    await transporter.sendMail(mailOptions);
    console.log('Franchise notification email sent successfully');
    return true;
  } catch (error) {
    console.error('Error sending franchise notification email:', error);
    return false;
  }
}

export async function POST(request: NextRequest) {
  try {
    const body: FranchiseRequest = await request.json();

    // Validate required fields
    if (!body.full_name || !body.city || !body.phone || !body.email) {
      return NextResponse.json(
        { success: false, error: 'Все поля обязательны для заполнения' },
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

    // Send email notification (async, don't wait)
    sendFranchiseNotification(body).catch(err => {
      console.error('Failed to send franchise notification:', err);
    });

    return NextResponse.json({
      success: true,
      message: 'Заявка успешно отправлена',
    });
  } catch (error) {
    console.error('Franchise API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
