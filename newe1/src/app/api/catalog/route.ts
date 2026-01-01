import { NextRequest, NextResponse } from 'next/server';
import { getProducts, getSeries } from '@/lib/catalog';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const seriesSlug = searchParams.get('series') || undefined;
    const limit = parseInt(searchParams.get('limit') || '20');
    const offset = parseInt(searchParams.get('offset') || '0');

    const [{ products, total }, series] = await Promise.all([
      getProducts({ seriesSlug, limit, offset }),
      getSeries()
    ]);

    return NextResponse.json({
      success: true,
      products,
      series,
      total,
      limit,
      offset
    });
  } catch (error) {
    console.error('Catalog API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка получения каталога' },
      { status: 500 }
    );
  }
}
