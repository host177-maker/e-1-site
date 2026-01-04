import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single active article by slug
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ slug: string }> }
) {
  try {
    const { slug } = await params;
    const pool = getPool();

    // Check if table exists
    const tableCheck = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'blog_articles'
      )
    `);

    if (!tableCheck.rows[0].exists) {
      return NextResponse.json(
        { success: false, error: 'Статья не найдена' },
        { status: 404 }
      );
    }

    const result = await pool.query(
      `SELECT * FROM blog_articles WHERE slug = $1 AND is_active = true`,
      [slug]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Статья не найдена' },
        { status: 404 }
      );
    }

    // Get related articles (same category, excluding current)
    const relatedResult = await pool.query(
      `SELECT id, slug, title, category, short_description, cover_image, published_at
       FROM blog_articles
       WHERE category = $1 AND slug != $2 AND is_active = true
       ORDER BY published_at DESC
       LIMIT 3`,
      [result.rows[0].category, slug]
    );

    // Get categories with counts for sidebar
    const categoriesResult = await pool.query(`
      SELECT category, COUNT(*) as count
      FROM blog_articles
      WHERE is_active = true
      GROUP BY category
      ORDER BY category
    `);

    return NextResponse.json({
      success: true,
      data: result.rows[0],
      related: relatedResult.rows,
      categories: categoriesResult.rows,
    });
  } catch (error) {
    console.error('Error fetching blog article:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
