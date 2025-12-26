import { NextRequest, NextResponse } from 'next/server';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Simple IP-based geolocation using free services
export async function GET(request: NextRequest) {
  try {
    // Get client IP from headers
    const forwardedFor = request.headers.get('x-forwarded-for');
    const realIp = request.headers.get('x-real-ip');
    const ip = forwardedFor?.split(',')[0] || realIp || '';

    // Skip geolocation for local IPs
    if (!ip || ip === '127.0.0.1' || ip === '::1' || ip.startsWith('192.168.') || ip.startsWith('10.')) {
      return NextResponse.json({
        success: true,
        city: 'Москва',
        region: 'Московская область',
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
          return NextResponse.json({
            success: true,
            city: data.city,
            region: data.region || '',
            country: data.country_name,
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
          return NextResponse.json({
            success: true,
            city: data.city,
            region: data.regionName || '',
            country: data.country,
            source: 'ip-api',
          });
        }
      }
    } catch {
      // Continue to default
    }

    // Default fallback
    return NextResponse.json({
      success: true,
      city: 'Москва',
      region: 'Московская область',
      source: 'default',
    });
  } catch (error) {
    console.error('Geolocation error:', error);
    return NextResponse.json({
      success: true,
      city: 'Москва',
      region: 'Московская область',
      source: 'error-fallback',
    });
  }
}
