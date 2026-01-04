import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

async function ensureBlogTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS blog_articles (
      id SERIAL PRIMARY KEY,
      slug VARCHAR(500) NOT NULL UNIQUE,
      title VARCHAR(500) NOT NULL,
      category VARCHAR(255) NOT NULL,
      short_description TEXT,
      content TEXT NOT NULL,
      cover_image VARCHAR(500),
      published_at DATE NOT NULL DEFAULT CURRENT_DATE,
      author_name VARCHAR(255) NOT NULL,
      is_active BOOLEAN DEFAULT true,
      meta_title VARCHAR(500),
      meta_description TEXT,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )
  `);

  // Create index for faster lookups
  await pool.query(`
    CREATE INDEX IF NOT EXISTS idx_blog_articles_slug ON blog_articles(slug)
  `);
  await pool.query(`
    CREATE INDEX IF NOT EXISTS idx_blog_articles_category ON blog_articles(category)
  `);
  await pool.query(`
    CREATE INDEX IF NOT EXISTS idx_blog_articles_published ON blog_articles(published_at DESC)
  `);
}

// GET: List all articles or filter by status/category
export async function GET(request: NextRequest) {
  try {
    await ensureBlogTable();
    const pool = getPool();

    const { searchParams } = new URL(request.url);
    const status = searchParams.get('status') || 'all';
    const category = searchParams.get('category');
    const year = searchParams.get('year');
    const limit = parseInt(searchParams.get('limit') || '50');
    const offset = parseInt(searchParams.get('offset') || '0');

    let whereClause = '';
    const params: (string | number | boolean)[] = [];
    let paramIndex = 1;

    if (status === 'active') {
      whereClause = `WHERE is_active = true`;
    } else if (status === 'inactive') {
      whereClause = `WHERE is_active = false`;
    }

    if (category) {
      whereClause += whereClause ? ` AND category = $${paramIndex}` : `WHERE category = $${paramIndex}`;
      params.push(category);
      paramIndex++;
    }

    if (year) {
      const yearCondition = `EXTRACT(YEAR FROM published_at) = $${paramIndex}`;
      whereClause += whereClause ? ` AND ${yearCondition}` : `WHERE ${yearCondition}`;
      params.push(parseInt(year));
      paramIndex++;
    }

    // Get total count
    const countResult = await pool.query(
      `SELECT COUNT(*) as total FROM blog_articles ${whereClause}`,
      params
    );
    const total = parseInt(countResult.rows[0].total);

    // Get articles
    params.push(limit, offset);
    const result = await pool.query(
      `SELECT * FROM blog_articles ${whereClause}
       ORDER BY published_at DESC, created_at DESC
       LIMIT $${paramIndex} OFFSET $${paramIndex + 1}`,
      params
    );

    // Get counts by status
    const countsResult = await pool.query(`
      SELECT
        COUNT(*) as total,
        COUNT(*) FILTER (WHERE is_active = true) as active,
        COUNT(*) FILTER (WHERE is_active = false) as inactive
      FROM blog_articles
    `);

    // Get categories with counts
    const categoriesResult = await pool.query(`
      SELECT category, COUNT(*) as count
      FROM blog_articles
      WHERE is_active = true
      GROUP BY category
      ORDER BY category
    `);

    // Get years with counts
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
      counts: {
        total: parseInt(countsResult.rows[0].total),
        active: parseInt(countsResult.rows[0].active),
        inactive: parseInt(countsResult.rows[0].inactive),
      },
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

// POST: Create new article
export async function POST(request: NextRequest) {
  try {
    await ensureBlogTable();
    const pool = getPool();
    const body = await request.json();

    const {
      slug,
      title,
      category,
      short_description,
      content,
      cover_image,
      published_at,
      author_name,
      is_active,
      meta_title,
      meta_description,
    } = body;

    if (!slug || !title || !category || !content || !author_name) {
      return NextResponse.json(
        { success: false, error: 'Заполните все обязательные поля' },
        { status: 400 }
      );
    }

    // Check for duplicate slug
    const existingSlug = await pool.query(
      'SELECT id FROM blog_articles WHERE slug = $1',
      [slug]
    );
    if (existingSlug.rows.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Статья с такой ссылкой уже существует' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `INSERT INTO blog_articles (
        slug, title, category, short_description, content, cover_image,
        published_at, author_name, is_active, meta_title, meta_description
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)
      RETURNING *`,
      [
        slug,
        title,
        category,
        short_description || null,
        content,
        cover_image || null,
        published_at || new Date().toISOString().split('T')[0],
        author_name,
        is_active !== false,
        meta_title || title,
        meta_description || short_description || null,
      ]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating blog article:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
