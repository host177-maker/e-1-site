import { NextResponse } from 'next/server';
import { getBanners, getFooter, getMessengers, getSocials, getContacts } from '@/lib/db';

// GET - получить публичный контент сайта
export async function GET() {
  try {
    const banners = getBanners().filter(b => b.isActive).sort((a, b) => a.order - b.order);
    const footer = getFooter();
    const messengers = getMessengers().filter(m => m.isActive).sort((a, b) => a.order - b.order);
    const socials = getSocials().filter(s => s.isActive).sort((a, b) => a.order - b.order);
    const contacts = getContacts();

    return NextResponse.json({
      banners,
      footer,
      messengers,
      socials,
      contacts,
    });
  } catch (error) {
    console.error('Get content error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}
