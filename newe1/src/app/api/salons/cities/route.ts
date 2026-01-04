import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const regionFilter = searchParams.get('region');
    const pool = getPool();

    let query = `
      SELECT s.city, s.region, COUNT(*) as count, c.name_prepositional
      FROM salons s
      LEFT JOIN cities c ON LOWER(c.name) = LOWER(s.city)
    `;
    const params: string[] = [];

    if (regionFilter) {
      query += ` WHERE s.region = $1`;
      params.push(regionFilter);
    }

    query += ` GROUP BY s.city, s.region, c.name_prepositional ORDER BY s.region, s.city`;

    const result = await pool.query(query, params);

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
          name_prepositional: r.name_prepositional || null,
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
