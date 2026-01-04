import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List active promotions (public API)
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const limit = parseInt(searchParams.get('limit') || '20');
    const offset = parseInt(searchParams.get('offset') || '0');
    const slug = searchParams.get('slug');

    const pool = getPool();

    // Check if table exists first
    const tableCheck = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'promotions'
      )
    `);

    if (!tableCheck.rows[0].exists) {
      return NextResponse.json({
        success: true,
        data: [],
        pagination: {
          total: 0,
          limit,
          offset,
          hasMore: false,
        },
      });
    }

    // If slug is provided, return single promotion
    if (slug) {
      const result = await pool.query(
        `SELECT id, title, slug, content, images, start_date, end_date, published_at, created_at
         FROM promotions
         WHERE slug = $1 AND is_active = true AND end_date >= CURRENT_DATE`,
        [slug]
      );

      if (result.rows.length === 0) {
        return NextResponse.json(
          { success: false, error: 'Акция не найдена' },
          { status: 404 }
        );
      }

      return NextResponse.json({
        success: true,
        data: result.rows[0],
      });
    }

    // Get list of active promotions (sorted by admin-defined order)
    const result = await pool.query(
      `SELECT id, title, slug, content, images, start_date, end_date, published_at, created_at
       FROM promotions
       WHERE is_active = true AND end_date >= CURRENT_DATE
       ORDER BY sort_order ASC, created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );

    // Get total count
    const countResult = await pool.query(
      `SELECT COUNT(*) as total
       FROM promotions
       WHERE is_active = true AND end_date >= CURRENT_DATE`
    );

    const total = parseInt(countResult.rows[0].total);

    return NextResponse.json({
      success: true,
      data: result.rows,
      pagination: {
        total,
        limit,
        offset,
        hasMore: offset + result.rows.length < total,
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
