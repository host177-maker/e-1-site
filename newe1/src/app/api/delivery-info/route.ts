import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get delivery info for a city
export async function GET(request: NextRequest) {
  try {
    const pool = getPool();
    const { searchParams } = new URL(request.url);
    const cityId = searchParams.get('city_id');
    const cityName = searchParams.get('city_name');

    if (!cityId && !cityName) {
      return NextResponse.json(
        { success: false, error: 'city_id или city_name обязателен' },
        { status: 400 }
      );
    }

    // Find city
    let cityQuery = `
      SELECT c.*,
             w.id as warehouse_id,
             w.name as warehouse_name,
             w.display_name as warehouse_display_name,
             w.address as warehouse_address,
             w.latitude as warehouse_latitude,
             w.longitude as warehouse_longitude,
             w.phone as warehouse_phone,
             w.working_hours as warehouse_working_hours,
             sp.delivery_base_price,
             sp.delivery_per_km,
             sp.floor_lift_price,
             sp.elevator_lift_price,
             sp.assembly_per_km
      FROM cities c
      LEFT JOIN warehouses w ON w.id = c.warehouse_id
      LEFT JOIN service_prices sp ON sp.region_group = c.price_group AND sp.is_active = true
    `;

    const params: (string | number)[] = [];
    if (cityId) {
      cityQuery += ` WHERE c.id = $1`;
      params.push(parseInt(cityId));
    } else if (cityName) {
      cityQuery += ` WHERE LOWER(c.name) = LOWER($1)`;
      params.push(cityName);
    }

    const cityResult = await pool.query(cityQuery, params);

    if (cityResult.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Город не найден' },
        { status: 404 }
      );
    }

    const city = cityResult.rows[0];

    return NextResponse.json({
      success: true,
      data: {
        city: {
          id: city.id,
          name: city.name,
          price_group: city.price_group,
        },
        warehouse: city.warehouse_id ? {
          id: city.warehouse_id,
          name: city.warehouse_display_name || city.warehouse_name,
          address: city.warehouse_address,
          latitude: city.warehouse_latitude ? parseFloat(city.warehouse_latitude) : null,
          longitude: city.warehouse_longitude ? parseFloat(city.warehouse_longitude) : null,
          phone: city.warehouse_phone,
          working_hours: city.warehouse_working_hours,
        } : null,
        prices: city.delivery_base_price ? {
          delivery_base_price: parseFloat(city.delivery_base_price),
          delivery_per_km: parseFloat(city.delivery_per_km),
          floor_lift_price: parseFloat(city.floor_lift_price),
          elevator_lift_price: parseFloat(city.elevator_lift_price),
          assembly_per_km: parseFloat(city.assembly_per_km),
        } : null,
      },
    });
  } catch (error) {
    console.error('Error fetching delivery info:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
