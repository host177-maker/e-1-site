import { NextRequest, NextResponse } from 'next/server';
import { getB2BLeads, getB2BLeadsCounts, deleteB2BLead, deleteB2BLeads, LeadType } from '@/lib/b2bLeads';

export const dynamic = 'force-dynamic';

// GET - Get all B2B leads with filtering
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url);
    const search = searchParams.get('search') || undefined;
    const type = searchParams.get('type') as LeadType | undefined;
    const limit = searchParams.get('limit') ? parseInt(searchParams.get('limit')!, 10) : 50;
    const offset = searchParams.get('offset') ? parseInt(searchParams.get('offset')!, 10) : 0;

    const { leads, total } = await getB2BLeads({
      search,
      type: type || undefined,
      limit,
      offset,
    });

    const counts = await getB2BLeadsCounts();

    return NextResponse.json({
      success: true,
      data: leads,
      total,
      counts,
    });
  } catch (error) {
    console.error('Error fetching B2B leads:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка получения данных' },
      { status: 500 }
    );
  }
}

// DELETE - Delete one or multiple leads
export async function DELETE(request: NextRequest) {
  try {
    const body = await request.json();

    // Handle single delete
    if (body.id) {
      const success = await deleteB2BLead(body.id);
      if (!success) {
        return NextResponse.json(
          { success: false, error: 'Заявка не найдена' },
          { status: 404 }
        );
      }
      return NextResponse.json({ success: true, message: 'Заявка удалена' });
    }

    // Handle bulk delete
    if (Array.isArray(body.ids) && body.ids.length > 0) {
      const deletedCount = await deleteB2BLeads(body.ids);
      return NextResponse.json({
        success: true,
        message: `Удалено заявок: ${deletedCount}`,
        deletedCount,
      });
    }

    return NextResponse.json(
      { success: false, error: 'Не указаны ID для удаления' },
      { status: 400 }
    );
  } catch (error) {
    console.error('Error deleting B2B leads:', error);
    return NextResponse.json(
      { success: false, error: 'Ошибка удаления' },
      { status: 500 }
    );
  }
}
