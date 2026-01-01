import { NextRequest } from 'next/server';
import { getCurrentAdmin } from '@/lib/auth';
import { getPool } from '@/lib/db';
import * as XLSX from 'xlsx';

const MAX_FILE_SIZE = 10 * 1024 * 1024;

// Утилита для создания slug
function createSlug(text: string): string {
  const translitMap: { [key: string]: string } = {
    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
    'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
    'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
    'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
    'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
    ' ': '-', '/': '-', '(': '', ')': '', '"': '', "'": '', ',': ''
  };

  return text
    .toLowerCase()
    .split('')
    .map(char => translitMap[char] ?? char)
    .join('')
    .replace(/[^a-z0-9-]/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

export async function POST(request: NextRequest) {
  const encoder = new TextEncoder();

  const stream = new TransformStream();
  const writer = stream.writable.getWriter();

  const send = async (event: string, data: object) => {
    await writer.write(encoder.encode(`event: ${event}\ndata: ${JSON.stringify(data)}\n\n`));
  };

  // Start processing in background
  (async () => {
    try {
      const admin = await getCurrentAdmin();
      if (!admin) {
        await send('error', { message: 'Unauthorized' });
        await writer.close();
        return;
      }

      const formData = await request.formData();
      const file = formData.get('file') as File | null;

      if (!file) {
        await send('error', { message: 'Файл не указан' });
        await writer.close();
        return;
      }

      if (file.size > MAX_FILE_SIZE) {
        await send('error', { message: 'Файл слишком большой (макс. 10 МБ)' });
        await writer.close();
        return;
      }

      await send('progress', { current: 0, total: 100, stage: 'Чтение файла', item: file.name });

      const buffer = Buffer.from(await file.arrayBuffer());
      const workbook = XLSX.read(buffer, { type: 'buffer' });

      await send('progress', { current: 5, total: 100, stage: 'Парсинг данных', item: 'Анализ структуры' });

      // Парсим данные
      const data = parseExcelData(workbook);

      await send('progress', { current: 10, total: 100, stage: 'Импорт', item: `${data.products.length} вариантов найдено, ${data.skippedRows.length} пропущено при парсинге` });

      const result = await importCatalogOptimized(data, admin.id, async (progress) => {
        await send('progress', progress);
      }, data.skippedRows);

      await send('complete', {
        success: result.success,
        message: result.success ? 'Импорт завершён успешно' : 'Импорт завершён с ошибками',
        stats: {
          series: result.seriesCount,
          bodyColors: result.bodyColorsCount,
          fillings: result.fillingsCount,
          products: result.productsCount,
          variants: result.variantsCount,
          skipped: result.skippedRows.length,
          skippedFillings: result.skippedFillings.length
        },
        errors: result.errors.slice(0, 50),
        skippedRows: result.skippedRows,
        skippedFillings: result.skippedFillings
      });
    } catch (error) {
      console.error('Import error:', error);
      await send('error', { message: error instanceof Error ? error.message : 'Ошибка импорта' });
    } finally {
      await writer.close();
    }
  })();

  return new Response(stream.readable, {
    headers: {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      'Connection': 'keep-alive',
    },
  });
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
  // Пропущенные строки с причинами
  const skippedRows: { row: number; article: string; cardName: string; reason: string }[] = [];

  // 1. Парсинг листа "Карточки товаров"
  const productsSheet = workbook.Sheets['Карточки товаров'];
  if (productsSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(productsSheet, { header: 1 });
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      const rowNum = i + 1; // Номер строки в Excel (с 1, учитывая заголовок)

      // Проверяем обязательные поля
      if (!row) {
        skippedRows.push({ row: rowNum, article: '', cardName: '', reason: 'Пустая строка' });
        continue;
      }

      const cardName = String(row[0] || '').trim();
      const article = String(row[2] || '').trim();
      const seriesName = String(row[8] || '').trim();

      if (!cardName) {
        skippedRows.push({ row: rowNum, article, cardName: '', reason: 'Отсутствует наименование карточки (колонка A)' });
        continue;
      }

      if (!article) {
        skippedRows.push({ row: rowNum, article: '', cardName, reason: 'Отсутствует артикул (колонка C)' });
        continue;
      }

      if (!seriesName) {
        skippedRows.push({ row: rowNum, article, cardName, reason: 'Отсутствует серия (колонка I)' });
        continue;
      }

      products.push({
        rowNum,
        card_name: cardName,
        full_name: String(row[1] || '').trim(),
        article: article,
        body_color: String(row[3] || '').trim(),
        profile_color: String(row[4] || '').trim(),
        height: parseFloat(row[5]) || 0,
        width: parseFloat(row[6]) || 0,
        depth: parseFloat(row[7]) || 0,
        series: seriesName,
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
        image_interior: String(row[19] || '').trim() || null
      });
    }
  }

  // 2. Сначала собираем уникальные серии ТОЛЬКО из колонки I (Серия) листа товаров
  const uniqueSeriesNames = new Set(products.map(p => p.series).filter(Boolean));

  // 3. Парсинг листа "Описание серий" - только для дополнительной информации
  const seriesSheetName = workbook.SheetNames.find(n => n.includes('Описание серий'));
  const seriesSheet = seriesSheetName ? workbook.Sheets[seriesSheetName] : null;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const seriesInfoMap: { [name: string]: any } = {};

  if (seriesSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(seriesSheet, { header: 1 });
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      if (!row || !row[0]) continue;

      const seriesName = String(row[0] || '').trim();
      // Сохраняем информацию только если эта серия есть в товарах
      if (uniqueSeriesNames.has(seriesName)) {
        seriesInfoMap[seriesName] = {
          description: String(row[1] || '').trim() || null,
          image1: String(row[2] || '').trim() || null,
          image2: String(row[3] || '').trim() || null,
          image3: String(row[4] || '').trim() || null,
          image4: String(row[5] || '').trim() || null,
          video1: String(row[6] || '').trim() || null,
          video2: String(row[7] || '').trim() || null,
          gallery_folder: String(row[8] || '').trim() || null
        };
      }
    }
  }

  // 4. Создаём массив серий только на основе уникальных значений из товаров
  for (const seriesName of uniqueSeriesNames) {
    const info = seriesInfoMap[seriesName] || {};
    series.push({
      name: seriesName,
      description: info.description || null,
      image1: info.image1 || null,
      image2: info.image2 || null,
      image3: info.image3 || null,
      image4: info.image4 || null,
      video1: info.video1 || null,
      video2: info.video2 || null,
      gallery_folder: info.gallery_folder || null
    });
  }

  // 3. Парсинг листа "Наполнение корпуса"
  const fillingsSheet = workbook.Sheets['Наполнение корпуса'];
  if (fillingsSheet) {
    const rows = XLSX.utils.sheet_to_json<string[]>(fillingsSheet, { header: 1 });
    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      const rowNum = i + 1; // Номер строки в Excel
      if (!row || !row[0]) continue;

      fillings.push({
        rowNum,
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

  // Добавляем цвета из товаров
  const existingColors = new Set(bodyColors.map(c => `${c.series}:${c.name}`));
  for (const p of products) {
    if (p.series && p.body_color) {
      const key = `${p.series}:${p.body_color}`;
      if (!existingColors.has(key)) {
        bodyColors.push({ series: p.series, name: p.body_color });
        existingColors.add(key);
      }
    }
  }

  return { products, series, fillings, bodyColors, skippedRows };
}

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

interface SkippedRow {
  row: number;
  article: string;
  cardName: string;
  reason: string;
}

interface SkippedFilling {
  row: number;
  series: string;
  doorCount: number;
  dimensions: string;
  shortName: string;
  reason: string;
}

async function importCatalogOptimized(
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  data: any,
  adminId: number,
  onProgress: (progress: { current: number; total: number; stage: string; item?: string }) => Promise<void>,
  skippedRows: SkippedRow[]
) {
  const pool = getPool();
  const errors: string[] = [];
  const skippedFillings: SkippedFilling[] = [];
  let seriesCount = 0;
  let bodyColorsCount = 0;
  let fillingsCount = 0;
  let productsCount = 0;
  let variantsCount = 0;

  // Глобальный трекер дубликатов артикулов
  const globalSeenArticles = new Set<string>();
  // Трекер дубликатов наполнений (ключ: series_id:door_count:height:width:depth)
  const seenFillings = new Set<string>();

  // Создаём запись в истории импорта
  const historyResult = await pool.query(
    `INSERT INTO catalog_import_history (status, imported_by) VALUES ('in_progress', $1) RETURNING id`,
    [adminId]
  );
  const importId = historyResult.rows[0].id;

  try {
    await onProgress({ current: 10, total: 100, stage: 'Очистка старых данных', item: 'Удаление неактуальных серий' });

    // 0. Очистка старых серий (удаляем все и вставляем только актуальные из товаров)
    // Сначала удаляем варианты, затем товары, затем серии
    await pool.query('DELETE FROM catalog_variants');
    await pool.query('DELETE FROM catalog_products');
    await pool.query('DELETE FROM catalog_body_colors');
    await pool.query('DELETE FROM catalog_fillings');
    await pool.query('DELETE FROM catalog_series');

    await onProgress({ current: 12, total: 100, stage: 'Импорт серий', item: `${data.series.length} серий` });

    // 1. Импорт серий (их мало, можно по одной)
    const seriesMap: { [name: string]: number } = {};
    for (const s of data.series) {
      try {
        const slug = createSlug(s.name);
        const result = await pool.query(
          `INSERT INTO catalog_series (name, slug, description, image1, image2, image3, image4, video1, video2, gallery_folder)
           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
           ON CONFLICT (name) DO UPDATE SET
             description = COALESCE(EXCLUDED.description, catalog_series.description),
             image1 = COALESCE(EXCLUDED.image1, catalog_series.image1),
             image2 = COALESCE(EXCLUDED.image2, catalog_series.image2),
             image3 = COALESCE(EXCLUDED.image3, catalog_series.image3),
             image4 = COALESCE(EXCLUDED.image4, catalog_series.image4),
             video1 = COALESCE(EXCLUDED.video1, catalog_series.video1),
             video2 = COALESCE(EXCLUDED.video2, catalog_series.video2),
             gallery_folder = COALESCE(EXCLUDED.gallery_folder, catalog_series.gallery_folder),
             updated_at = CURRENT_TIMESTAMP
           RETURNING id`,
          [s.name, slug, s.description || null, s.image1 || null, s.image2 || null,
           s.image3 || null, s.image4 || null, s.video1 || null, s.video2 || null, s.gallery_folder || null]
        );
        seriesMap[s.name] = result.rows[0].id;
        seriesCount++;
      } catch (e) {
        errors.push(`Ошибка серии "${s.name}": ${e instanceof Error ? e.message : String(e)}`);
      }
    }

    // Загружаем все серии для маппинга
    const allSeries = await pool.query('SELECT id, name FROM catalog_series');
    for (const row of allSeries.rows) {
      if (!seriesMap[row.name]) {
        seriesMap[row.name] = row.id;
      }
    }

    await onProgress({ current: 18, total: 100, stage: 'Импорт цветов', item: `${data.bodyColors.length} цветов` });

    // 2. Импорт цветов корпуса
    const bodyColorMap: { [key: string]: number } = {};
    for (const bc of data.bodyColors) {
      const seriesId = seriesMap[bc.series];
      if (!seriesId) continue;
      try {
        const slug = createSlug(bc.name);
        const result = await pool.query(
          `INSERT INTO catalog_body_colors (series_id, name, slug, description, image_small, image_large)
           VALUES ($1, $2, $3, $4, $5, $6)
           ON CONFLICT (series_id, name) DO UPDATE SET
             description = COALESCE(EXCLUDED.description, catalog_body_colors.description),
             image_small = COALESCE(EXCLUDED.image_small, catalog_body_colors.image_small),
             image_large = COALESCE(EXCLUDED.image_large, catalog_body_colors.image_large)
           RETURNING id`,
          [seriesId, bc.name, slug, bc.description || null,
           bc.image_small || PLACEHOLDER_IMAGE, bc.image_large || PLACEHOLDER_IMAGE]
        );
        bodyColorMap[`${bc.series}:${bc.name}`] = result.rows[0].id;
        bodyColorsCount++;
      } catch (e) {
        errors.push(`Ошибка цвета "${bc.name}": ${e instanceof Error ? e.message : String(e)}`);
      }
    }

    // Загружаем все цвета для маппинга
    const allBodyColors = await pool.query('SELECT bc.id, bc.name, s.name as series_name FROM catalog_body_colors bc JOIN catalog_series s ON bc.series_id = s.id');
    for (const row of allBodyColors.rows) {
      const key = `${row.series_name}:${row.name}`;
      if (!bodyColorMap[key]) {
        bodyColorMap[key] = row.id;
      }
    }

    await onProgress({ current: 22, total: 100, stage: 'Импорт наполнений', item: `${data.fillings.length} наполнений` });

    // 3. Импорт наполнений
    for (const f of data.fillings) {
      const seriesId = seriesMap[f.series];
      if (!seriesId) {
        skippedFillings.push({
          row: f.rowNum,
          series: f.series,
          doorCount: f.door_count,
          dimensions: `${f.width}×${f.height}×${f.depth}`,
          shortName: f.short_name || '',
          reason: `Серия "${f.series}" не найдена`
        });
        continue;
      }

      // Проверяем на дубликат (включая short_name для разных типов наполнения)
      const shortNameKey = f.short_name || '';
      const fillingKey = `${seriesId}:${f.door_count}:${f.height}:${f.width}:${f.depth}:${shortNameKey}`;
      if (seenFillings.has(fillingKey)) {
        skippedFillings.push({
          row: f.rowNum,
          series: f.series,
          doorCount: f.door_count,
          dimensions: `${f.width}×${f.height}×${f.depth}`,
          shortName: f.short_name || '',
          reason: `Дубликат (серия "${f.series}", ${f.door_count} дв., ${f.width}×${f.height}×${f.depth}, "${shortNameKey}")`
        });
        continue;
      }
      seenFillings.add(fillingKey);

      try {
        // Используем INSERT без ON CONFLICT, так как таблица очищается перед импортом
        // и мы уже проверили дубликаты выше
        await pool.query(
          `INSERT INTO catalog_fillings (series_id, door_count, height, width, depth, short_name, description,
             image_plain, image_dimensions, image_filled, base_article, extra_article1, extra_article2, extra_article3, extra_article4)
           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)`,
          [seriesId, f.door_count, f.height, f.width, f.depth, f.short_name || null, f.description || null,
           f.image_plain || PLACEHOLDER_IMAGE, f.image_dimensions || PLACEHOLDER_IMAGE,
           f.image_filled || PLACEHOLDER_IMAGE, f.base_article || null,
           f.extra_article1 || null, f.extra_article2 || null, f.extra_article3 || null, f.extra_article4 || null]
        );
        fillingsCount++;
      } catch (e) {
        errors.push(`Ошибка наполнения: ${e instanceof Error ? e.message : String(e)}`);
      }
    }

    // 4. Загрузка справочников
    const doorTypeMap: { [name: string]: number } = {};
    const doorTypes = await pool.query('SELECT id, name FROM catalog_door_types');
    for (const row of doorTypes.rows) {
      doorTypeMap[row.name] = row.id;
    }

    const profileColorMap: { [name: string]: number } = {};
    const profileColors = await pool.query('SELECT id, name FROM catalog_profile_colors');
    for (const row of profileColors.rows) {
      profileColorMap[row.name] = row.id;
    }

    await onProgress({ current: 25, total: 100, stage: 'Импорт товаров', item: `Подготовка ${data.products.length} записей` });

    // 5. Сначала создаём уникальные товары (карточки)
    const productMap: { [name: string]: number } = {};
    const uniqueProducts = new Map<string, { name: string; series: string; doorType: string; doorCount: number }>();

    for (const p of data.products) {
      if (!uniqueProducts.has(p.card_name)) {
        uniqueProducts.set(p.card_name, {
          name: p.card_name,
          series: p.series,
          doorType: p.door_type,
          doorCount: p.door_count
        });
      }
    }

    await onProgress({ current: 30, total: 100, stage: 'Создание товаров', item: `${uniqueProducts.size} карточек` });

    // Создаём товары пачками
    const productEntries = Array.from(uniqueProducts.entries());
    const productBatchSize = 100;

    for (let i = 0; i < productEntries.length; i += productBatchSize) {
      const batch = productEntries.slice(i, i + productBatchSize);

      for (const [cardName, prod] of batch) {
        const seriesId = seriesMap[prod.series];
        if (!seriesId) {
          console.log(`Skipping product "${cardName}" - series "${prod.series}" not found in seriesMap`);
          errors.push(`Товар "${cardName}" пропущен - серия "${prod.series}" не найдена`);
          continue;
        }

        try {
          const slug = createSlug(cardName);
          const doorTypeId = doorTypeMap[prod.doorType] || null;

          const result = await pool.query(
            `INSERT INTO catalog_products (name, slug, series_id, door_type_id, door_count)
             VALUES ($1, $2, $3, $4, $5)
             ON CONFLICT (slug) DO UPDATE SET
               series_id = EXCLUDED.series_id,
               door_type_id = EXCLUDED.door_type_id,
               door_count = EXCLUDED.door_count,
               updated_at = CURRENT_TIMESTAMP
             RETURNING id`,
            [cardName, slug, seriesId, doorTypeId, prod.doorCount || null]
          );
          productMap[cardName] = result.rows[0].id;
          productsCount++;
        } catch (e) {
          errors.push(`Ошибка товара "${cardName}": ${e instanceof Error ? e.message : String(e)}`);
        }
      }

      const progress = 30 + Math.round((i / productEntries.length) * 15);
      await onProgress({ current: progress, total: 100, stage: 'Создание товаров', item: `${Math.min(i + productBatchSize, productEntries.length)} / ${productEntries.length}` });
    }

    await onProgress({ current: 45, total: 100, stage: 'Импорт вариантов', item: `${data.products.length} артикулов` });

    // 6. Теперь вставляем варианты пачками через COPY или multi-value INSERT
    const BATCH_SIZE = 500;
    const totalVariants = data.products.length;

    for (let i = 0; i < totalVariants; i += BATCH_SIZE) {
      const batch = data.products.slice(i, i + BATCH_SIZE);

      // Собираем значения для batch insert
      const values: string[] = [];
      const params: (string | number | null)[] = [];
      let paramIndex = 1;
      // Дедупликация артикулов в пределах batch (PostgreSQL не может обновить одну строку дважды)
      const seenArticles = new Set<string>();

      for (const p of batch) {
        const productId = productMap[p.card_name];
        if (!productId) {
          skippedRows.push({
            row: p.rowNum,
            article: p.article,
            cardName: p.card_name,
            reason: `Товар "${p.card_name}" не найден (серия не существует)`
          });
          continue;
        }

        // Пропускаем дубликаты артикулов глобально
        if (globalSeenArticles.has(p.article)) {
          skippedRows.push({
            row: p.rowNum,
            article: p.article,
            cardName: p.card_name,
            reason: `Дубликат артикула "${p.article}"`
          });
          continue;
        }
        globalSeenArticles.add(p.article);
        seenArticles.add(p.article);

        const bodyColorId = bodyColorMap[`${p.series}:${p.body_color}`] || null;
        const profileColorId = profileColorMap[p.profile_color] || null;

        values.push(`($${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++}, $${paramIndex++})`);
        params.push(
          productId, p.article, p.full_name, bodyColorId, profileColorId,
          p.height, p.width, p.depth,
          p.door_material1 || null, p.door_material2 || null, p.door_material3 || null,
          p.door_material4 || null, p.door_material5 || null, p.door_material6 || null,
          p.image_white || null, p.image_interior || null
        );
      }

      if (values.length > 0) {
        try {
          const result = await pool.query(
            `INSERT INTO catalog_variants (product_id, article, full_name, body_color_id, profile_color_id,
               height, width, depth, door_material1, door_material2, door_material3, door_material4,
               door_material5, door_material6, image_white, image_interior)
             VALUES ${values.join(', ')}
             ON CONFLICT (article) DO UPDATE SET
               full_name = EXCLUDED.full_name,
               body_color_id = EXCLUDED.body_color_id,
               profile_color_id = EXCLUDED.profile_color_id,
               height = EXCLUDED.height,
               width = EXCLUDED.width,
               depth = EXCLUDED.depth,
               door_material1 = EXCLUDED.door_material1,
               door_material2 = EXCLUDED.door_material2,
               door_material3 = EXCLUDED.door_material3,
               door_material4 = EXCLUDED.door_material4,
               door_material5 = EXCLUDED.door_material5,
               door_material6 = EXCLUDED.door_material6,
               image_white = COALESCE(EXCLUDED.image_white, catalog_variants.image_white),
               image_interior = COALESCE(EXCLUDED.image_interior, catalog_variants.image_interior)`,
            params
          );
          variantsCount += result.rowCount || 0;
        } catch (e) {
          errors.push(`Ошибка batch вариантов: ${e instanceof Error ? e.message : String(e)}`);
        }
      }

      const progress = 45 + Math.round(((i + BATCH_SIZE) / totalVariants) * 50);
      await onProgress({
        current: Math.min(progress, 95),
        total: 100,
        stage: 'Импорт вариантов',
        item: `${Math.min(i + BATCH_SIZE, totalVariants).toLocaleString()} / ${totalVariants.toLocaleString()}`
      });
    }

    await onProgress({ current: 98, total: 100, stage: 'Завершение', item: 'Сохранение результатов' });

    // Обновляем историю импорта
    await pool.query(
      `UPDATE catalog_import_history
       SET status = 'completed', products_count = $1, variants_count = $2, error_message = $3
       WHERE id = $4`,
      [productsCount, variantsCount, errors.length > 0 ? errors.slice(0, 100).join('\n') : null, importId]
    );

    return {
      success: errors.length === 0,
      seriesCount,
      bodyColorsCount,
      fillingsCount,
      productsCount,
      variantsCount,
      errors,
      skippedRows,
      skippedFillings
    };
  } catch (e) {
    await pool.query(
      `UPDATE catalog_import_history SET status = 'failed', error_message = $1 WHERE id = $2`,
      [e instanceof Error ? e.message : String(e), importId]
    );
    throw e;
  }
}
