import { NextRequest, NextResponse } from 'next/server';
import getPool from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET(request: NextRequest) {
  try {
    const pool = getPool();
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
