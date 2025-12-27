import { NextRequest, NextResponse } from 'next/server';
import { writeFile, mkdir } from 'fs/promises';
import { existsSync } from 'fs';
import path from 'path';

export const dynamic = 'force-dynamic';
export const runtime = 'nodejs';

const UPLOAD_DIR = path.join(process.cwd(), 'newe1', 'public', 'uploads', 'reviews');
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

export async function POST(request: NextRequest) {
  try {
    // Ensure upload directory exists
    if (!existsSync(UPLOAD_DIR)) {
      await mkdir(UPLOAD_DIR, { recursive: true });
    }

    const formData = await request.formData();
    const files = formData.getAll('photos') as File[];

    if (!files || files.length === 0) {
      return NextResponse.json(
        { success: false, message: 'Файлы не выбраны' },
        { status: 400 }
      );
    }

    if (files.length > 5) {
      return NextResponse.json(
        { success: false, message: 'Можно загрузить не более 5 фотографий' },
        { status: 400 }
      );
    }

    const uploadedPaths: string[] = [];

    for (const file of files) {
      // Validate file type
      if (!ALLOWED_TYPES.includes(file.type)) {
        return NextResponse.json(
          { success: false, message: `Недопустимый формат файла: ${file.name}. Разрешены: JPG, PNG, WebP` },
          { status: 400 }
        );
      }

      // Validate file size
      if (file.size > MAX_FILE_SIZE) {
        return NextResponse.json(
          { success: false, message: `Файл ${file.name} слишком большой. Максимум 5MB` },
          { status: 400 }
        );
      }

      // Generate unique filename
      const ext = file.name.split('.').pop() || 'jpg';
      const uniqueName = `${Date.now()}-${Math.random().toString(36).substring(2, 9)}.${ext}`;
      const filePath = path.join(UPLOAD_DIR, uniqueName);

      // Write file
      const bytes = await file.arrayBuffer();
      const buffer = Buffer.from(bytes);
      await writeFile(filePath, buffer);

      uploadedPaths.push(`/uploads/reviews/${uniqueName}`);
    }

    return NextResponse.json({
      success: true,
      paths: uploadedPaths,
    });
  } catch (error) {
    console.error('Error uploading files:', error);
    return NextResponse.json(
      { success: false, message: 'Ошибка при загрузке файлов' },
      { status: 500 }
    );
  }
}
