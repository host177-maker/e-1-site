import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List all reviews (including inactive)
export async function GET(request: NextRequest) {
  try {
    const currentAdmin = await getCurrentAdmin();
    if (!currentAdmin) {
      return NextResponse.json(
        { success: false, error: 'Не авторизован' },
        { status: 401 }
      );
    }

    const { searchParams } = new URL(request.url);
    const limit = parseInt(searchParams.get('limit') || '50');
    const offset = parseInt(searchParams.get('offset') || '0');
    const status = searchParams.get('status'); // 'active', 'pending', 'all'

    const pool = getPool();

    let whereClause = '';
    if (status === 'active') {
      whereClause = 'WHERE is_active = true';
    } else if (status === 'pending') {
      whereClause = 'WHERE is_active = false';
    }

    const result = await pool.query(
      `SELECT id, name, phone, order_number, review_text, company_response,
              rating, photos, is_active, show_on_main, created_at
       FROM reviews
       ${whereClause}
       ORDER BY created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );

    // Get counts
    const countResult = await pool.query(
      `SELECT
        COUNT(*) FILTER (WHERE is_active = true) as active_count,
        COUNT(*) FILTER (WHERE is_active = false) as pending_count,
        COUNT(*) as total_count
       FROM reviews`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
      counts: {
        active: parseInt(countResult.rows[0].active_count),
        pending: parseInt(countResult.rows[0].pending_count),
        total: parseInt(countResult.rows[0].total_count),
      },
      pagination: {
        limit,
        offset,
        hasMore: result.rows.length === limit,
      },
    });
  } catch (error) {
    console.error('Error fetching reviews:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
