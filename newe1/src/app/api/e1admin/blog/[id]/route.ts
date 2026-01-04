import { NextRequest, NextResponse } from 'next/server';
import { getPool } from '@/lib/db';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// GET: Get single article by ID
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      'SELECT * FROM blog_articles WHERE id = $1',
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Статья не найдена' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error fetching blog article:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// PATCH: Update article
export async function PATCH(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();
    const body = await request.json();

    // Check if article exists
    const existing = await pool.query(
      'SELECT id FROM blog_articles WHERE id = $1',
      [id]
    );
    if (existing.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Статья не найдена' },
        { status: 404 }
      );
    }

    // Build dynamic update query
    const updates: string[] = [];
    const values: (string | number | boolean | null)[] = [];
    let paramIndex = 1;

    const fields = [
      'slug', 'title', 'category', 'short_description', 'content',
      'cover_image', 'published_at', 'author_name', 'is_active',
      'meta_title', 'meta_description'
    ];

    for (const field of fields) {
      if (body[field] !== undefined) {
        updates.push(`${field} = $${paramIndex}`);
        values.push(body[field]);
        paramIndex++;
      }
    }

    if (updates.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Нет данных для обновления' },
        { status: 400 }
      );
    }

    // Check for duplicate slug if updating slug
    if (body.slug) {
      const slugCheck = await pool.query(
        'SELECT id FROM blog_articles WHERE slug = $1 AND id != $2',
        [body.slug, id]
      );
      if (slugCheck.rows.length > 0) {
        return NextResponse.json(
          { success: false, error: 'Статья с такой ссылкой уже существует' },
          { status: 400 }
        );
      }
    }

    updates.push(`updated_at = NOW()`);
    values.push(id);

    const result = await pool.query(
      `UPDATE blog_articles SET ${updates.join(', ')} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    return NextResponse.json({
      success: true,
      data: result.rows[0],
    });
  } catch (error) {
    console.error('Error updating blog article:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}

// DELETE: Delete article
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const { id } = await params;
    const pool = getPool();

    const result = await pool.query(
      'DELETE FROM blog_articles WHERE id = $1 RETURNING id',
      [id]
    );

    if (result.rows.length === 0) {
      return NextResponse.json(
        { success: false, error: 'Статья не найдена' },
        { status: 404 }
      );
    }

    return NextResponse.json({
      success: true,
      message: 'Статья удалена',
    });
  } catch (error) {
    console.error('Error deleting blog article:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
