import { NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET() {
  try {
    const admin = await getCurrentAdmin();

    if (!admin) {
      return NextResponse.json({ success: true, authenticated: false });
    }

    return NextResponse.json({
      success: true,
      authenticated: true,
      user: {
        id: admin.id,
        username: admin.username,
      },
    });
  } catch (error) {
    console.error('Session check error:', error);
    return NextResponse.json({ success: true, authenticated: false });
  }
}
