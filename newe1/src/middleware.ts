import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { jwtVerify } from 'jose';

const JWT_SECRET = new TextEncoder().encode(
  process.env.JWT_SECRET || 'e1-admin-secret-key-change-in-production'
);

export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Защита API админки
  if (pathname.startsWith('/api/admin')) {
    const token = request.cookies.get('admin_token')?.value;

    if (!token) {
      return NextResponse.json({ error: 'Не авторизован' }, { status: 401 });
    }

    try {
      const { payload } = await jwtVerify(token, JWT_SECRET);
      if (payload.role !== 'admin') {
        return NextResponse.json({ error: 'Доступ запрещён' }, { status: 403 });
      }
    } catch {
      return NextResponse.json({ error: 'Недействительный токен' }, { status: 401 });
    }
  }

  // Защита страниц админки (кроме страницы входа)
  if (pathname.startsWith('/adminka') && pathname !== '/adminka') {
    const token = request.cookies.get('admin_token')?.value;

    if (!token) {
      return NextResponse.redirect(new URL('/adminka', request.url));
    }

    try {
      await jwtVerify(token, JWT_SECRET);
    } catch {
      const response = NextResponse.redirect(new URL('/adminka', request.url));
      response.cookies.delete('admin_token');
      return response;
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/adminka/:path*', '/api/admin/:path*'],
};
