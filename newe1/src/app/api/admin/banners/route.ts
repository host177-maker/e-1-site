import { NextRequest, NextResponse } from 'next/server';
import { getCurrentUser } from '@/lib/auth';
import { getBanners, saveBanners, addBanner, updateBanner, deleteBanner } from '@/lib/db';

// GET - получить баннеры
export async function GET() {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const banners = getBanners();
    return NextResponse.json({ banners });
  } catch (error) {
    console.error('Get banners error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// POST - добавить баннер
export async function POST(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { image, alt, link, order, isActive } = await request.json();

    if (!image || !alt) {
      return NextResponse.json(
        { error: 'Изображение и описание обязательны' },
        { status: 400 }
      );
    }

    const banner = addBanner({
      image,
      alt,
      link: link || '/',
      order: order ?? getBanners().length + 1,
      isActive: isActive ?? true,
    });

    return NextResponse.json({ success: true, banner });
  } catch (error) {
    console.error('Add banner error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// PUT - обновить баннер или список баннеров
export async function PUT(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const body = await request.json();

    // Если передан массив баннеров - сохраняем всё
    if (body.banners) {
      saveBanners(body.banners);
      return NextResponse.json({ success: true, banners: body.banners });
    }

    // Иначе обновляем один баннер
    const { id, image, alt, link, order, isActive } = body;

    if (!id) {
      return NextResponse.json({ error: 'ID обязателен' }, { status: 400 });
    }

    const banner = updateBanner(id, { image, alt, link, order, isActive });
    if (!banner) {
      return NextResponse.json({ error: 'Баннер не найден' }, { status: 404 });
    }

    return NextResponse.json({ success: true, banner });
  } catch (error) {
    console.error('Update banner error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// DELETE - удалить баннер
export async function DELETE(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { id } = await request.json();

    if (!id) {
      return NextResponse.json({ error: 'ID обязателен' }, { status: 400 });
    }

    const deleted = deleteBanner(id);
    if (!deleted) {
      return NextResponse.json({ error: 'Баннер не найден' }, { status: 404 });
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Delete banner error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}
