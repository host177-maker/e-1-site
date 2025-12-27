import { NextRequest, NextResponse } from 'next/server';
import { writeFile, mkdir } from 'fs/promises';
import { existsSync } from 'fs';
import path from 'path';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

// Handle both cases: running from repo root or from newe1 directory
const cwd = process.cwd();
const baseDir = cwd.endsWith('newe1') ? cwd : path.join(cwd, 'newe1');
const UPLOAD_DIR = path.join(baseDir, 'public', 'uploads', 'instructions');
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB for PDFs
const ALLOWED_TYPES = ['application/pdf'];

export async function POST(request: NextRequest) {
  try {
    // Ensure upload directory exists
    if (!existsSync(UPLOAD_DIR)) {
      await mkdir(UPLOAD_DIR, { recursive: true });
    }

    const formData = await request.formData();
    const file = formData.get('file') as File;

    if (!file) {
      return NextResponse.json(
        { success: false, message: 'Файл не выбран' },
        { status: 400 }
      );
    }

    // Validate file type
    if (!ALLOWED_TYPES.includes(file.type)) {
      return NextResponse.json(
        { success: false, message: 'Недопустимый формат файла. Разрешены только PDF' },
        { status: 400 }
      );
    }

    // Validate file size
    if (file.size > MAX_FILE_SIZE) {
      return NextResponse.json(
        { success: false, message: 'Файл слишком большой. Максимум 50MB' },
        { status: 400 }
      );
    }

    // Generate unique filename
    const uniqueName = `instruction-${Date.now()}-${Math.random().toString(36).substring(2, 9)}.pdf`;
    const filePath = path.join(UPLOAD_DIR, uniqueName);

    // Write file
    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);
    await writeFile(filePath, buffer);

    return NextResponse.json({
      success: true,
      path: `/api/files/instructions/${uniqueName}`,
    });
  } catch (error) {
    console.error('Error uploading file:', error);
    const errorMessage = error instanceof Error ? error.message : 'Неизвестная ошибка';
    return NextResponse.json(
      { success: false, message: `Ошибка при загрузке файла: ${errorMessage}` },
      { status: 500 }
    );
  }
}
