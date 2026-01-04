import { NextRequest, NextResponse } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { importCatalog } from '@/lib/catalog';
import { getPool } from '@/lib/db';
import * as XLSX from 'xlsx';

// Максимальный размер файла - 10 МБ
const MAX_FILE_SIZE = 10 * 1024 * 1024;

export async function POST(request: NextRequest) {
  try {
    const admin = await getCurrentAdmin();
    if (!admin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const formData = await request.formData();
    const file = formData.get('file') as File | null;
    const googleSheetUrl = formData.get('googleSheetUrl') as string | null;

    let workbook: XLSX.WorkBook;

    if (file) {
      // Загрузка из файла
      if (file.size > MAX_FILE_SIZE) {
        return NextResponse.json({ error: 'Файл слишком большой (макс. 10 МБ)' }, { status: 400 });
      }

      const buffer = Buffer.from(await file.arrayBuffer());
      workbook = XLSX.read(buffer, { type: 'buffer' });
    } else if (googleSheetUrl) {
      // Загрузка из Google Sheets
      // Преобразуем URL в CSV export URL
      const match = googleSheetUrl.match(/\/d\/([a-zA-Z0-9-_]+)/);
      if (!match) {
        return NextResponse.json({ error: 'Неверный URL Google Sheets' }, { status: 400 });
      }

      const spreadsheetId = match[1];
      const sheets = ['Карточки товаров', 'Описание серий и групповых преи', 'Наполнение корпуса', 'Цвета  корпуса', 'Описание услуг'];

      workbook = XLSX.utils.book_new();

      for (const sheetName of sheets) {
        const csvUrl = `https://docs.google.com/spreadsheets/d/${spreadsheetId}/gviz/tq?tqx=out:csv&sheet=${encodeURIComponent(sheetName)}`;
        try {
          const response = await fetch(csvUrl);
          if (!response.ok) {
            console.error(`Не удалось загрузить лист "${sheetName}": ${response.status}`);
            continue;
          }
          const csvData = await response.text();
          const sheet = XLSX.read(csvData, { type: 'string' });
          const ws = sheet.Sheets[sheet.SheetNames[0]];
          XLSX.utils.book_append_sheet(workbook, ws, sheetName);
        } catch (e) {
          console.error(`Ошибка загрузки листа "${sheetName}":`, e);
        }
      }
    } else {
      return NextResponse.json({ error: 'Укажите файл или URL Google Sheets' }, { status: 400 });
    }

    // Парсинг данных из Excel
    const data = parseExcelData(workbook);

    // Импорт в БД
    const result = await importCatalog(data, admin.id);

    return NextResponse.json({
      success: result.success,
      message: result.success ? 'Каталог успешно импортирован' : 'Импорт завершён с ошибками',
      stats: {
        series: result.seriesCount,
        bodyColors: result.bodyColorsCount,
        fillings: result.fillingsCount,
        products: result.productsCount,
        variants: result.variantsCount,
        services: result.servicesCount
      },
      errors: result.errors.slice(0, 50) // Показываем первые 50 ошибок
    });
  } catch (error) {
    console.error('Import error:', error);
    return NextResponse.json(
      { error: error instanceof Error ? error.message : 'Ошибка импорта' },
      { status: 500 }
    );
  }
}

function parseExcelData(workbook: XLSX.WorkBook) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const products: any[] = [];
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const series: any[] = [];
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const fillings: any[] = [];
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const bodyColors: any[] = [];
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const services: any[] = [];

  // 1. Парсинг листа "Карточки товаров"
  const productsSheet = workbook.Sheets['Карточки товаров'];
  if (productsSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(productsSheet, { header: 1 });
    // Пропускаем заголовок
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      if (!row || !row[0] || !row[2]) continue; // Пропускаем пустые строки

      products.push({
        card_name: String(row[0] || '').trim(),
        full_name: String(row[1] || '').trim(),
        article: String(row[2] || '').trim(),
        body_color: String(row[3] || '').trim(),
        profile_color: String(row[4] || '').trim(),
        height: parseFloat(row[5]) || 0,
        width: parseFloat(row[6]) || 0,
        depth: parseFloat(row[7]) || 0,
        series: String(row[8] || '').trim(),
        group: String(row[9] || '').trim(),
        door_type: String(row[10] || '').trim(),
        door_count: parseInt(row[11]) || 0,
        door_material1: row[12] && row[12] !== '-' ? String(row[12]).trim() : null,
        door_material2: row[13] && row[13] !== '-' ? String(row[13]).trim() : null,
        door_material3: row[14] && row[14] !== '-' ? String(row[14]).trim() : null,
        door_material4: row[15] && row[15] !== '-' ? String(row[15]).trim() : null,
        door_material5: row[16] && row[16] !== '-' ? String(row[16]).trim() : null,
        door_material6: row[17] && row[17] !== '-' ? String(row[17]).trim() : null,
        image_white: String(row[18] || '').trim() || null,
        image_interior: String(row[19] || '').trim() || null,
        // Колонки U и V - рекламные плашки
        discount_percent: row[20] ? parseFloat(row[20]) || null : null,
        promo_badge: String(row[21] || '').trim() || null
      });
    }
  }

  // 2. Парсинг листа "Описание серий"
  const seriesSheetName = workbook.SheetNames.find(n => n.includes('Описание серий'));
  const seriesSheet = seriesSheetName ? workbook.Sheets[seriesSheetName] : null;
  if (seriesSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(seriesSheet, { header: 1 });
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      if (!row || !row[0]) continue;

      series.push({
        name: String(row[0] || '').trim(),
        description: String(row[1] || '').trim() || null,
        image1: String(row[2] || '').trim() || null,
        image2: String(row[3] || '').trim() || null,
        image3: String(row[4] || '').trim() || null,
        image4: String(row[5] || '').trim() || null,
        video1: String(row[6] || '').trim() || null,
        video2: String(row[7] || '').trim() || null,
        gallery_folder: String(row[8] || '').trim() || null
      });
    }
  }

  // Добавляем серии из товаров, которых нет в листе "Описание серий"
  const existingSeriesNames = new Set(series.map(s => s.name));
  const productSeriesNames = new Set(products.map(p => p.series).filter(Boolean));
  for (const seriesName of productSeriesNames) {
    if (!existingSeriesNames.has(seriesName)) {
      series.push({ name: seriesName });
    }
  }

  // 3. Парсинг листа "Наполнение корпуса"
  const fillingsSheet = workbook.Sheets['Наполнение корпуса'];
  if (fillingsSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(fillingsSheet, { header: 1 });
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      if (!row || !row[0]) continue;

      fillings.push({
        series: String(row[0] || '').trim(),
        door_count: parseInt(row[1]) || 0,
        height: parseFloat(row[2]) || 0,
        width: parseFloat(row[3]) || 0,
        depth: parseFloat(row[4]) || 0,
        short_name: String(row[5] || '').trim() || null,
        description: String(row[6] || '').trim() || null,
        image_plain: String(row[7] || '').trim() || null,
        image_dimensions: String(row[8] || '').trim() || null,
        image_filled: String(row[9] || '').trim() || null,
        base_article: String(row[10] || '').trim() || null,
        extra_article1: String(row[11] || '').trim() || null,
        extra_article2: String(row[12] || '').trim() || null,
        extra_article3: String(row[13] || '').trim() || null,
        extra_article4: String(row[14] || '').trim() || null
      });
    }
  }

  // 4. Парсинг листа "Цвета корпуса"
  const colorsSheetName = workbook.SheetNames.find(n => n.includes('Цвет') || n.includes('цвет'));
  const colorsSheet = colorsSheetName ? workbook.Sheets[colorsSheetName] : null;
  if (colorsSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(colorsSheet, { header: 1 });
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      if (!row || !row[0] || !row[1]) continue;

      bodyColors.push({
        series: String(row[0] || '').trim(),
        name: String(row[1] || '').trim(),
        description: String(row[2] || '').trim() || null,
        image_small: String(row[3] || '').trim() || null,
        image_large: String(row[4] || '').trim() || null
      });
    }
  }

  // Добавляем цвета из товаров, которых нет в листе "Цвета корпуса"
  const existingColors = new Set(bodyColors.map(c => `${c.series}:${c.name}`));
  for (const p of products) {
    if (p.series && p.body_color) {
      const key = `${p.series}:${p.body_color}`;
      if (!existingColors.has(key)) {
        bodyColors.push({
          series: p.series,
          name: p.body_color
        });
        existingColors.add(key);
      }
    }
  }

  // 5. Парсинг листа "Описание услуг"
  const servicesSheet = workbook.Sheets['Описание услуг'];
  if (servicesSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(servicesSheet, { header: 1 });
    // Пропускаем заголовок
    // Ожидаемые колонки: Название, Описание, Иконка, Порядок
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      if (!row || !row[0]) continue; // Пропускаем пустые строки

      services.push({
        name: String(row[0] || '').trim(),
        description: String(row[1] || '').trim() || null,
        icon: String(row[2] || '').trim() || null,
        sort_order: parseInt(row[3]) || i
      });
    }
  }

  return { products, series, fillings, bodyColors, services };
}

// Получить статус каталога
export async function GET() {
  try {
    const admin = await getCurrentAdmin();
    if (!admin) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const pool = getPool();

    // Проверяем существование таблиц
    const tablesExist = await pool.query(`
      SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'catalog_series'
      ) as exists
    `);

    if (!tablesExist.rows[0]?.exists) {
      return NextResponse.json({
        stats: { series: 0, products: 0, variants: 0 },
        imports: [],
        needsMigration: true,
        message: 'Таблицы каталога не созданы. Примените миграцию 006_catalog.sql'
      });
    }

    // Статистика
    const [series, products, variants, lastImport] = await Promise.all([
      pool.query('SELECT COUNT(*) as count FROM catalog_series'),
      pool.query('SELECT COUNT(*) as count FROM catalog_products'),
      pool.query('SELECT COUNT(*) as count FROM catalog_variants'),
      pool.query('SELECT * FROM catalog_import_history ORDER BY imported_at DESC LIMIT 5').catch(() => ({ rows: [] }))
    ]);

    return NextResponse.json({
      stats: {
        series: parseInt(series.rows[0]?.count || '0'),
        products: parseInt(products.rows[0]?.count || '0'),
        variants: parseInt(variants.rows[0]?.count || '0')
      },
      imports: lastImport.rows
    });
  } catch (error) {
    console.error('Get catalog status error:', error);
    return NextResponse.json({
      stats: { series: 0, products: 0, variants: 0 },
      imports: [],
      error: 'Ошибка получения статуса. Возможно, таблицы каталога не созданы.'
    });
  }
}
