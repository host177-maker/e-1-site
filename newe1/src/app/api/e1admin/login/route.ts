import { NextRequest, NextResponse } from 'next/server';
import { loginAdmin, SESSION_COOKIE_NAME } from '@/lib/auth';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { username, password } = body;

    if (!username || !password) {
      return NextResponse.json(
        { success: false, error: 'Введите имя пользователя и пароль' },
        { status: 400 }
      );
    }

    const result = await loginAdmin(username, password);

    if (!result.success) {
      return NextResponse.json(
        { success: false, error: result.error },
        { status: 401 }
      );
    }

    const response = NextResponse.json({ success: true });

    // Only use secure cookies on HTTPS (not on localhost/local network)
    const host = request.headers.get('host') || '';
    const isLocalhost = host.includes('localhost') || host.includes('127.0.0.1') || host.startsWith('192.168.');
    const useSecure = process.env.NODE_ENV === 'production' && !isLocalhost;

    response.cookies.set(SESSION_COOKIE_NAME, result.token!, {
      httpOnly: true,
      secure: useSecure,
      sameSite: 'lax',
      maxAge: 24 * 60 * 60,
      path: '/',
    });

    return response;
  } catch (error) {
    console.error('Login route error:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
