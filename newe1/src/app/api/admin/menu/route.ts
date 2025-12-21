import { NextRequest, NextResponse } from 'next/server';
import { getCurrentUser } from '@/lib/auth';
import { getMenu, saveMenu, addMenuItem, updateMenuItem, deleteMenuItem, MenuConfig } from '@/lib/db';

// GET - получить меню
export async function GET() {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const menu = getMenu();
    return NextResponse.json({ menu });
  } catch (error) {
    console.error('Get menu error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// POST - добавить пункт меню
export async function POST(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { menuType, title, url, icon, parentId, order, isVisible } = await request.json();

    if (!menuType || !title || !url) {
      return NextResponse.json(
        { error: 'Тип меню, название и URL обязательны' },
        { status: 400 }
      );
    }

    if (!['topMenu', 'mainMenu', 'footerMenu'].includes(menuType)) {
      return NextResponse.json(
        { error: 'Неверный тип меню' },
        { status: 400 }
      );
    }

    const item = addMenuItem(menuType as keyof MenuConfig, {
      title,
      url,
      icon,
      parentId,
      order: order ?? 0,
      isVisible: isVisible ?? true,
    });

    return NextResponse.json({ success: true, item });
  } catch (error) {
    console.error('Add menu item error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// PUT - обновить пункт меню или всё меню
export async function PUT(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const body = await request.json();

    // Если передан полный объект меню - сохраняем его целиком
    if (body.menu) {
      saveMenu(body.menu);
      return NextResponse.json({ success: true, menu: body.menu });
    }

    // Иначе обновляем отдельный пункт
    const { menuType, id, title, url, icon, parentId, order, isVisible } = body;

    if (!menuType || !id) {
      return NextResponse.json(
        { error: 'Тип меню и ID обязательны' },
        { status: 400 }
      );
    }

    const item = updateMenuItem(menuType as keyof MenuConfig, id, {
      title,
      url,
      icon,
      parentId,
      order,
      isVisible,
    });

    if (!item) {
      return NextResponse.json({ error: 'Пункт меню не найден' }, { status: 404 });
    }

    return NextResponse.json({ success: true, item });
  } catch (error) {
    console.error('Update menu error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}

// DELETE - удалить пункт меню
export async function DELETE(request: NextRequest) {
  try {
    const currentUser = await getCurrentUser();
    if (!currentUser || currentUser.role !== 'admin') {
      return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
    }

    const { menuType, id } = await request.json();

    if (!menuType || !id) {
      return NextResponse.json(
        { error: 'Тип меню и ID обязательны' },
        { status: 400 }
      );
    }

    const deleted = deleteMenuItem(menuType as keyof MenuConfig, id);
    if (!deleted) {
      return NextResponse.json({ error: 'Пункт меню не найден' }, { status: 404 });
    }

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error('Delete menu item error:', error);
    return NextResponse.json({ error: 'Ошибка сервера' }, { status: 500 });
  }
}
