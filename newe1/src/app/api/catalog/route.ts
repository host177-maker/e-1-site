import { NextRequest, NextResponse } from 'next/server';
import { getProducts, getSeries, getFilterOptions } from '@/lib/catalog';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);

    // Базовые параметры
    const limit = parseInt(searchParams.get('limit') || '20');
    const offset = parseInt(searchParams.get('offset') || '0');

    // Параметры фильтров (мультивыбор)
    const doorTypesParam = searchParams.get('doorTypes');
    const seriesParam = searchParams.get('series');
    const widthMin = searchParams.get('widthMin') ? parseInt(searchParams.get('widthMin')!) : undefined;
    const widthMax = searchParams.get('widthMax') ? parseInt(searchParams.get('widthMax')!) : undefined;
    const heightsParam = searchParams.get('heights');
    const depthsParam = searchParams.get('depths');

    // Парсим массивы
    const doorTypes = doorTypesParam ? doorTypesParam.split(',').filter(Boolean) : undefined;
    const seriesSlugs = seriesParam ? seriesParam.split(',').filter(Boolean) : undefined;
    const heights = heightsParam ? heightsParam.split(',').map(h => parseInt(h)).filter(h => !isNaN(h)) : undefined;
    const depths = depthsParam ? depthsParam.split(',').map(d => parseInt(d)).filter(d => !isNaN(d)) : undefined;

    // Запрашиваем параметры фильтров с подсчётом
    const includeFilters = searchParams.get('includeFilters') === 'true';

    const currentFilters = {
      doorTypes,
      series: seriesSlugs,
      widthMin,
      widthMax,
      heights,
      depths
    };

    const [{ products, total }, series, filterOptions] = await Promise.all([
      getProducts({
        doorTypeSlugs: doorTypes,
        seriesSlugs,
        widthMin,
        widthMax,
        heights,
        depths,
        limit,
        offset
      }),
      getSeries(),
      includeFilters ? getFilterOptions(currentFilters) : null
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
