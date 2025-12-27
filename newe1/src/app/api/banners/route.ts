import { NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List active banners (public API)
export async function GET() {
  try {
    const pool = getPool();

    // Check if table exists
    const tableCheck = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'banners'
      )
    `);

    if (!tableCheck.rows[0].exists) {
      return NextResponse.json({
        success: true,
        data: [],
      });
    }

    const result = await pool.query(
      `SELECT id, desktop_image, mobile_image, link
       FROM banners
       WHERE is_active = true
       ORDER BY sort_order ASC, created_at DESC`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
    });
  } catch (error) {
    console.error('Error fetching banners:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
