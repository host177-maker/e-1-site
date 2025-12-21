import { NextRequest, NextResponse } from 'next/server';
import { getCurrentUser } from '@/lib/auth';
import { getMessengers, saveMessengers, addMessenger, updateMessenger, deleteMessenger } from '@/lib/db';

// GET - получить мессенджеры
export async function GET() {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const messengers = getMessengers();
    return NextResponse.json({ messengers });
  } catch (error) {
    console.error('Get messengers error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// POST - добавить мессенджер
export async function POST(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { name, url, icon, order, isActive, showInHeader, showInFooter } = await request.json();

    if (!name || !url) {
      return NextResponse.json(
        { error: 'Название и URL обязательны' },
        { status: 400 }
      );
    }

    const messenger = addMessenger({
      name,
      url,
      icon: icon || 'default',
      order: order ?? getMessengers().length + 1,
      isActive: isActive ?? true,
      showInHeader: showInHeader ?? true,
      showInFooter: showInFooter ?? true,
    });

    return NextResponse.json({ success: true, messenger });
  } catch (error) {
    console.error('Add messenger error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// PUT - обновить мессенджер или список
export async function PUT(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const body = await request.json();

    // Если передан массив - сохраняем всё
    if (body.messengers) {
      saveMessengers(body.messengers);
      return NextResponse.json({ success: true, messengers: body.messengers });
    }

    // Иначе обновляем один
    const { id, ...updates } = body;

    if (!id) {
      return NextResponse.json({ error: 'ID обязателен' }, { status: 400 });
    }

    const messenger = updateMessenger(id, updates);
    if (!messenger) {
      return NextResponse.json({ error: 'Мессенджер не найден' }, { status: 404 });
    }

    return NextResponse.json({ success: true, messenger });
  } catch (error) {
    console.error('Update messenger error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// DELETE - удалить мессенджер
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

    const deleted = deleteMessenger(id);
    if (!deleted) {
      return NextResponse.json({ error: 'Мессенджер не найден' }, { status: 404 });
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Delete messenger error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}
