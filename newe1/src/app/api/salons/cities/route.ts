import { NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    const pool = getPool();
    const result = await pool.query(`
      SELECT city, region, COUNT(*) as count
      FROM salons
      GROUP BY city, region
      ORDER BY region, city
    `);

    // Group cities by region
    const byRegion: Record<string, { cities: { city: string; count: number }[] }> = {};

    for (const row of result.rows) {
      const region = row.region || 'Без региона';
      if (!byRegion[region]) {
        byRegion[region] = { cities: [] };
      }
      byRegion[region].cities.push({
        city: row.city,
        count: parseInt(row.count),
      });
    }

    return NextResponse.json({
      success: true,
      data: {
        cities: result.rows.map(r => ({
          city: r.city,
          region: r.region,
          count: parseInt(r.count),
        })),
        byRegion,
      },
    });
  } catch (error) {
    console.error('Error fetching cities:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to fetch cities' },
      { status: 500 }
    );
  }
}
