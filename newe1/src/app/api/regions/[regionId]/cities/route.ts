import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ regionId: string }> }
) {
  try {
    const { regionId } = await params;
    const pool = getPool();

    // Get cities for region that have salons
    const result = await pool.query(`
      SELECT DISTINCT c.id, c.name,
             COUNT(DISTINCT s.id) as salon_count
      FROM cities c
      INNER JOIN salons s ON s.city_id = c.id
      WHERE c.region_id = $1
      GROUP BY c.id, c.name
      ORDER BY c.name
    `, [regionId]);

    return NextResponse.json({
      success: true,
      data: result.rows.map((r: { id: number; name: string; salon_count: string }) => ({
        id: r.id,
        name: r.name,
        salonCount: parseInt(r.salon_count),
      })),
    });
  } catch (error) {
    console.error('Error fetching cities:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to fetch cities' },
      { status: 500 }
    );
  }
}
