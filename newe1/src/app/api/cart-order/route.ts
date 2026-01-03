import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getEmailByKey, EMAIL_KEYS } from '@/lib/emailSettings';

interface CartItem {
  name: string;
  slug: string;
  price: number;
  quantity: number;
  width?: number;
  height?: number;
  depth?: number;
  bodyColor?: string;
  profileColor?: string;
  filling?: string;
  includeAssembly: boolean;
  assemblyPrice: number;
}

interface CartOrderRequest {
  customerName: string;
  customerPhone: string;
  customerEmail?: string;
  comment?: string;
  items: CartItem[];
  totalPrice: number;
}

export async function POST(request: NextRequest) {
  try {
    const body: CartOrderRequest = await request.json();

    // Validation
    if (!body.customerName || !body.customerPhone || !body.items || body.items.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Не все обязательные поля заполнены' },
        { status: 400 }
      );
    }

    // Get sales email from settings
    const salesEmail = await getEmailByKey(EMAIL_KEYS.SALES);

    if (!salesEmail) {
      console.error('Sales email not configured');
      return NextResponse.json(
        { success: false, error: 'Email отдела продаж не настроен' },
        { status: 500 }
      );
    }

    // Create transporter
    const transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: Number(process.env.SMTP_PORT) || 587,
      secure: process.env.SMTP_SECURE === 'true',
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
      },
    });

    // Build items list HTML
    const itemsHtml = body.items.map((item, index) => `
      <tr style="border-bottom: 1px solid #e5e5e5;">
        <td style="padding: 12px 8px;">${index + 1}</td>
        <td style="padding: 12px 8px;">
          <strong>${item.name}</strong><br/>
          <span style="color: #666; font-size: 12px;">
            ${item.width && item.height && item.depth ? `Размер: ${item.width}×${item.height}×${item.depth} мм` : ''}<br/>
            ${item.bodyColor ? `Цвет корпуса: ${item.bodyColor}` : ''}<br/>
            ${item.profileColor ? `Цвет профиля: ${item.profileColor}` : ''}<br/>
            ${item.filling ? `Наполнение: ${item.filling}` : ''}<br/>
            ${item.includeAssembly ? `<span style="color: #62bb46;">+ Сборка: ${item.assemblyPrice.toLocaleString('ru-RU')} ₽</span>` : ''}
          </span>
        </td>
        <td style="padding: 12px 8px; text-align: center;">${item.quantity}</td>
        <td style="padding: 12px 8px; text-align: right;">${item.price.toLocaleString('ru-RU')} ₽</td>
        <td style="padding: 12px 8px; text-align: right; font-weight: bold;">
          ${((item.price + (item.includeAssembly ? item.assemblyPrice : 0)) * item.quantity).toLocaleString('ru-RU')} ₽
        </td>
      </tr>
    `).join('');

    // Build items list text
    const itemsText = body.items.map((item, index) => {
      const itemTotal = (item.price + (item.includeAssembly ? item.assemblyPrice : 0)) * item.quantity;
      return `${index + 1}. ${item.name}
   Размер: ${item.width}×${item.height}×${item.depth} мм
   Цвет корпуса: ${item.bodyColor || '-'}
   Цвет профиля: ${item.profileColor || '-'}
   Наполнение: ${item.filling || '-'}
   Сборка: ${item.includeAssembly ? `Да (+${item.assemblyPrice.toLocaleString('ru-RU')} ₽)` : 'Нет'}
   Количество: ${item.quantity}
   Цена за шт.: ${item.price.toLocaleString('ru-RU')} ₽
   Итого: ${itemTotal.toLocaleString('ru-RU')} ₽`;
    }).join('\n\n');

    // HTML email template
    const htmlContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Новый заказ из корзины</title>
      </head>
      <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #62bb46 0%, #4a9935 100%); padding: 20px; border-radius: 10px 10px 0 0;">
          <h1 style="color: white; margin: 0; font-size: 24px;">Новый заказ из корзины</h1>
        </div>

        <div style="background: #f9f9f9; padding: 20px; border: 1px solid #e5e5e5; border-top: none;">
          <h2 style="color: #333; border-bottom: 2px solid #62bb46; padding-bottom: 10px;">Данные покупателя</h2>
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="padding: 8px 0; color: #666; width: 150px;">ФИО:</td>
              <td style="padding: 8px 0; font-weight: bold;">${body.customerName}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; color: #666;">Телефон:</td>
              <td style="padding: 8px 0; font-weight: bold;">${body.customerPhone}</td>
            </tr>
            ${body.customerEmail ? `
            <tr>
              <td style="padding: 8px 0; color: #666;">Email:</td>
              <td style="padding: 8px 0;">${body.customerEmail}</td>
            </tr>
            ` : ''}
            ${body.comment ? `
            <tr>
              <td style="padding: 8px 0; color: #666;">Комментарий:</td>
              <td style="padding: 8px 0;">${body.comment}</td>
            </tr>
            ` : ''}
          </table>

          <h2 style="color: #333; border-bottom: 2px solid #62bb46; padding-bottom: 10px; margin-top: 30px;">Товары в заказе</h2>
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #f0f0f0;">
                <th style="padding: 12px 8px; text-align: left; width: 30px;">№</th>
                <th style="padding: 12px 8px; text-align: left;">Товар</th>
                <th style="padding: 12px 8px; text-align: center; width: 60px;">Кол-во</th>
                <th style="padding: 12px 8px; text-align: right; width: 100px;">Цена</th>
                <th style="padding: 12px 8px; text-align: right; width: 100px;">Сумма</th>
              </tr>
            </thead>
            <tbody>
              ${itemsHtml}
            </tbody>
            <tfoot>
              <tr style="background: #62bb46; color: white;">
                <td colspan="4" style="padding: 12px 8px; font-weight: bold; text-align: right;">ИТОГО:</td>
                <td style="padding: 12px 8px; font-weight: bold; text-align: right;">${body.totalPrice.toLocaleString('ru-RU')} ₽</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div style="background: #333; color: #999; padding: 15px; border-radius: 0 0 10px 10px; text-align: center; font-size: 12px;">
          <p style="margin: 0;">Это автоматическое уведомление с сайта E1</p>
        </div>
      </body>
      </html>
    `;

    // Text email template
    const textContent = `
НОВЫЙ ЗАКАЗ ИЗ КОРЗИНЫ

ДАННЫЕ ПОКУПАТЕЛЯ
-----------------
ФИО: ${body.customerName}
Телефон: ${body.customerPhone}
${body.customerEmail ? `Email: ${body.customerEmail}` : ''}
${body.comment ? `Комментарий: ${body.comment}` : ''}

ТОВАРЫ В ЗАКАЗЕ
---------------
${itemsText}

ИТОГО: ${body.totalPrice.toLocaleString('ru-RU')} ₽

---
Это автоматическое уведомление с сайта E1
    `;

    // Send email
    await transporter.sendMail({
      from: process.env.SMTP_FROM || 'noreply@e-1.ru',
      to: salesEmail,
      subject: `Новый заказ из корзины от ${body.customerName}`,
      text: textContent,
      html: htmlContent,
    });

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error processing cart order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка при обработке заказа' },
      { status: 500 }
    );
  }
}
