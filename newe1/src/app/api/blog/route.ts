import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List active articles for public display
export async function GET(request: NextRequest) {
  try {
    const pool = getPool();

    // Check if table exists
    const tableCheck = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'blog_articles'
      )
    `);

    if (!tableCheck.rows[0].exists) {
      return NextResponse.json({
        success: true,
        data: [],
        total: 0,
        categories: [],
        years: [],
      });
    }

    const { searchParams } = new URL(request.url);
    const category = searchParams.get('category');
    const year = searchParams.get('year');
    const limit = parseInt(searchParams.get('limit') || '12');
    const offset = parseInt(searchParams.get('offset') || '0');

    let whereClause = 'WHERE is_active = true';
    const params: (string | number)[] = [];
    let paramIndex = 1;

    if (category) {
      whereClause += ` AND category = $${paramIndex}`;
      params.push(category);
      paramIndex++;
    }

    if (year && year !== 'all') {
      whereClause += ` AND EXTRACT(YEAR FROM published_at) = $${paramIndex}`;
      params.push(parseInt(year));
      paramIndex++;
    }

    // Get total count for current filter
    const countResult = await pool.query(
      `SELECT COUNT(*) as total FROM blog_articles ${whereClause}`,
      params
    );
    const total = parseInt(countResult.rows[0].total);

    // Get articles
    params.push(limit, offset);
    const result = await pool.query(
      `SELECT id, slug, title, category, short_description, cover_image, published_at
       FROM blog_articles ${whereClause}
       ORDER BY published_at DESC, created_at DESC
       LIMIT $${paramIndex} OFFSET $${paramIndex + 1}`,
      params
    );

    // Get categories with counts (only active articles)
    const categoriesResult = await pool.query(`
      SELECT category, COUNT(*) as count
      FROM blog_articles
      WHERE is_active = true
      GROUP BY category
      ORDER BY category
    `);

    // Get years with counts (only active articles)
    const yearsResult = await pool.query(`
      SELECT EXTRACT(YEAR FROM published_at)::int as year, COUNT(*) as count
      FROM blog_articles
      WHERE is_active = true
      GROUP BY EXTRACT(YEAR FROM published_at)
      ORDER BY year DESC
    `);

    return NextResponse.json({
      success: true,
      data: result.rows,
      total,
      categories: categoriesResult.rows,
      years: yearsResult.rows,
    });
  } catch (error) {
    console.error('Error fetching blog articles:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
