import { NextResponse } from 'next/server';
import { getCurrentAdmin, initializeDefaultAdmin } from '@/lib/auth';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

export async function GET() {
  try {
    // Initialize default admin if needed
    await initializeDefaultAdmin();

    const admin = await getCurrentAdmin();

    if (!admin) {
      return NextResponse.json(
        { success: false, authenticated: false },
        { status: 401 }
      );
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
    return NextResponse.json(
      { success: false, error: 'Ошибка сервера' },
      { status: 500 }
    );
  }
}
