import { NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: List all active warehouses (public API)
export async function GET() {
  try {
    const pool = getPool();

    const result = await pool.query(`
      SELECT id, name, display_name, address, latitude, longitude, phone, working_hours
      FROM warehouses
      WHERE is_active = true
      ORDER BY sort_order ASC, name ASC
    `);

    return NextResponse.json({
      success: true,
      data: result.rows.map(w => ({
        ...w,
        latitude: w.latitude ? parseFloat(w.latitude) : null,
        longitude: w.longitude ? parseFloat(w.longitude) : null,
      })),
    });
  } catch (error) {
    console.error('Error fetching warehouses:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
