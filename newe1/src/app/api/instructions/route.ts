import { NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List all active instructions (public)
export async function GET() {
  try {
    const pool = getPool();

    // Check if table exists first
    const tableCheck = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'instructions'
      )
    `);

    if (!tableCheck.rows[0].exists) {
      return NextResponse.json({
        success: true,
        data: [],
      });
    }

    const result = await pool.query(
      `SELECT id, name, pdf_path, video_url
       FROM instructions
       WHERE is_active = true
       ORDER BY sort_order ASC, created_at DESC`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
    });
  } catch (error) {
    console.error('Error fetching instructions:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
