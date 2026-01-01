import { NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET() {
  try {
    const pool = getPool();

    // Get regions that have salons
    const result = await pool.query(`
      SELECT DISTINCT r.id, r.name, r.sort_order,
             COUNT(DISTINCT s.id) as salon_count
      FROM regions r
      INNER JOIN salons s ON s.region_id = r.id
      GROUP BY r.id, r.name, r.sort_order
      ORDER BY r.sort_order, r.name
    `);

    return NextResponse.json({
      success: true,
      data: result.rows.map((r: { id: number; name: string; salon_count: string }) => ({
        id: r.id,
        name: r.name,
        salonCount: parseInt(r.salon_count),
      })),
    });
  } catch (error) {
    console.error('Error fetching regions:', error);

    // Fallback: get unique regions from salons table
    try {
      const pool = getPool();

      const result = await pool.query(`
        SELECT region as name, COUNT(*) as salon_count
        FROM salons
        WHERE region IS NOT NULL AND region != ''
        GROUP BY region
        ORDER BY region
      `);

      return NextResponse.json({
        success: true,
        data: result.rows.map((r: { name: string; salon_count: string }, idx: number) => ({
          id: idx + 1,
          name: r.name,
          salonCount: parseInt(r.salon_count),
        })),
      });
    } catch (fallbackError) {
      console.error('Fallback error:', fallbackError);
      return NextResponse.json(
        { success: false, error: 'Failed to fetch regions' },
        { status: 500 }
      );
    }
  }
}
