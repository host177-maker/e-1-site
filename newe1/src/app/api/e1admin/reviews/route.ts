import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Ensure is_rejected column exists
async function ensureRejectedColumn(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    ALTER TABLE reviews ADD COLUMN IF NOT EXISTS is_rejected BOOLEAN DEFAULT false
  `);
}

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

    await ensureRejectedColumn();

    const { searchParams } = new URL(request.url);
    const limit = parseInt(searchParams.get('limit') || '50');
    const offset = parseInt(searchParams.get('offset') || '0');
    const status = searchParams.get('status'); // 'active', 'pending', 'rejected', 'all'

    const pool = getPool();

    let whereClause = '';
    if (status === 'active') {
      whereClause = 'WHERE is_active = true AND (is_rejected = false OR is_rejected IS NULL)';
    } else if (status === 'pending') {
      whereClause = 'WHERE is_active = false AND (is_rejected = false OR is_rejected IS NULL)';
    } else if (status === 'rejected') {
      whereClause = 'WHERE is_rejected = true';
    }

    const result = await pool.query(
      `SELECT id, name, phone, order_number, review_text, company_response,
              rating, photos, is_active, show_on_main, COALESCE(is_rejected, false) as is_rejected, created_at
       FROM reviews
       ${whereClause}
       ORDER BY created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );

    // Get counts
    const countResult = await pool.query(
      `SELECT
        COUNT(*) FILTER (WHERE is_active = true AND (is_rejected = false OR is_rejected IS NULL)) as active_count,
        COUNT(*) FILTER (WHERE is_active = false AND (is_rejected = false OR is_rejected IS NULL)) as pending_count,
        COUNT(*) FILTER (WHERE is_rejected = true) as rejected_count,
        COUNT(*) as total_count
       FROM reviews`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
      counts: {
        active: parseInt(countResult.rows[0].active_count),
        pending: parseInt(countResult.rows[0].pending_count),
        rejected: parseInt(countResult.rows[0].rejected_count),
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
