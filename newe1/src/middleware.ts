import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Redirect old URLs to new ones for backward compatibility
  if (pathname === '/client/purchase-warranty' || pathname === '/client/purchase-warranty/') {
    return NextResponse.redirect(new URL('/service/purchase-terms', request.url), 301);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/client/:path*'],
};
