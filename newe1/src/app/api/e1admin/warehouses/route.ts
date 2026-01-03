import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';
import * as XLSX from 'xlsx';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Ensure warehouses table exists
async function ensureWarehousesTable() {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS warehouses (
      id SERIAL PRIMARY KEY,
      external_id VARCHAR(50),
      sort_order INTEGER DEFAULT 500,
      name VARCHAR(255) NOT NULL,
      display_name VARCHAR(255),
      is_active BOOLEAN DEFAULT true,
      address TEXT,
      latitude DECIMAL(10, 6),
      longitude DECIMAL(10, 6),
      phone VARCHAR(100),
      working_hours TEXT,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )
  `);
}

// GET: List all warehouses
export async function GET() {
  try {
    await ensureWarehousesTable();
    const pool = getPool();

    const result = await pool.query(`
      SELECT w.*,
             COUNT(DISTINCT c.id) as city_count
      FROM warehouses w
      LEFT JOIN cities c ON c.warehouse_id = w.id
      GROUP BY w.id
      ORDER BY w.sort_order ASC, w.name ASC
    `);

    const activeCount = result.rows.filter(w => w.is_active).length;
    const inactiveCount = result.rows.filter(w => !w.is_active).length;

    return NextResponse.json({
      success: true,
      data: result.rows.map(w => ({
        ...w,
        city_count: parseInt(w.city_count) || 0,
      })),
      counts: {
        active: activeCount,
        inactive: inactiveCount,
        total: result.rows.length,
      },
    });
  } catch (error) {
    console.error('Error fetching warehouses:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new warehouse or import from xlsx
export async function POST(request: NextRequest) {
  try {
    await ensureWarehousesTable();
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
      const sheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[sheetName];
      const data = XLSX.utils.sheet_to_json(worksheet, { header: 1 }) as unknown[][];

      // Skip header row, take only first 18 rows (as per requirement)
      const rows = data.slice(1, 18);
      let imported = 0;

      for (const row of rows) {
        if (!row || row.length < 2) continue;

        const [
          externalId,
          sortOrder,
          name,
          displayName,
          isActiveStr,
          address,
          latitude,
          longitude,
          phone,
          workingHours,
        ] = row as (string | number | undefined)[];

        if (!name) continue;

        const isActive = isActiveStr === 'Да' || isActiveStr === 'да' || isActiveStr === 'TRUE' || isActiveStr === 'true' || isActiveStr === 1;

        // Check if warehouse already exists
        const existing = await pool.query(
          `SELECT id FROM warehouses WHERE external_id = $1 OR LOWER(name) = LOWER($2)`,
          [String(externalId), String(name)]
        );

        if (existing.rows.length > 0) {
          // Update existing
          await pool.query(
            `UPDATE warehouses SET
               sort_order = $1,
               display_name = $2,
               is_active = $3,
               address = $4,
               latitude = $5,
               longitude = $6,
               phone = $7,
               working_hours = $8,
               updated_at = NOW()
             WHERE id = $9`,
            [
              sortOrder || 500,
              displayName || name,
              isActive,
              address || null,
              latitude || null,
              longitude || null,
              phone || null,
              workingHours || null,
              existing.rows[0].id,
            ]
          );
        } else {
          // Insert new
          await pool.query(
            `INSERT INTO warehouses (external_id, sort_order, name, display_name, is_active, address, latitude, longitude, phone, working_hours)
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)`,
            [
              String(externalId),
              sortOrder || 500,
              String(name),
              displayName || name,
              isActive,
              address || null,
              latitude || null,
              longitude || null,
              phone || null,
              workingHours || null,
            ]
          );
        }
        imported++;
      }

      return NextResponse.json({
        success: true,
        imported,
        message: `Импортировано ${imported} складов`,
      });
    }

    // Regular create
    const body = await request.json();
    const { name, display_name, address, latitude, longitude, phone, working_hours, sort_order, is_active } = body;

    if (!name || !name.trim()) {
      return NextResponse.json(
        { success: false, error: 'Название обязательно' },
        { status: 400 }
      );
    }

    const result = await pool.query(
      `INSERT INTO warehouses (name, display_name, address, latitude, longitude, phone, working_hours, sort_order, is_active)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
       RETURNING *`,
      [
        name.trim(),
        display_name?.trim() || name.trim(),
        address?.trim() || null,
        latitude || null,
        longitude || null,
        phone?.trim() || null,
        working_hours?.trim() || null,
        sort_order || 500,
        is_active !== false,
      ]
    );

    return NextResponse.json({
      success: true,
      data: { ...result.rows[0], city_count: 0 },
    });
  } catch (error) {
    console.error('Error creating warehouse:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
