import { NextResponse } from 'next/server';
import { getServices } from '@/lib/catalog';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Получить все активные услуги
export async function GET() {
  try {
    const services = await getServices();

    return NextResponse.json({
      success: true,
      services,
    });
  } catch (error) {
    console.error('Error fetching services:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка загрузки услуг' },
      { status: 500 }
    );
  }
}
