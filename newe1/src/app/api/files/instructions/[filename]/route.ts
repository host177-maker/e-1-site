import { NextRequest, NextResponse } from 'next/server';
import { readFile } from 'fs/promises';
import { existsSync } from 'fs';
import path from 'path';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Handle both cases: running from repo root or from newe1 directory
const cwd = process.cwd();
const baseDir = cwd.endsWith('newe1') ? cwd : path.join(cwd, 'newe1');
const UPLOAD_DIR = path.join(baseDir, 'public', 'uploads', 'instructions');

export async function GET(
  request: NextRequest,
  { params }: { params: Promise<{ filename: string }> }
) {
  try {
    const { filename } = await params;

    // Security: only allow PDF files and prevent directory traversal
    if (!filename.endsWith('.pdf') || filename.includes('..') || filename.includes('/')) {
      return NextResponse.json(
        { error: 'Недопустимый файл' },
        { status: 400 }
      );
    }

    const filePath = path.join(UPLOAD_DIR, filename);

    if (!existsSync(filePath)) {
      return NextResponse.json(
        { error: 'Файл не найден' },
        { status: 404 }
      );
    }

    const fileBuffer = await readFile(filePath);

    return new NextResponse(fileBuffer, {
      headers: {
        'Content-Type': 'application/pdf',
        'Content-Disposition': `attachment; filename="${filename}"`,
        'Cache-Control': 'public, max-age=31536000',
      },
    });
  } catch (error) {
    console.error('Error serving file:', error);
    return NextResponse.json(
      { error: 'Ошибка при загрузке файла' },
      { status: 500 }
    );
  }
}
