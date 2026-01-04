import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';
import * as XLSX from 'xlsx';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Ensure service_prices table exists
async function ensureServicePricesTable() {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS service_prices (
      id SERIAL PRIMARY KEY,
      region_group VARCHAR(100) NOT NULL UNIQUE,
      delivery_base_price DECIMAL(10, 2) DEFAULT 0,
      delivery_per_km DECIMAL(10, 2) DEFAULT 0,
      floor_lift_price DECIMAL(10, 2) DEFAULT 0,
      elevator_lift_price DECIMAL(10, 2) DEFAULT 0,
      assembly_per_km DECIMAL(10, 2) DEFAULT 0,
      min_assembly_amount DECIMAL(10, 2) DEFAULT 3000,
      assembly_percent DECIMAL(5, 2) DEFAULT 12,
      is_active BOOLEAN DEFAULT true,
      sort_order INTEGER DEFAULT 500,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )
  `);

  // Add new columns if they don't exist
  await pool.query(`
    ALTER TABLE service_prices ADD COLUMN IF NOT EXISTS min_assembly_amount DECIMAL(10, 2) DEFAULT 3000
  `);
  await pool.query(`
    ALTER TABLE service_prices ADD COLUMN IF NOT EXISTS assembly_percent DECIMAL(5, 2) DEFAULT 12
  `);
}

// GET: List all service prices or get by region/city
export async function GET(request: NextRequest) {
  try {
    await ensureServicePricesTable();
    const pool = getPool();
    const { searchParams } = new URL(request.url);
    const region = searchParams.get('region');
    const city = searchParams.get('city');

    let result;

    // If city name is provided, look up its price_group first
    if (city || region) {
      const cityName = city || region;

      // First, try to find city's price_group
      const cityResult = await pool.query(`
        SELECT price_group FROM cities WHERE LOWER(name) = LOWER($1) LIMIT 1
      `, [cityName]);

      const priceGroup = cityResult.rows[0]?.price_group;

      if (priceGroup) {
        // Find price by city's price_group
        result = await pool.query(`
          SELECT * FROM service_prices
          WHERE is_active = true AND LOWER(region_group) = LOWER($1)
          ORDER BY sort_order ASC
          LIMIT 1
        `, [priceGroup]);
      }

      // If no price found by city's price_group, try partial match with city/region name
      if (!result || result.rows.length === 0) {
        result = await pool.query(`
          SELECT * FROM service_prices
          WHERE is_active = true AND (
            LOWER(region_group) = LOWER($1) OR
            LOWER($1) LIKE '%' || LOWER(region_group) || '%' OR
            LOWER(region_group) LIKE '%' || LOWER($1) || '%'
          )
          ORDER BY sort_order ASC
          LIMIT 1
        `, [cityName]);
      }

      // If still no match found, get the default (first active)
      if (!result || result.rows.length === 0) {
        result = await pool.query(`
          SELECT * FROM service_prices
          WHERE is_active = true
          ORDER BY sort_order ASC
          LIMIT 1
        `);
      }
    } else {
      result = await pool.query(`
        SELECT * FROM service_prices
        ORDER BY sort_order ASC, region_group ASC
      `);
    }

    return NextResponse.json({
      success: true,
      data: result.rows,
    });
  } catch (error) {
    console.error('Error fetching service prices:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new service price or import from xlsx
export async function POST(request: NextRequest) {
  try {
    await ensureServicePricesTable();
    const pool = getPool();

    const contentType = request.headers.get('content-type') || '';

    // Check if this is a file upload for import
    if (contentType.includes('multipart/form-data')) {
      const formData = await request.formData();
      const file = formData.get('file') as File;

      if (!file) {
        return NextResponse.json(
          { success: false, error: 'Файл не выбран' },
          { status: 400 }
        );
      }

      const buffer = Buffer.from(await file.arrayBuffer());
      const workbook = XLSX.read(buffer, { type: 'buffer' });

      // Find ПрайсУслуг sheet
      const sheetName = workbook.SheetNames.find(s =>
        s.toLowerCase().includes('прайсуслуг') || s.toLowerCase().includes('прайс')
      ) || workbook.SheetNames[0];

      const worksheet = workbook.Sheets[sheetName];
      const data = XLSX.utils.sheet_to_json(worksheet, { header: 1 }) as unknown[][];

      // Skip first 2 rows (title and header)
      const rows = data.slice(2);
      let imported = 0;

      for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        if (!row || row.length < 2) continue;

        const [
          regionGroup,
          deliveryBasePrice,
          deliveryPerKm,
          floorLiftPrice,
          elevatorLiftPrice,
          assemblyPerKm,
        ] = row as (string | number | undefined)[];

        if (!regionGroup || typeof regionGroup !== 'string') continue;

        // Check if already exists
        const existing = await pool.query(
          `SELECT id FROM service_prices WHERE LOWER(region_group) = LOWER($1)`,
          [regionGroup]
        );

        if (existing.rows.length > 0) {
          // Update existing
          await pool.query(
            `UPDATE service_prices SET
               delivery_base_price = $1,
               delivery_per_km = $2,
               floor_lift_price = $3,
               elevator_lift_price = $4,
               assembly_per_km = $5,
               sort_order = $6,
               updated_at = NOW()
             WHERE id = $7`,
            [
              deliveryBasePrice || 0,
              deliveryPerKm || 0,
              floorLiftPrice || 0,
              elevatorLiftPrice || 0,
              assemblyPerKm || 0,
              i + 1,
              existing.rows[0].id,
            ]
          );
        } else {
          // Insert new
          await pool.query(
            `INSERT INTO service_prices (region_group, delivery_base_price, delivery_per_km, floor_lift_price, elevator_lift_price, assembly_per_km, sort_order)
             VALUES ($1, $2, $3, $4, $5, $6, $7)`,
            [
              regionGroup,
              deliveryBasePrice || 0,
              deliveryPerKm || 0,
              floorLiftPrice || 0,
              elevatorLiftPrice || 0,
              assemblyPerKm || 0,
              i + 1,
            ]
          );
        }
        imported++;
      }

      return NextResponse.json({
        success: true,
        imported,
        message: `Импортировано ${imported} групп прайса`,
      });
    }

    // Regular create
    const body = await request.json();
    const {
      region_group,
      delivery_base_price,
      delivery_per_km,
      floor_lift_price,
      elevator_lift_price,
      assembly_per_km,
      min_assembly_amount,
      assembly_percent,
      sort_order,
      is_active
    } = body;

    if (!region_group || !region_group.trim()) {
      return NextResponse.json(
        { success: false, error: 'Название группы обязательно' },
        { status: 400 }
      );
    }

    // Check for duplicate
    const existing = await pool.query(
      `SELECT id FROM service_prices WHERE LOWER(region_group) = LOWER($1)`,
      [region_group.trim()]
    );

    if (existing.rows.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Группа с таким названием уже существует' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `INSERT INTO service_prices (region_group, delivery_base_price, delivery_per_km, floor_lift_price, elevator_lift_price, assembly_per_km, min_assembly_amount, assembly_percent, sort_order, is_active)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
       RETURNING *`,
      [
        region_group.trim(),
        delivery_base_price || 0,
        delivery_per_km || 0,
        floor_lift_price || 0,
        elevator_lift_price || 0,
        assembly_per_km || 0,
        min_assembly_amount || 3000,
        assembly_percent || 12,
        sort_order || 500,
        is_active !== false,
      ]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating service price:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
