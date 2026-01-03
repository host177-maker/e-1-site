import { NextRequest, NextResponse } from 'next/server';
import nodemailer from 'nodemailer';
import { getPool } from '@/lib/db';
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

interface DeliveryInfo {
  type: 'delivery' | 'pickup';
  address?: string;
  liftType: 'none' | 'stairs' | 'elevator';
  floor?: number;
  deliveryCost: number;
  liftCost: number;
  assemblyCost?: number;
}

interface CartOrderRequest {
  customerName: string;
  customerPhone: string;
  customerEmail?: string;
  promoCode?: string;
  comment?: string;
  city?: string;
  delivery?: DeliveryInfo;
  items: CartItem[];
  totalPrice: number;
}

// Ensure orders table exists
async function ensureOrdersTable() {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS cart_orders (
      id SERIAL PRIMARY KEY,
      customer_name VARCHAR(255) NOT NULL,
      customer_phone VARCHAR(50) NOT NULL,
      customer_email VARCHAR(255),
      promo_code VARCHAR(50),
      comment TEXT,
      city VARCHAR(100),
      delivery_type VARCHAR(20),
      delivery_address TEXT,
      lift_type VARCHAR(20),
      floor INTEGER,
      delivery_cost DECIMAL(10, 2) DEFAULT 0,
      lift_cost DECIMAL(10, 2) DEFAULT 0,
      assembly_cost DECIMAL(10, 2) DEFAULT 0,
      items JSONB NOT NULL,
      total_price DECIMAL(10, 2) NOT NULL,
      status VARCHAR(50) DEFAULT 'new',
      email_sent BOOLEAN DEFAULT false,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )
  `);
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

    // Ensure table exists
    await ensureOrdersTable();
    const pool = getPool();

    // Save order to database
    const orderResult = await pool.query(
      `INSERT INTO cart_orders (
        customer_name, customer_phone, customer_email, promo_code, comment,
        city, delivery_type, delivery_address, lift_type, floor,
        delivery_cost, lift_cost, assembly_cost, items, total_price
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)
      RETURNING id`,
      [
        body.customerName,
        body.customerPhone,
        body.customerEmail || null,
        body.promoCode || null,
        body.comment || null,
        body.city || null,
        body.delivery?.type || 'delivery',
        body.delivery?.address || null,
        body.delivery?.liftType || 'none',
        body.delivery?.floor || null,
        body.delivery?.deliveryCost || 0,
        body.delivery?.liftCost || 0,
        body.delivery?.assemblyCost || 0,
        JSON.stringify(body.items),
        body.totalPrice,
      ]
    );

    const orderId = orderResult.rows[0].id;

    // Try to send email (optional, don't fail if it doesn't work)
    let emailSent = false;
    try {
      const salesEmail = await getEmailByKey(EMAIL_KEYS.SALES);

      if (salesEmail && process.env.SMTP_HOST) {
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

        // Delivery info HTML
        const deliveryHtml = body.delivery ? `
          <h2 style="color: #333; border-bottom: 2px solid #62bb46; padding-bottom: 10px; margin-top: 30px;">Доставка</h2>
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="padding: 8px 0; color: #666; width: 150px;">Город:</td>
              <td style="padding: 8px 0;">${body.city || '-'}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; color: #666;">Тип:</td>
              <td style="padding: 8px 0;">${body.delivery.type === 'pickup' ? 'Самовывоз' : 'Доставка'}</td>
            </tr>
            ${body.delivery.address ? `
            <tr>
              <td style="padding: 8px 0; color: #666;">Адрес:</td>
              <td style="padding: 8px 0;">${body.delivery.address}</td>
            </tr>
            ` : ''}
            <tr>
              <td style="padding: 8px 0; color: #666;">Подъём:</td>
              <td style="padding: 8px 0;">${body.delivery.liftType === 'none' ? 'Не требуется' : body.delivery.liftType === 'elevator' ? 'С грузовым лифтом' : `Без лифта (${body.delivery.floor} этаж)`}</td>
            </tr>
            <tr>
              <td style="padding: 8px 0; color: #666;">Стоимость доставки:</td>
              <td style="padding: 8px 0; font-weight: bold;">${body.delivery.deliveryCost.toLocaleString('ru-RU')} ₽</td>
            </tr>
            ${body.delivery.liftCost > 0 ? `
            <tr>
              <td style="padding: 8px 0; color: #666;">Стоимость подъёма:</td>
              <td style="padding: 8px 0; font-weight: bold;">${body.delivery.liftCost.toLocaleString('ru-RU')} ₽</td>
            </tr>
            ` : ''}
            ${(body.delivery.assemblyCost || 0) > 0 ? `
            <tr>
              <td style="padding: 8px 0; color: #666;">Выезд сборщика:</td>
              <td style="padding: 8px 0; font-weight: bold;">${body.delivery.assemblyCost?.toLocaleString('ru-RU')} ₽</td>
            </tr>
            ` : ''}
          </table>
        ` : '';

        // HTML email template
        const htmlContent = `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="utf-8">
            <title>Новый заказ #${orderId}</title>
          </head>
          <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #62bb46 0%, #4a9935 100%); padding: 20px; border-radius: 10px 10px 0 0;">
              <h1 style="color: white; margin: 0; font-size: 24px;">Новый заказ #${orderId}</h1>
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
                ${body.promoCode ? `
                <tr>
                  <td style="padding: 8px 0; color: #666;">Промокод:</td>
                  <td style="padding: 8px 0; font-weight: bold; color: #62bb46;">${body.promoCode}</td>
                </tr>
                ` : ''}
                ${body.comment ? `
                <tr>
                  <td style="padding: 8px 0; color: #666;">Комментарий:</td>
                  <td style="padding: 8px 0;">${body.comment}</td>
                </tr>
                ` : ''}
              </table>

              ${deliveryHtml}

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

        await transporter.sendMail({
          from: process.env.SMTP_FROM || 'noreply@e-1.ru',
          to: salesEmail,
          subject: `Новый заказ #${orderId} от ${body.customerName}`,
          html: htmlContent,
        });

        emailSent = true;

        // Update order with email status
        await pool.query(
          `UPDATE cart_orders SET email_sent = true WHERE id = $1`,
          [orderId]
        );
      }
    } catch (emailError) {
      console.error('Failed to send email:', emailError);
      // Don't fail the request, order is already saved
    }

    return NextResponse.json({
      success: true,
      orderId,
      emailSent,
    });
  } catch (error) {
    console.error('Error processing cart order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка при обработке заказа' },
      { status: 500 }
    );
  }
}
