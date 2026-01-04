import { getPool, queryWithRetry } from './db';

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

// Интерфейсы
export interface CatalogSeries {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image1?: string;
  image2?: string;
  image3?: string;
  image4?: string;
  video1?: string;
  video2?: string;
  gallery_folder?: string;
  sort_order: number;
  is_active: boolean;
}

export interface CatalogProduct {
  id: number;
  name: string;
  slug: string;
  series_id: number;
  series_name?: string;
  door_type_id?: number;
  door_type_name?: string;
  door_count?: number;
  is_active: boolean;
  sort_order: number;
  variants_count?: number;
  min_price?: number;
  default_image?: string;
}

export interface CatalogVariant {
  id: number;
  product_id: number;
  article: string;
  full_name: string;
  body_color_id?: number;
  body_color_name?: string;
  profile_color_id?: number;
  profile_color_name?: string;
  height: number;
  width: number;
  depth: number;
  door_material1?: string;
  door_material2?: string;
  door_material3?: string;
  door_material4?: string;
  door_material5?: string;
  door_material6?: string;
  image_white?: string;
  image_interior?: string;
  is_active: boolean;
}

export interface CatalogBodyColor {
  id: number;
  series_id: number;
  name: string;
  slug: string;
  description?: string;
  image_small?: string;
  image_large?: string;
}

export interface CatalogProfileColor {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image_small?: string;
  image_large?: string;
}

export interface CatalogFilling {
  id: number;
  series_id: number;
  door_count: number;
  height: number;
  width: number;
  depth: number;
  short_name?: string;
  description?: string;
  image_plain?: string;
  image_dimensions?: string;
  image_filled?: string;
}

export interface CatalogService {
  id: number;
  name: string;
  description?: string;
  icon?: string;
  sort_order: number;
  is_active: boolean;
}

export interface ImportResult {
  success: boolean;
  seriesCount: number;
  bodyColorsCount: number;
  fillingsCount: number;
  productsCount: number;
  variantsCount: number;
  servicesCount: number;
  errors: string[];
}

// Placeholder изображение
const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

// Получить все серии
export async function getSeries(): Promise<CatalogSeries[]> {
  const pool = getPool();
  const result = await pool.query(
    'SELECT * FROM catalog_series WHERE is_active = true ORDER BY sort_order, name'
  );
  return result.rows;
}

// Получить серию по slug
export async function getSeriesBySlug(slug: string): Promise<CatalogSeries | null> {
  const pool = getPool();
  const result = await pool.query(
    'SELECT * FROM catalog_series WHERE slug = $1 AND is_active = true',
    [slug]
  );
  return result.rows[0] || null;
}

// Получить все активные услуги
export async function getServices(): Promise<CatalogService[]> {
  const pool = getPool();
  try {
    const result = await pool.query(
      'SELECT * FROM catalog_services WHERE is_active = true ORDER BY sort_order, name'
    );
    return result.rows;
  } catch {
    // Таблица может ещё не существовать
    return [];
  }
}

// Предопределённые диапазоны для подсчёта
const widthRangesDef = [
  { key: '0-1090', min: 0, max: 1090 },
  { key: '1100-1390', min: 1100, max: 1390 },
  { key: '1400-1610', min: 1400, max: 1610 },
  { key: '1620-2000', min: 1620, max: 2000 },
  { key: '2010-2390', min: 2010, max: 2390 },
  { key: '2400-9990', min: 2400, max: 9990 },
];

const depthRangesDef = [
  { key: '0-450', min: 0, max: 450 },
  { key: '450-530', min: 450, max: 530 },
  { key: '530-9990', min: 530, max: 9990 },
];

// Получить опции для фильтров с подсчётом товаров (каскадные счётчики)
export async function getFilterOptions(currentFilters?: {
  doorTypes?: string[];
  series?: string[];
  widthMin?: number;
  widthMax?: number;
  heights?: number[];
  depthMin?: number;
  depthMax?: number;
  priceMin?: number;
  priceMax?: number;
}): Promise<{
  doorTypes: { id: number; name: string; slug: string; count: number }[];
  series: { id: number; name: string; slug: string; count: number }[];
  widthRange: { min: number; max: number };
  widthRangeCounts: { key: string; count: number }[];
  heights: { value: number; count: number }[];
  depths: { value: number; count: number }[];
  depthRangeCounts: { key: string; count: number }[];
  wardrobeTypeCounts: { key: string; count: number }[];
  priceRange: { min: number; max: number };
}> {
  const pool = getPool();

  // Каскадные фильтры: каждый уровень зависит от верхних
  // Иерархия: doorTypes -> series -> price/width/height/depth

  // 1. Типы шкафов - всегда полные счётчики (верхний уровень)
  const doorTypesQuery = `
    SELECT dt.id, dt.name, dt.slug,
           (SELECT COUNT(DISTINCT p.id) FROM catalog_products p
            WHERE p.door_type_id = dt.id AND p.is_active = true) as count
    FROM catalog_door_types dt
    ORDER BY dt.sort_order
  `;

  // 2. Серии - фильтруются по выбранным типам шкафов
  let seriesQuery: string;
  let seriesParams: string[] = [];
  if (currentFilters?.doorTypes?.length) {
    seriesQuery = `
      SELECT s.id, s.name, s.slug,
             (SELECT COUNT(DISTINCT p.id) FROM catalog_products p
              JOIN catalog_door_types dt ON p.door_type_id = dt.id
              WHERE p.series_id = s.id AND p.is_active = true
              AND dt.slug = ANY($1::text[])) as count
      FROM catalog_series s
      WHERE s.is_active = true
      ORDER BY s.sort_order, s.name
    `;
    seriesParams = currentFilters.doorTypes;
  } else {
    seriesQuery = `
      SELECT s.id, s.name, s.slug,
             (SELECT COUNT(DISTINCT p.id) FROM catalog_products p
              WHERE p.series_id = s.id AND p.is_active = true) as count
      FROM catalog_series s
      WHERE s.is_active = true
      ORDER BY s.sort_order, s.name
    `;
  }

  // Базовые условия для размеров (зависят от doorTypes + series)
  const buildDimensionConditions = () => {
    const conditions: string[] = ['v.is_active = true', 'p.is_active = true'];
    const params: (string | string[])[] = [];
    let paramIndex = 1;

    if (currentFilters?.doorTypes?.length) {
      conditions.push(`dt.slug = ANY($${paramIndex++}::text[])`);
      params.push(currentFilters.doorTypes);
    }
    if (currentFilters?.series?.length) {
      conditions.push(`s.slug = ANY($${paramIndex++}::text[])`);
      params.push(currentFilters.series);
    }

    return { conditions, params, paramIndex };
  };

  const { conditions: dimConditions, params: dimParams } = buildDimensionConditions();
  const dimJoins = `
    FROM catalog_variants v
    JOIN catalog_products p ON v.product_id = p.id
    LEFT JOIN catalog_series s ON p.series_id = s.id
    LEFT JOIN catalog_door_types dt ON p.door_type_id = dt.id
  `;
  const dimWhere = dimConditions.length > 0 ? `WHERE ${dimConditions.join(' AND ')}` : '';

  // Размеры
  const dimensionsQuery = `
    SELECT
      MIN(v.width) as min_width, MAX(v.width) as max_width
    ${dimJoins}
    ${dimWhere}
  `;

  const [doorTypesResult, seriesResult, dimensionsResult] = await Promise.all([
    pool.query(doorTypesQuery),
    seriesParams.length > 0
      ? pool.query(seriesQuery, [seriesParams])
      : pool.query(seriesQuery),
    dimParams.length > 0
      ? pool.query(dimensionsQuery, dimParams)
      : pool.query(dimensionsQuery)
  ]);

  const dimensions = dimensionsResult.rows[0] || { min_width: 80, max_width: 300 };

  // Подсчёт для высот с каскадными фильтрами
  const heightCountsQuery = `
    SELECT v.height as value, COUNT(DISTINCT p.id) as count
    ${dimJoins}
    ${dimWhere}
    GROUP BY v.height
    ORDER BY v.height
  `;
  const heightCountsResult = dimParams.length > 0
    ? await pool.query(heightCountsQuery, dimParams)
    : await pool.query(heightCountsQuery);
  const heightCounts = heightCountsResult.rows.map(r => ({ value: r.value, count: parseInt(r.count) }));

  // Подсчёт для глубин с каскадными фильтрами
  const depthCountsQuery = `
    SELECT v.depth as value, COUNT(DISTINCT p.id) as count
    ${dimJoins}
    ${dimWhere}
    GROUP BY v.depth
    ORDER BY v.depth
  `;
  const depthCountsResult = dimParams.length > 0
    ? await pool.query(depthCountsQuery, dimParams)
    : await pool.query(depthCountsQuery);
  const depthCounts = depthCountsResult.rows.map(r => ({ value: r.value, count: parseInt(r.count) }));

  // Подсчёт для диапазонов ширины
  const widthRangeCounts: { key: string; count: number }[] = [];
  for (const range of widthRangesDef) {
    const widthRangeQuery = `
      SELECT COUNT(DISTINCT p.id) as count
      ${dimJoins}
      ${dimWhere}${dimConditions.length > 0 ? ' AND ' : ' WHERE '}v.width >= $${dimParams.length + 1} AND v.width <= $${dimParams.length + 2}
    `;
    const result = await pool.query(widthRangeQuery, [...dimParams, range.min, range.max]);
    widthRangeCounts.push({ key: range.key, count: parseInt(result.rows[0]?.count || '0') });
  }

  // Подсчёт для диапазонов глубины
  const depthRangeCounts: { key: string; count: number }[] = [];
  for (const range of depthRangesDef) {
    const depthRangeQuery = `
      SELECT COUNT(DISTINCT p.id) as count
      ${dimJoins}
      ${dimWhere}${dimConditions.length > 0 ? ' AND ' : ' WHERE '}v.depth >= $${dimParams.length + 1} AND v.depth <= $${dimParams.length + 2}
    `;
    const result = await pool.query(depthRangeQuery, [...dimParams, range.min, range.max]);
    depthRangeCounts.push({ key: range.key, count: parseInt(result.rows[0]?.count || '0') });
  }

  // Подсчёт для специальных типов шкафов (wardrobeTypes)
  const wardrobeTypeCounts: { key: string; count: number }[] = [];

  // two-door: door_count = 2
  const twoDoorResult = await pool.query(`
    SELECT COUNT(DISTINCT p.id) as count
    FROM catalog_products p
    WHERE p.is_active = true AND p.door_count = 2
  `);
  wardrobeTypeCounts.push({ key: 'two-door', count: parseInt(twoDoorResult.rows[0]?.count || '0') });

  // three-door: door_count = 3
  const threeDoorResult = await pool.query(`
    SELECT COUNT(DISTINCT p.id) as count
    FROM catalog_products p
    WHERE p.is_active = true AND p.door_count = 3
  `);
  wardrobeTypeCounts.push({ key: 'three-door', count: parseInt(threeDoorResult.rows[0]?.count || '0') });

  // bedroom: depth > 570mm (57cm)
  const bedroomResult = await pool.query(`
    SELECT COUNT(DISTINCT p.id) as count
    FROM catalog_products p
    JOIN catalog_variants v ON v.product_id = p.id AND v.is_active = true
    WHERE p.is_active = true AND v.depth > 570
  `);
  wardrobeTypeCounts.push({ key: 'bedroom', count: parseInt(bedroomResult.rows[0]?.count || '0') });

  // hallway / mirror: door_material contains "зеркало"
  const mirrorResult = await pool.query(`
    SELECT COUNT(DISTINCT p.id) as count
    FROM catalog_products p
    JOIN catalog_variants v ON v.product_id = p.id AND v.is_active = true
    WHERE p.is_active = true AND (
      LOWER(COALESCE(v.door_material1, '')) LIKE '%зеркало%' OR
      LOWER(COALESCE(v.door_material2, '')) LIKE '%зеркало%' OR
      LOWER(COALESCE(v.door_material3, '')) LIKE '%зеркало%' OR
      LOWER(COALESCE(v.door_material4, '')) LIKE '%зеркало%' OR
      LOWER(COALESCE(v.door_material5, '')) LIKE '%зеркало%' OR
      LOWER(COALESCE(v.door_material6, '')) LIKE '%зеркало%'
    )
  `);
  wardrobeTypeCounts.push({ key: 'hallway', count: parseInt(mirrorResult.rows[0]?.count || '0') });
  wardrobeTypeCounts.push({ key: 'mirror', count: parseInt(mirrorResult.rows[0]?.count || '0') });

  // living: height > 2300mm (230cm)
  const livingResult = await pool.query(`
    SELECT COUNT(DISTINCT p.id) as count
    FROM catalog_products p
    JOIN catalog_variants v ON v.product_id = p.id AND v.is_active = true
    WHERE p.is_active = true AND v.height > 2300
  `);
  wardrobeTypeCounts.push({ key: 'living', count: parseInt(livingResult.rows[0]?.count || '0') });

  // corner: name contains "угловой"
  const cornerResult = await pool.query(`
    SELECT COUNT(DISTINCT p.id) as count
    FROM catalog_products p
    WHERE p.is_active = true AND LOWER(p.name) LIKE '%угловой%'
  `);
  wardrobeTypeCounts.push({ key: 'corner', count: parseInt(cornerResult.rows[0]?.count || '0') });

  return {
    doorTypes: doorTypesResult.rows.map(r => ({ ...r, count: parseInt(r.count) })),
    series: seriesResult.rows.map(r => ({ ...r, count: parseInt(r.count) })),
    widthRange: {
      min: dimensions.min_width || 80,
      max: dimensions.max_width || 300
    },
    widthRangeCounts,
    heights: heightCounts,
    depths: depthCounts,
    depthRangeCounts,
    wardrobeTypeCounts,
    priceRange: { min: 2000, max: 170000 }
  };
}

// Получить товары (с фильтрами)
export async function getProducts(options: {
  seriesId?: number;
  seriesSlug?: string;
  seriesSlugs?: string[];
  doorTypeId?: number;
  doorTypeSlug?: string;
  doorTypeSlugs?: string[];
  wardrobeTypes?: string[];
  widthMin?: number;
  widthMax?: number;
  heights?: number[];
  depths?: number[];
  priceMin?: number;
  priceMax?: number;
  limit?: number;
  offset?: number;
}): Promise<{ products: CatalogProduct[]; total: number }> {
  const pool = getPool();
  const conditions: string[] = ['p.is_active = true'];
  const params: (string | number | string[] | number[])[] = [];
  let paramIndex = 1;

  // Специальные типы шкафов, требующие variant JOIN
  const wardrobeTypesNeedingVariant = ['bedroom', 'hallway', 'living', 'mirror'];
  const hasWardrobeVariantFilter = options.wardrobeTypes?.some(wt => wardrobeTypesNeedingVariant.includes(wt));

  // Условия, требующие JOIN с variants
  const needsVariantJoin = options.widthMin || options.widthMax ||
    (options.heights && options.heights.length > 0) ||
    (options.depths && options.depths.length > 0) ||
    hasWardrobeVariantFilter;

  if (options.seriesId) {
    conditions.push(`p.series_id = $${paramIndex++}`);
    params.push(options.seriesId);
  }

  if (options.seriesSlug) {
    conditions.push(`s.slug = $${paramIndex++}`);
    params.push(options.seriesSlug);
  }

  // Мультивыбор серий
  if (options.seriesSlugs && options.seriesSlugs.length > 0) {
    conditions.push(`s.slug = ANY($${paramIndex++}::text[])`);
    params.push(options.seriesSlugs);
  }

  if (options.doorTypeId) {
    conditions.push(`p.door_type_id = $${paramIndex++}`);
    params.push(options.doorTypeId);
  }

  if (options.doorTypeSlug) {
    conditions.push(`dt.slug = $${paramIndex++}`);
    params.push(options.doorTypeSlug);
  }

  // Мультивыбор типов шкафов
  if (options.doorTypeSlugs && options.doorTypeSlugs.length > 0) {
    conditions.push(`dt.slug = ANY($${paramIndex++}::text[])`);
    params.push(options.doorTypeSlugs);
  }

  // Специальные типы шкафов (wardrobeTypes)
  if (options.wardrobeTypes && options.wardrobeTypes.length > 0) {
    // Фильтр по названию: угловые
    if (options.wardrobeTypes.includes('corner')) {
      conditions.push(`LOWER(p.name) LIKE '%угловой%'`);
    }

    // Фильтр по количеству дверей
    if (options.wardrobeTypes.includes('two-door')) {
      conditions.push(`p.door_count = 2`);
    }
    if (options.wardrobeTypes.includes('three-door')) {
      conditions.push(`p.door_count = 3`);
    }
  }

  // Фильтры по размерам (через подзапрос к variants)
  if (needsVariantJoin) {
    const variantConditions: string[] = [];

    if (options.widthMin) {
      variantConditions.push(`v.width >= $${paramIndex++}`);
      params.push(options.widthMin);
    }
    if (options.widthMax) {
      variantConditions.push(`v.width <= $${paramIndex++}`);
      params.push(options.widthMax);
    }
    if (options.heights && options.heights.length > 0) {
      variantConditions.push(`v.height = ANY($${paramIndex++}::int[])`);
      params.push(options.heights);
    }
    if (options.depths && options.depths.length > 0) {
      variantConditions.push(`v.depth = ANY($${paramIndex++}::int[])`);
      params.push(options.depths);
    }

    // Специальные wardrobeTypes, требующие variant JOIN
    if (options.wardrobeTypes && options.wardrobeTypes.length > 0) {
      // В спальню: глубина > 57 см (570 мм)
      if (options.wardrobeTypes.includes('bedroom')) {
        variantConditions.push(`v.depth > 570`);
      }

      // В гостиную: высота > 230 см (2300 мм)
      if (options.wardrobeTypes.includes('living')) {
        variantConditions.push(`v.height > 2300`);
      }

      // С зеркалом / В прихожую: материал двери содержит "зеркало"
      if (options.wardrobeTypes.includes('mirror') || options.wardrobeTypes.includes('hallway')) {
        variantConditions.push(`(
          LOWER(COALESCE(v.door_material1, '')) LIKE '%зеркало%' OR
          LOWER(COALESCE(v.door_material2, '')) LIKE '%зеркало%' OR
          LOWER(COALESCE(v.door_material3, '')) LIKE '%зеркало%' OR
          LOWER(COALESCE(v.door_material4, '')) LIKE '%зеркало%' OR
          LOWER(COALESCE(v.door_material5, '')) LIKE '%зеркало%' OR
          LOWER(COALESCE(v.door_material6, '')) LIKE '%зеркало%'
        )`);
      }
    }

    if (variantConditions.length > 0) {
      conditions.push(`EXISTS (
        SELECT 1 FROM catalog_variants v
        WHERE v.product_id = p.id AND v.is_active = true AND ${variantConditions.join(' AND ')}
      )`);
    }
  }

  const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(' AND ')}` : '';

  // Получаем общее количество
  const countResult = await pool.query(
    `SELECT COUNT(DISTINCT p.id) as total
     FROM catalog_products p
     LEFT JOIN catalog_series s ON p.series_id = s.id
     LEFT JOIN catalog_door_types dt ON p.door_type_id = dt.id
     ${whereClause}`,
    params
  );
  const total = parseInt(countResult.rows[0].total);

  // Получаем товары с доп. информацией
  const limit = options.limit || 20;
  const offset = options.offset || 0;

  const result = await pool.query(
    `SELECT p.*,
            s.name as series_name,
            dt.name as door_type_name,
            (SELECT COUNT(*) FROM catalog_variants v WHERE v.product_id = p.id AND v.is_active = true) as variants_count,
            (SELECT v.image_white FROM catalog_variants v WHERE v.product_id = p.id AND v.image_white IS NOT NULL AND v.is_active = true LIMIT 1) as default_image
     FROM catalog_products p
     LEFT JOIN catalog_series s ON p.series_id = s.id
     LEFT JOIN catalog_door_types dt ON p.door_type_id = dt.id
     ${whereClause}
     ORDER BY p.sort_order, p.name
     LIMIT $${paramIndex++} OFFSET $${paramIndex++}`,
    [...params, limit, offset]
  );

  return { products: result.rows, total };
}

// Получить товар по slug с вариантами
export async function getProductBySlug(slug: string): Promise<{
  product: CatalogProduct;
  variants: CatalogVariant[];
  bodyColors: CatalogBodyColor[];
  profileColors: { id: number; name: string }[];
  sizes: { height: number; width: number; depth: number }[];
  filling?: CatalogFilling;
  fillings?: CatalogFilling[];
  series?: CatalogSeries;
} | null> {
  const pool = getPool();

  // Получаем товар
  const productResult = await pool.query(
    `SELECT p.*,
            s.name as series_name,
            dt.name as door_type_name
     FROM catalog_products p
     LEFT JOIN catalog_series s ON p.series_id = s.id
     LEFT JOIN catalog_door_types dt ON p.door_type_id = dt.id
     WHERE p.slug = $1 AND p.is_active = true`,
    [slug]
  );

  if (productResult.rows.length === 0) return null;

  const product = productResult.rows[0];

  // Получаем варианты
  const variantsResult = await pool.query(
    `SELECT v.*,
            bc.name as body_color_name,
            pc.name as profile_color_name
     FROM catalog_variants v
     LEFT JOIN catalog_body_colors bc ON v.body_color_id = bc.id
     LEFT JOIN catalog_profile_colors pc ON v.profile_color_id = pc.id
     WHERE v.product_id = $1 AND v.is_active = true
     ORDER BY v.height, v.width, v.depth, bc.name, pc.name`,
    [product.id]
  );

  // Получаем уникальные цвета корпуса для серии
  const bodyColorsResult = await pool.query(
    `SELECT DISTINCT bc.*
     FROM catalog_body_colors bc
     WHERE bc.series_id = $1 AND bc.is_active = true
     ORDER BY bc.sort_order, bc.name`,
    [product.series_id]
  );

  // Получаем цвета профилей
  // Примечание: после применения миграции 008 можно добавить: slug, description, image_small, image_large
  const profileColorsResult = await pool.query(
    `SELECT id, name FROM catalog_profile_colors WHERE is_active = true ORDER BY sort_order`
  );

  // Получаем уникальные размеры для этого товара
  const sizesResult = await pool.query(
    `SELECT DISTINCT height, width, depth
     FROM catalog_variants
     WHERE product_id = $1 AND is_active = true
     ORDER BY height, width, depth`,
    [product.id]
  );

  // Получаем серию
  const seriesResult = await pool.query(
    `SELECT * FROM catalog_series WHERE id = $1`,
    [product.series_id]
  );

  // Получаем наполнение (для первого варианта) с допуском ±15мм
  // Ищем только по серии и размерам, без door_count
  let filling = null;
  let fillings: CatalogFilling[] = [];
  const tolerance = 15;
  if (variantsResult.rows.length > 0) {
    const firstVariant = variantsResult.rows[0];

    const fillingResult = await pool.query(
      `SELECT * FROM catalog_fillings
       WHERE series_id = $1
         AND ABS(height - $2) <= $5 AND ABS(width - $3) <= $5 AND ABS(depth - $4) <= $5
       ORDER BY ABS(height - $2) + ABS(width - $3) + ABS(depth - $4)
       LIMIT 1`,
      [product.series_id, firstVariant.height, firstVariant.width, firstVariant.depth, tolerance]
    );
    filling = fillingResult.rows[0] || null;

    const fillingsResult = await pool.query(
      `SELECT * FROM catalog_fillings
       WHERE series_id = $1
         AND ABS(height - $2) <= $5 AND ABS(width - $3) <= $5 AND ABS(depth - $4) <= $5
       ORDER BY door_count, short_name NULLS LAST`,
      [product.series_id, firstVariant.height, firstVariant.width, firstVariant.depth, tolerance]
    );
    fillings = fillingsResult.rows;
  }

  return {
    product,
    variants: variantsResult.rows,
    bodyColors: bodyColorsResult.rows,
    profileColors: profileColorsResult.rows,
    sizes: sizesResult.rows,
    filling,
    fillings,
    series: seriesResult.rows[0]
  };
}

// Получить вариант по параметрам
export async function getVariantByParams(
  productId: number,
  height: number,
  width: number,
  depth: number,
  bodyColorId?: number,
  profileColorId?: number
): Promise<CatalogVariant | null> {
  const conditions = [
    'v.product_id = $1',
    'v.height = $2',
    'v.width = $3',
    'v.depth = $4',
    'v.is_active = true'
  ];
  const params: (number | undefined)[] = [productId, height, width, depth];
  let paramIndex = 5;

  if (bodyColorId) {
    conditions.push(`body_color_id = $${paramIndex++}`);
    params.push(bodyColorId);
  }

  if (profileColorId) {
    conditions.push(`profile_color_id = $${paramIndex++}`);
    params.push(profileColorId);
  }

  // Используем queryWithRetry для устойчивости к временным ошибкам соединения
  const result = await queryWithRetry<CatalogVariant>(
    `SELECT v.*,
            bc.name as body_color_name,
            pc.name as profile_color_name
     FROM catalog_variants v
     LEFT JOIN catalog_body_colors bc ON v.body_color_id = bc.id
     LEFT JOIN catalog_profile_colors pc ON v.profile_color_id = pc.id
     WHERE ${conditions.join(' AND ')}
     LIMIT 1`,
    params.filter(p => p !== undefined)
  );

  return result.rows[0] || null;
}

// Получить наполнение по параметрам (точное совпадение размеров и кол-ва дверей)
export async function getFilling(
  seriesId: number,
  doorCount: number | null,
  height: number,
  width: number,
  depth: number
): Promise<CatalogFilling | null> {
  // Ищем по серии, размерам и количеству дверей (точное совпадение)
  const conditions = ['series_id = $1', 'height = $2', 'width = $3', 'depth = $4'];
  const params: (number | null)[] = [seriesId, height, width, depth];

  if (doorCount !== null) {
    conditions.push('door_count = $5');
    params.push(doorCount);
  }

  const query = `SELECT * FROM catalog_fillings
     WHERE ${conditions.join(' AND ')}
     LIMIT 1`;

  const result = await queryWithRetry<CatalogFilling>(query, params);
  return result.rows[0] || null;
}

// Получить все варианты наполнения для серии, размеров и кол-ва дверей (точное совпадение)
export async function getFillings(
  seriesId: number,
  doorCount: number | null,
  height: number,
  width: number,
  depth: number
): Promise<CatalogFilling[]> {
  // Ищем по серии, размерам и количеству дверей (точное совпадение)
  const conditions = ['series_id = $1', 'height = $2', 'width = $3', 'depth = $4'];
  const params: (number | null)[] = [seriesId, height, width, depth];

  if (doorCount !== null) {
    conditions.push('door_count = $5');
    params.push(doorCount);
  }

  const query = `SELECT * FROM catalog_fillings
     WHERE ${conditions.join(' AND ')}
     ORDER BY short_name NULLS LAST`;

  const result = await queryWithRetry<CatalogFilling>(query, params);
  return result.rows;
}

// Импорт каталога из данных (для API)
export async function importCatalog(data: {
  products: Array<{
    card_name: string;
    full_name: string;
    article: string;
    body_color: string;
    profile_color: string;
    height: number;
    width: number;
    depth: number;
    series: string;
    group?: string;
    door_type: string;
    door_count: number;
    door_material1?: string;
    door_material2?: string;
    door_material3?: string;
    door_material4?: string;
    door_material5?: string;
    door_material6?: string;
    image_white?: string;
    image_interior?: string;
  }>;
  series: Array<{
    name: string;
    description?: string;
    image1?: string;
    image2?: string;
    image3?: string;
    image4?: string;
    video1?: string;
    video2?: string;
    gallery_folder?: string;
  }>;
  fillings: Array<{
    series: string;
    door_count: number;
    height: number;
    width: number;
    depth: number;
    short_name?: string;
    description?: string;
    image_plain?: string;
    image_dimensions?: string;
    image_filled?: string;
    base_article?: string;
    extra_article1?: string;
    extra_article2?: string;
    extra_article3?: string;
    extra_article4?: string;
  }>;
  bodyColors: Array<{
    series: string;
    name: string;
    description?: string;
    image_small?: string;
    image_large?: string;
  }>;
  services?: Array<{
    name: string;
    description?: string;
    icon?: string;
    sort_order?: number;
  }>;
}, adminId: number): Promise<ImportResult> {
  const pool = getPool();
  const errors: string[] = [];
  let seriesCount = 0;
  let bodyColorsCount = 0;
  let fillingsCount = 0;
  let productsCount = 0;
  let variantsCount = 0;
  let servicesCount = 0;

  // Создаём запись в истории импорта
  const historyResult = await pool.query(
    `INSERT INTO catalog_import_history (status, imported_by) VALUES ('in_progress', $1) RETURNING id`,
    [adminId]
  );
  const importId = historyResult.rows[0].id;

  try {
    // 1. Импорт серий
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

    // 2. Импорт цветов корпуса
    const bodyColorMap: { [key: string]: number } = {};
    for (const bc of data.bodyColors) {
      const seriesId = seriesMap[bc.series];
      if (!seriesId) {
        errors.push(`Серия "${bc.series}" не найдена для цвета "${bc.name}"`);
        continue;
      }
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

    // 3. Импорт наполнений
    for (const f of data.fillings) {
      const seriesId = seriesMap[f.series];
      if (!seriesId) continue;
      try {
        await pool.query(
          `INSERT INTO catalog_fillings (series_id, door_count, height, width, depth, short_name, description,
             image_plain, image_dimensions, image_filled, base_article, extra_article1, extra_article2, extra_article3, extra_article4)
           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)
           ON CONFLICT (series_id, door_count, height, width, depth) DO UPDATE SET
             short_name = COALESCE(EXCLUDED.short_name, catalog_fillings.short_name),
             description = COALESCE(EXCLUDED.description, catalog_fillings.description),
             image_plain = COALESCE(EXCLUDED.image_plain, catalog_fillings.image_plain),
             image_dimensions = COALESCE(EXCLUDED.image_dimensions, catalog_fillings.image_dimensions),
             image_filled = COALESCE(EXCLUDED.image_filled, catalog_fillings.image_filled)`,
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

    // 4. Импорт типов открывания
    const doorTypeMap: { [name: string]: number } = {};
    const doorTypes = await pool.query('SELECT id, name FROM catalog_door_types');
    for (const row of doorTypes.rows) {
      doorTypeMap[row.name] = row.id;
    }

    // 5. Импорт цветов профилей
    const profileColorMap: { [name: string]: number } = {};
    const profileColors = await pool.query('SELECT id, name FROM catalog_profile_colors');
    for (const row of profileColors.rows) {
      profileColorMap[row.name] = row.id;
    }

    // 6. Импорт товаров и вариантов
    const productMap: { [name: string]: number } = {};

    for (const p of data.products) {
      const seriesId = seriesMap[p.series];
      if (!seriesId) {
        errors.push(`Серия "${p.series}" не найдена для товара "${p.card_name}"`);
        continue;
      }

      // Создаём или обновляем товар
      if (!productMap[p.card_name]) {
        try {
          const slug = createSlug(p.card_name);
          const doorTypeId = doorTypeMap[p.door_type] || null;

          const result = await pool.query(
            `INSERT INTO catalog_products (name, slug, series_id, door_type_id, door_count)
             VALUES ($1, $2, $3, $4, $5)
             ON CONFLICT (slug) DO UPDATE SET
               series_id = EXCLUDED.series_id,
               door_type_id = EXCLUDED.door_type_id,
               door_count = EXCLUDED.door_count,
               updated_at = CURRENT_TIMESTAMP
             RETURNING id`,
            [p.card_name, slug, seriesId, doorTypeId, p.door_count || null]
          );
          productMap[p.card_name] = result.rows[0].id;
          productsCount++;
        } catch (e) {
          errors.push(`Ошибка товара "${p.card_name}": ${e instanceof Error ? e.message : String(e)}`);
          continue;
        }
      }

      const productId = productMap[p.card_name];
      const bodyColorId = bodyColorMap[`${p.series}:${p.body_color}`] || null;
      const profileColorId = profileColorMap[p.profile_color] || null;

      // Создаём вариант
      try {
        await pool.query(
          `INSERT INTO catalog_variants (product_id, article, full_name, body_color_id, profile_color_id,
             height, width, depth, door_material1, door_material2, door_material3, door_material4,
             door_material5, door_material6, image_white, image_interior)
           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16)
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
          [productId, p.article, p.full_name, bodyColorId, profileColorId,
           p.height, p.width, p.depth,
           p.door_material1 || null, p.door_material2 || null, p.door_material3 || null,
           p.door_material4 || null, p.door_material5 || null, p.door_material6 || null,
           p.image_white || null, p.image_interior || null]
        );
        variantsCount++;
      } catch (e) {
        errors.push(`Ошибка варианта "${p.article}": ${e instanceof Error ? e.message : String(e)}`);
      }
    }

    // 7. Импорт услуг
    if (data.services && data.services.length > 0) {
      for (const service of data.services) {
        try {
          await pool.query(
            `INSERT INTO catalog_services (name, description, icon, sort_order)
             VALUES ($1, $2, $3, $4)
             ON CONFLICT DO NOTHING`,
            [service.name, service.description || null, service.icon || null, service.sort_order || 0]
          );
          servicesCount++;
        } catch (e) {
          errors.push(`Ошибка услуги "${service.name}": ${e instanceof Error ? e.message : String(e)}`);
        }
      }
    }

    // Обновляем историю импорта
    await pool.query(
      `UPDATE catalog_import_history
       SET status = 'completed', products_count = $1, variants_count = $2, error_message = $3
       WHERE id = $4`,
      [productsCount, variantsCount, errors.length > 0 ? errors.join('\n') : null, importId]
    );

    return {
      success: errors.length === 0,
      seriesCount,
      bodyColorsCount,
      fillingsCount,
      productsCount,
      variantsCount,
      servicesCount,
      errors
    };
  } catch (e) {
    await pool.query(
      `UPDATE catalog_import_history SET status = 'failed', error_message = $1 WHERE id = $2`,
      [e instanceof Error ? e.message : String(e), importId]
    );
    throw e;
  }
}

// Получить историю импортов
export async function getImportHistory(limit = 10): Promise<Array<{
  id: number;
  imported_at: Date;
  source_url?: string;
  products_count: number;
  variants_count: number;
  status: string;
  error_message?: string;
  imported_by: number;
}>> {
  const pool = getPool();
  const result = await pool.query(
    `SELECT * FROM catalog_import_history ORDER BY imported_at DESC LIMIT $1`,
    [limit]
  );
  return result.rows;
}

// Статистика каталога
export async function getCatalogStats(): Promise<{
  seriesCount: number;
  productsCount: number;
  variantsCount: number;
  bodyColorsCount: number;
  lastImport?: Date;
}> {
  const pool = getPool();

  const [series, products, variants, bodyColors, lastImport] = await Promise.all([
    pool.query('SELECT COUNT(*) as count FROM catalog_series WHERE is_active = true'),
    pool.query('SELECT COUNT(*) as count FROM catalog_products WHERE is_active = true'),
    pool.query('SELECT COUNT(*) as count FROM catalog_variants WHERE is_active = true'),
    pool.query('SELECT COUNT(*) as count FROM catalog_body_colors WHERE is_active = true'),
    pool.query('SELECT imported_at FROM catalog_import_history WHERE status = $1 ORDER BY imported_at DESC LIMIT 1', ['completed'])
  ]);

  return {
    seriesCount: parseInt(series.rows[0].count),
    productsCount: parseInt(products.rows[0].count),
    variantsCount: parseInt(variants.rows[0].count),
    bodyColorsCount: parseInt(bodyColors.rows[0].count),
    lastImport: lastImport.rows[0]?.imported_at
  };
}
