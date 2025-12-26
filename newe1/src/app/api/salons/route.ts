import { NextRequest, NextResponse } from 'next/server';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET(request: NextRequest) {
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

    const { searchParams } = new URL(request.url);
    const city = searchParams.get('city');
    const region = searchParams.get('region');

    let query = 'SELECT * FROM salons';
    const params: string[] = [];
    const conditions: string[] = [];

    if (city) {
      conditions.push(`city = $${params.length + 1}`);
      params.push(city);
    }

    if (region) {
      conditions.push(`region = $${params.length + 1}`);
      params.push(region);
    }

    if (conditions.length > 0) {
      query += ' WHERE ' + conditions.join(' AND ');
    }

    query += ' ORDER BY region, city, name';

    const result = await pool.query(query, params);
    await pool.end();

    return NextResponse.json({
      success: true,
      data: result.rows,
      count: result.rows.length,
    });
  } catch (error) {
    console.error('Error fetching salons:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to fetch salons' },
      { status: 500 }
    );
  }
}
