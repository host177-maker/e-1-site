import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import crypto from 'crypto';

const SESSION_COOKIE_NAME = 'e1admin_session';

// Verify session token in middleware (simplified check)
function verifySessionToken(token: string): boolean {
  try {
    const [ivHex, encrypted] = token.split(':');
    if (!ivHex || !encrypted) return false;

    const secret = process.env.SESSION_SECRET || 'default-secret-change-in-production-please';
    const key = crypto.scryptSync(secret, 'salt', 32);
    const iv = Buffer.from(ivHex, 'hex');
    const decipher = crypto.createDecipheriv('aes-256-cbc', key, iv);
    let decrypted = decipher.update(encrypted, 'hex', 'utf8');
    decrypted += decipher.final('utf8');

    const payload = JSON.parse(decrypted);

    // Check expiration
    if (payload.exp < Date.now()) {
      return false;
    }

    return true;
  } catch {
    return false;
  }
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Redirect old URLs to new ones for backward compatibility
  if (pathname === '/client/purchase-warranty' || pathname === '/client/purchase-warranty/') {
    return NextResponse.redirect(new URL('/service/purchase-terms', request.url), 301);
  }

  // Protect admin pages (except login)
  if (pathname.startsWith('/e1admin') && pathname !== '/e1admin/login' && pathname !== '/e1admin/login/') {
    const sessionCookie = request.cookies.get(SESSION_COOKIE_NAME);

    if (!sessionCookie || !verifySessionToken(sessionCookie.value)) {
      return NextResponse.redirect(new URL('/e1admin/login', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/client/:path*', '/e1admin/:path*'],
};
