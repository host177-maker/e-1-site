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
  const errors: string[] = [];

  try {
    // slug извлекается для возможного использования в будущем
    await params;
    const body = await request.json();
    const { productId, height, width, depth, bodyColorId, profileColorId, seriesId, doorCount } = body;

    // Получаем вариант
    let variant = null;
    try {
      variant = await getVariantByParams(productId, height, width, depth, bodyColorId, profileColorId);
    } catch (variantError) {
      const errMsg = variantError instanceof Error ? variantError.message : String(variantError);
      console.error('getVariantByParams error:', errMsg);
      errors.push(`variant: ${errMsg}`);
    }

    // Получаем наполнение для текущего размера
    let filling = null;
    let fillings: Awaited<ReturnType<typeof getFillings>> = [];
    if (seriesId && height && width && depth) {
      try {
        filling = await getFilling(seriesId, doorCount || null, height, width, depth);
      } catch (fillingError) {
        const errMsg = fillingError instanceof Error ? fillingError.message : String(fillingError);
        console.error('getFilling error:', errMsg);
        errors.push(`filling: ${errMsg}`);
      }

      try {
        fillings = await getFillings(seriesId, doorCount || null, height, width, depth);
      } catch (fillingsError) {
        const errMsg = fillingsError instanceof Error ? fillingsError.message : String(fillingsError);
        console.error('getFillings error:', errMsg);
        errors.push(`fillings: ${errMsg}`);
      }
    }

    // Если были ошибки базы данных - возвращаем 500 чтобы фронтенд не очищал данные
    if (errors.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Ошибка базы данных', details: errors.join('; '), variant, filling, fillings },
        { status: 500 }
      );
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
      { success: false, error: 'Ошибка получения варианта', details: error instanceof Error ? error.message : String(error) },
      { status: 500 }
    );
  }
}
