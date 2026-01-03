import { NextRequest, NextResponse } from 'next/server';
import { getProducts, getSeries, getFilterOptions } from '@/lib/catalog';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);

    // Базовые параметры
    const seriesSlug = searchParams.get('series') || undefined;
    const limit = parseInt(searchParams.get('limit') || '20');
    const offset = parseInt(searchParams.get('offset') || '0');

    // Параметры фильтров
    const doorType = searchParams.get('doorType') || undefined;
    const widthMin = searchParams.get('widthMin') ? parseInt(searchParams.get('widthMin')!) : undefined;
    const widthMax = searchParams.get('widthMax') ? parseInt(searchParams.get('widthMax')!) : undefined;
    const heightsParam = searchParams.get('heights');
    const depthsParam = searchParams.get('depths');
    const priceMin = searchParams.get('priceMin') ? parseInt(searchParams.get('priceMin')!) : undefined;
    const priceMax = searchParams.get('priceMax') ? parseInt(searchParams.get('priceMax')!) : undefined;

    // Парсим массивы
    const heights = heightsParam ? heightsParam.split(',').map(h => parseInt(h)).filter(h => !isNaN(h)) : undefined;
    const depths = depthsParam ? depthsParam.split(',').map(d => parseInt(d)).filter(d => !isNaN(d)) : undefined;

    // Запрашиваем параметры фильтров только если запрошено
    const includeFilters = searchParams.get('includeFilters') === 'true';

    const [{ products, total }, series, filterOptions] = await Promise.all([
      getProducts({
        seriesSlug,
        doorTypeSlug: doorType,
        widthMin,
        widthMax,
        heights,
        depths,
        priceMin,
        priceMax,
        limit,
        offset
      }),
      getSeries(),
      includeFilters ? getFilterOptions() : null
    ]);

    return NextResponse.json({
      success: true,
      products,
      series,
      total,
      limit,
      offset,
      ...(filterOptions && { filterOptions })
    });
  } catch (error) {
    console.error('Catalog API error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка получения каталога' },
      { status: 500 }
    );
  }
}
