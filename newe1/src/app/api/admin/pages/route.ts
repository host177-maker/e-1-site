import { NextRequest, NextResponse } from 'next/server';
import { getCurrentUser } from '@/lib/auth';
import { getPages, createPage, getPageBySlug } from '@/lib/db';

// GET - список страниц
export async function GET() {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const pages = getPages();
    return NextResponse.json({ pages });
  } catch (error) {
    console.error('Get pages error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// POST - создать страницу
export async function POST(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { slug, title, content, metaDescription, metaKeywords, isPublished } = await request.json();

    if (!slug || !title) {
      return NextResponse.json(
        { error: 'URL (slug) и заголовок обязательны' },
        { status: 400 }
      );
    }

    // Проверка формата slug (разрешены буквы, цифры, дефисы и слэши для вложенности)
    const cleanSlug = slug.replace(/^\/+|\/+$/g, ''); // убираем слэши в начале и конце
    if (!/^[a-z0-9-]+(\/[a-z0-9-]+)*$/.test(cleanSlug)) {
      return NextResponse.json(
        { error: 'URL может содержать только латинские буквы, цифры, дефисы и слэши' },
        { status: 400 }
      );
    }

    if (getPageBySlug(slug)) {
      return NextResponse.json(
        { error: 'Страница с таким URL уже существует' },
        { status: 400 }
      );
    }

    const page = createPage({
      slug,
      title,
      content: content || '',
      metaDescription,
      metaKeywords,
      isPublished: isPublished ?? false,
      createdBy: currentUser.username,
    });

    return NextResponse.json({ success: true, page });
  } catch (error) {
    console.error('Create page error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}
