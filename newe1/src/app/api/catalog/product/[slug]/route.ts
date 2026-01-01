import { NextRequest, NextResponse } from 'next/server';
import { getProductBySlug, getVariantByParams, getFilling, getFillings } from '@/lib/catalog';

interface Params {
  params: Promise<{ slug: string }>;
}

export async function GET(request: NextRequest, { params }: Params) {
  try {
    const { slug } = await params;
    const data = await getProductBySlug(slug);

    if (!data) {
      return NextResponse.json(
        { success: false, error: 'Товар не найден' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      ...data
    });
  } catch (error) {
    console.error('Product API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка получения товара' },
      { status: 500 }
    );
  }
}

// POST - получить конкретный вариант по параметрам
export async function POST(request: NextRequest, { params }: Params) {
  try {
    // slug извлекается для возможного использования в будущем
    await params;
    const body = await request.json();
    const { productId, height, width, depth, bodyColorId, profileColorId, seriesId, doorCount } = body;

    // Получаем вариант
    const variant = await getVariantByParams(productId, height, width, depth, bodyColorId, profileColorId);

    // Получаем наполнение для текущего размера
    let filling = null;
    let fillings: Awaited<ReturnType<typeof getFillings>> = [];
    if (seriesId && height && width && depth) {
      // doorCount может быть null - тогда покажем все наполнения для размера
      console.log('Поиск наполнения:', { seriesId, doorCount, height, width, depth });
      filling = await getFilling(seriesId, doorCount || null, height, width, depth);
      fillings = await getFillings(seriesId, doorCount || null, height, width, depth);
      console.log('Найдено наполнений:', fillings.length);
    } else {
      console.log('Пропуск поиска наполнения:', { seriesId, height, width, depth });
    }

    return NextResponse.json({
      success: true,
      variant,
      filling,
      fillings
    });
  } catch (error) {
    console.error('Variant API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка получения варианта' },
      { status: 500 }
    );
  }
}
