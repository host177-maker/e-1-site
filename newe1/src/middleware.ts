import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const SESSION_COOKIE_NAME = 'e1admin_session';

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Redirect old URLs to new ones for backward compatibility
  if (pathname === '/client/purchase-warranty' || pathname === '/client/purchase-warranty/') {
    return NextResponse.redirect(new URL('/service/purchase-terms', request.url), 301);
  }

  // Protect admin pages (except login)
  if (pathname.startsWith('/e1admin') && pathname !== '/e1admin/login' && pathname !== '/e1admin/login/') {
    const sessionCookie = request.cookies.get(SESSION_COOKIE_NAME);

    // Just check if cookie exists - full validation happens in API/pages
    if (!sessionCookie?.value) {
      return NextResponse.redirect(new URL('/e1admin/login', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/client/:path*', '/e1admin/:path*'],
};
