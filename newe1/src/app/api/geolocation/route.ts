import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Get prepositional form of city name from database
async function getCityPrepositional(cityName: string): Promise<string | null> {
  try {
    const pool = getPool();
    const result = await pool.query(
      'SELECT name_prepositional FROM cities WHERE LOWER(name) = LOWER($1) LIMIT 1',
      [cityName]
    );
    return result.rows[0]?.name_prepositional || null;
  } catch {
    return null;
  }
}

// Simple IP-based geolocation using free services
export async function GET(request: NextRequest) {
  try {
    // Get client IP from headers
    const forwardedFor = request.headers.get('x-forwarded-for');
    const realIp = request.headers.get('x-real-ip');
    const ip = forwardedFor?.split(',')[0] || realIp || '';

    // Skip geolocation for local IPs
    if (!ip || ip === '127.0.0.1' || ip === '::1' || ip.startsWith('192.168.') || ip.startsWith('10.')) {
      const prepositional = await getCityPrepositional('Москва');
      return NextResponse.json({
        success: true,
        city: 'Москва',
        region: 'Московская область',
        name_prepositional: prepositional || 'Москве',
        source: 'default',
      });
    }

    // Try ipapi.co (free, no API key needed, 1000 requests/day)
    try {
      const response = await fetch(`https://ipapi.co/${ip}/json/`, {
        headers: {
          'User-Agent': 'e1-site/1.0',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.city && !data.error) {
          const prepositional = await getCityPrepositional(data.city);
          return NextResponse.json({
            success: true,
            city: data.city,
            region: data.region || '',
            country: data.country_name,
            name_prepositional: prepositional,
            source: 'ipapi',
          });
        }
      }
    } catch {
      // Continue to fallback
    }

    // Fallback to ip-api.com (free, 45 requests/minute)
    try {
      const response = await fetch(`http://ip-api.com/json/${ip}?lang=ru&fields=status,city,regionName,country`);

      if (response.ok) {
        const data = await response.json();
        if (data.status === 'success' && data.city) {
          const prepositional = await getCityPrepositional(data.city);
          return NextResponse.json({
            success: true,
            city: data.city,
            region: data.regionName || '',
            country: data.country,
            name_prepositional: prepositional,
            source: 'ip-api',
          });
        }
      }
    } catch {
      // Continue to default
    }

    // Default fallback
    const prepositional = await getCityPrepositional('Москва');
    return NextResponse.json({
      success: true,
      city: 'Москва',
      region: 'Московская область',
      name_prepositional: prepositional || 'Москве',
      source: 'default',
    });
  } catch (error) {
    console.error('Geolocation error:', error);
    return NextResponse.json({
      success: true,
      city: 'Москва',
      region: 'Московская область',
      name_prepositional: 'Москве',
      source: 'error-fallback',
    });
  }
}
