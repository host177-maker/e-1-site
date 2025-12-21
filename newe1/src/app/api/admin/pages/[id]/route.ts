import { NextRequest, NextResponse } from 'next/server';
import { getCurrentUser } from '@/lib/auth';
import { getPageById, updatePage, deletePage, getPageBySlug } from '@/lib/db';

// GET - получить страницу
export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { id } = await params;
    const page = getPageById(id);
    if (!page) {
      return NextResponse.json({ error: 'Страница не найдена' }, { status: 404 });
    }

    return NextResponse.json({ page });
  } catch (error) {
    console.error('Get page error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// PUT - обновить страницу
export async function PUT(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { id } = await params;
    const { slug, title, content, metaDescription, metaKeywords, isPublished } = await request.json();

    if (!title) {
      return NextResponse.json(
        { error: 'Заголовок обязателен' },
        { status: 400 }
      );
    }

    // Проверка slug если он изменился
    if (slug) {
      if (!/^[a-z0-9-]+$/.test(slug)) {
        return NextResponse.json(
          { error: 'URL может содержать только латинские буквы, цифры и дефисы' },
          { status: 400 }
        );
      }

      const existingPage = getPageBySlug(slug);
      if (existingPage && existingPage.id !== id) {
        return NextResponse.json(
          { error: 'Страница с таким URL уже существует' },
          { status: 400 }
        );
      }
    }

    const page = updatePage(id, {
      slug,
      title,
      content,
      metaDescription,
      metaKeywords,
      isPublished,
    });

    if (!page) {
      return NextResponse.json({ error: 'Страница не найдена' }, { status: 404 });
    }

    return NextResponse.json({ success: true, page });
  } catch (error) {
    console.error('Update page error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// DELETE - удалить страницу
export async function DELETE(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { id } = await params;
    const deleted = deletePage(id);
    if (!deleted) {
      return NextResponse.json({ error: 'Страница не найдена' }, { status: 404 });
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Delete page error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}
