import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getEmailByKey, EMAIL_KEYS } from '@/lib/emailSettings';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

const topicLabels: Record<string, string> = {
  materials: 'Поставка материалов',
  logistics: 'Логистика',
};

export async function POST(request: NextRequest) {
  try {
    const formData = await request.formData();

    const topic = formData.get('topic') as string;
    const full_name = formData.get('full_name') as string;
    const phone = formData.get('phone') as string;
    const company_name = formData.get('company_name') as string;
    const proposal = formData.get('proposal') as string;
    const file = formData.get('file') as File | null;

    // Validate required fields
    if (!full_name || !phone || !company_name || !proposal) {
      return NextResponse.json(
        { success: false, error: 'Заполните все обязательные поля' },
        { status: 400 }
      );
    }

    // Get procurement email from database or env
    const procurementEmail = await getEmailByKey(EMAIL_KEYS.PROCUREMENT);

    if (!procurementEmail) {
      console.error('Procurement email not configured');
      return NextResponse.json(
        { success: false, error: 'Email не настроен' },
        { status: 500 }
      );
    }

    // Create transporter
    const transporter = nodemailer.createTransport({
      host: process.env.SMTP_SERVER,
      port: 465,
      secure: true,
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASSWORD,
      },
    });

    // Prepare attachments
    const attachments: Array<{ filename: string; content: Buffer }> = [];

    if (file && file.size > 0) {
      const bytes = await file.arrayBuffer();
      const buffer = Buffer.from(bytes);
      attachments.push({
        filename: file.name,
        content: buffer,
      });
    }

    // Send email
    await transporter.sendMail({
      from: process.env.SMTP_USER,
      to: procurementEmail,
      subject: `Заявка от поставщика: ${topicLabels[topic] || topic}`,
      html: `
        <h2>Новая заявка от поставщика</h2>
        <table style="border-collapse: collapse; width: 100%; max-width: 600px;">
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">Тема заявки</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${topicLabels[topic] || topic}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">ФИО</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${full_name}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">Телефон</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${phone}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">Компания</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${company_name}</td>
          </tr>
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">Предложение</td>
            <td style="padding: 8px; border: 1px solid #ddd; white-space: pre-wrap;">${proposal}</td>
          </tr>
          ${file ? `
          <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background: #f5f5f5; font-weight: bold;">Приложение</td>
            <td style="padding: 8px; border: 1px solid #ddd;">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} МБ)</td>
          </tr>
          ` : ''}
        </table>
        <p style="color: #666; font-size: 12px; margin-top: 20px;">
          Отправлено с сайта e-1.ru
        </p>
      `,
      attachments,
    });

    return NextResponse.json({
      success: true,
      message: 'Заявка успешно отправлена',
    });
  } catch (error) {
    console.error('Error sending supplier application:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка отправки заявки' },
      { status: 500 }
    );
  }
}
