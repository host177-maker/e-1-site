import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

async function ensureInstructionsTable(): Promise<void> {
  const pool = getPool();
  await pool.query(`
    CREATE TABLE IF NOT EXISTS instructions (
      id SERIAL PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      pdf_path VARCHAR(500) NOT NULL,
      video_url VARCHAR(500),
      is_active BOOLEAN DEFAULT true,
      sort_order INTEGER DEFAULT 0,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )
  `);
}

// GET: List all instructions
export async function GET() {
  try {
    await ensureInstructionsTable();
    const pool = getPool();

    const result = await pool.query(
      `SELECT * FROM instructions ORDER BY sort_order ASC, created_at DESC`
    );

    return NextResponse.json({
      success: true,
      data: result.rows,
    });
  } catch (error) {
    console.error('Error fetching instructions:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// POST: Create new instruction
export async function POST(request: NextRequest) {
  try {
    await ensureInstructionsTable();
    const pool = getPool();
    const body = await request.json();

    const { name, pdf_path, video_url, is_active } = body;

    if (!name || !pdf_path) {
      return NextResponse.json(
        { success: false, error: 'Название и PDF-файл обязательны' },
        { status: 400 }
      );
    }

    // Get max sort_order
    const maxOrderResult = await pool.query(
      `SELECT COALESCE(MAX(sort_order), 0) as max_order FROM instructions`
    );
    const nextOrder = maxOrderResult.rows[0].max_order + 1;

    const result = await pool.query(
      `INSERT INTO instructions (name, pdf_path, video_url, sort_order, is_active)
       VALUES ($1, $2, $3, $4, $5)
       RETURNING *`,
      [name, pdf_path, video_url || null, nextOrder, is_active !== false]
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error creating instruction:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update instruction order (bulk update)
export async function PATCH(request: NextRequest) {
  try {
    await ensureInstructionsTable();
    const pool = getPool();
    const body = await request.json();

    const { order } = body; // Array of { id, sort_order }

    if (!Array.isArray(order)) {
      return NextResponse.json(
        { success: false, error: 'Неверный формат данных' },
        { status: 400 }
      );
    }

    // Update each instruction's sort_order
    for (const item of order) {
      await pool.query(
        `UPDATE instructions SET sort_order = $1, updated_at = NOW() WHERE id = $2`,
        [item.sort_order, item.id]
      );
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Error updating instruction order:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
