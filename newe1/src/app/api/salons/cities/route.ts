import { NextResponse } from 'next/server';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET() {
  try {
    // Dynamic import to avoid bundling issues
    const { Pool } = await import('pg');

    const pool = new Pool({
      host: process.env.POSTGRES_HOST || '192.168.40.41',
      port: parseInt(process.env.POSTGRES_PORT || '5432'),
      database: process.env.POSTGRES_DB || 'newe1',
      user: process.env.POSTGRES_USER || 'newe1',
      password: process.env.POSTGRES_PASSWORD || 'newe1pass',
    });

    const result = await pool.query(`
      SELECT city, region, COUNT(*) as count
      FROM salons
      GROUP BY city, region
      ORDER BY region, city
    `);

    await pool.end();

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
