import { NextRequest, NextResponse } from 'next/server';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ regionId: string }> }
) {
  try {
    const { regionId } = await params;
    const { Pool } = await import('pg');

    const pool = new Pool({
      host: process.env.POSTGRES_HOST || '192.168.40.41',
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB || 'newe1',
      user: process.env.POSTGRES_USER || 'newe1',
      password: process.env.POSTGRES_PASSWORD || 'newe1pass',
    });

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

    await pool.end();

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
