import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Ensure promotions table exists
async function ensurePromotionsTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS promotions (
      id SERIAL PRIMARY KEY,
      title VARCHAR(255) NOT NULL,
      slug VARCHAR(255) UNIQUE NOT NULL,
      content TEXT NOT NULL,
      images TEXT[] DEFAULT '{}',
      start_date DATE NOT NULL,
      end_date DATE NOT NULL,
      published_at TIMESTAMP WITH TIME ZONE,
      is_active BOOLEAN DEFAULT true,
      sort_order INTEGER DEFAULT 0,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      created_by INTEGER
    )
  `);

  // Add sort_order column if it doesn't exist
  await pool.query(`
    ALTER TABLE promotions ADD COLUMN IF NOT EXISTS sort_order INTEGER DEFAULT 0
  `);
}

// Generate slug from title
function generateSlug(title: string): string {
  const translitMap: Record<string, string> = {
    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
    'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
    'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
    'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '',
    'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
  };

  return title
    .toLowerCase()
    .split('')
    .map(char => translitMap[char] || char)
    .join('')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .substring(0, 100);
}

// GET: List all promotions
export async function GET(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    await ensurePromotionsTable();

    const { searchParams } = new URL(request.url);
    const status = searchParams.get('status'); // 'active', 'expired', 'all'

    const pool = getPool();

    let whereClause = '';
    if (status === 'active') {
      whereClause = "WHERE is_active = true AND end_date >= CURRENT_DATE";
    } else if (status === 'expired') {
      whereClause = "WHERE end_date < CURRENT_DATE";
    } else if (status === 'inactive') {
      whereClause = "WHERE is_active = false";
    }

    const result = await pool.query(
      `SELECT id, title, slug, content, images, start_date, end_date,
              published_at, is_active, sort_order, created_at, updated_at
       FROM promotions
       ${whereClause}
       ORDER BY sort_order ASC, created_at DESC`
    );

    // Get counts
    const countResult = await pool.query(
      `SELECT
        COUNT(*) FILTER (WHERE is_active = true AND end_date >= CURRENT_DATE) as active_count,
        COUNT(*) FILTER (WHERE end_date < CURRENT_DATE) as expired_count,
        COUNT(*) FILTER (WHERE is_active = false) as inactive_count,
        COUNT(*) as total_count
       FROM promotions`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
      counts: {
        active: parseInt(countResult.rows[0].active_count),
        expired: parseInt(countResult.rows[0].expired_count),
        inactive: parseInt(countResult.rows[0].inactive_count),
        total: parseInt(countResult.rows[0].total_count),
      },
    });
  } catch (error) {
    console.error('Error fetching promotions:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new promotion
export async function POST(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    await ensurePromotionsTable();

    const body = await request.json();
    const { title, content, images, start_date, end_date, published_at, is_active } = body;

    if (!title || !content || !start_date || !end_date) {
      return NextResponse.json(
        { success: false, error: 'Заполните все обязательные поля' },
        { status: 400 }
      );
    }

    // Generate unique slug
    let slug = generateSlug(title);
    const pool = getPool();

    // Check if slug exists and make it unique
    const existingSlug = await pool.query(
      'SELECT id FROM promotions WHERE slug = $1',
      [slug]
    );
    if (existingSlug.rows.length > 0) {
      slug = `${slug}-${Date.now()}`;
    }

    const result = await pool.query(
      `INSERT INTO promotions (title, slug, content, images, start_date, end_date, published_at, is_active, created_by)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
       RETURNING *`,
      [
        title,
        slug,
        content,
        images || [],
        start_date,
        end_date,
        published_at || null,
        is_active !== false,
        currentAdmin.id
      ]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating promotion:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PUT: Reorder promotions
export async function PUT(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    await ensurePromotionsTable();

    const body = await request.json();
    const { orderedIds } = body;

    if (!Array.isArray(orderedIds) || orderedIds.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат данных' },
        { status: 400 }
      );
    }

    const pool = getPool();

    // Update sort_order for each promotion
    for (let i = 0; i < orderedIds.length; i++) {
      await pool.query(
        'UPDATE promotions SET sort_order = $1 WHERE id = $2',
        [i, orderedIds[i]]
      );
    }

    return NextResponse.json({
      success: true,
      message: 'Порядок обновлён',
    });
  } catch (error) {
    console.error('Error reordering promotions:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
