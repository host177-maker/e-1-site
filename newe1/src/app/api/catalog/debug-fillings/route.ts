import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const seriesId = searchParams.get('seriesId');

    const pool = getPool();

    // Получаем все наполнения для серии (или все если серия не указана)
    let query = `
      SELECT cf.*, cs.name as series_name
      FROM catalog_fillings cf
      LEFT JOIN catalog_series cs ON cf.series_id = cs.id
    `;
    const params: (string | number)[] = [];

    if (seriesId) {
      query += ' WHERE cf.series_id = $1';
      params.push(parseInt(seriesId));
    }

    query += ' ORDER BY cf.series_id, cf.height, cf.width, cf.depth LIMIT 100';

    const result = await pool.query(query, params);

    // Также получаем уникальные размеры вариантов для сравнения
    let variantsQuery = `
      SELECT DISTINCT
        p.series_id,
        cs.name as series_name,
        v.height,
        v.width,
        v.depth,
        p.door_count
      FROM catalog_variants v
      JOIN catalog_products p ON v.product_id = p.id
      LEFT JOIN catalog_series cs ON p.series_id = cs.id
    `;

    if (seriesId) {
      variantsQuery += ' WHERE p.series_id = $1';
    }

    variantsQuery += ' ORDER BY p.series_id, v.height, v.width, v.depth LIMIT 100';

    const variantsResult = await pool.query(variantsQuery, params);

    return NextResponse.json({
      success: true,
      fillings: result.rows,
      fillingsCount: result.rows.length,
      variantSizes: variantsResult.rows,
      variantSizesCount: variantsResult.rows.length,
      message: 'Сравните размеры наполнений с размерами вариантов'
    });
  } catch (error) {
    console.error('Debug fillings error:', error);
    return NextResponse.json(
      { success: false, error: String(error) },
      { status: 500 }
    );
  }
}
