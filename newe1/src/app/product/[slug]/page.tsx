'use client';

import { useEffect, useState, useCallback, useMemo } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';

interface CatalogProduct {
  id: number;
  name: string;
  slug: string;
  series_id: number;
  series_name?: string;
  door_type_name?: string;
  door_count?: number;
}

interface CatalogVariant {
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
  image_white?: string;
  image_interior?: string;
}

interface CatalogBodyColor {
  id: number;
  name: string;
  slug: string;
  description?: string;
  image_small?: string;
  image_large?: string;
}

interface CatalogFilling {
  short_name?: string;
  description?: string;
  image_plain?: string;
  image_dimensions?: string;
  image_filled?: string;
}

interface CatalogSeries {
  id: number;
  name: string;
  description?: string;
  video1?: string;
  video2?: string;
}

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

export default function ProductPage() {
  const params = useParams();
  const slug = (params?.slug as string) || '';

  const [loading, setLoading] = useState(true);
  const [product, setProduct] = useState<CatalogProduct | null>(null);
  const [variants, setVariants] = useState<CatalogVariant[]>([]);
  const [bodyColors, setBodyColors] = useState<CatalogBodyColor[]>([]);
  const [profileColors, setProfileColors] = useState<{ id: number; name: string }[]>([]);
  const [filling, setFilling] = useState<CatalogFilling | null>(null);
  const [series, setSeries] = useState<CatalogSeries | null>(null);

  // Выбранные параметры размеров (отдельно)
  const [selectedHeight, setSelectedHeight] = useState<number | null>(null);
  const [selectedWidth, setSelectedWidth] = useState<number | null>(null);
  const [selectedDepth, setSelectedDepth] = useState<number | null>(null);

  const [selectedBodyColor, setSelectedBodyColor] = useState<CatalogBodyColor | null>(null);
  const [selectedProfileColor, setSelectedProfileColor] = useState<{ id: number; name: string } | null>(null);
  const [currentVariant, setCurrentVariant] = useState<CatalogVariant | null>(null);

  // Галерея
  const [mainImage, setMainImage] = useState<string>(PLACEHOLDER_IMAGE);
  const [showInterior, setShowInterior] = useState(false);

  // Модальные окна
  const [showFillingModal, setShowFillingModal] = useState(false);
  const [showBodyColorModal, setShowBodyColorModal] = useState(false);
  const [showBodyColorInfo, setShowBodyColorInfo] = useState(false);
  const [showAdditionalConfig, setShowAdditionalConfig] = useState(false);

  // Сборка
  const [includeAssembly, setIncludeAssembly] = useState(false);

  // Цены (пока заглушки)
  const basePrice = 35990;
  const oldPrice = 72000;
  const assemblyPrice = Math.round(basePrice * 0.12);

  // Все уникальные цвета профиля из вариантов (для проверки наличия)
  const variantProfileColors = useMemo(() => {
    const colorMap = new Map<number, { id: number; name: string }>();
    for (const v of variants) {
      if (v.profile_color_id && v.profile_color_name) {
        colorMap.set(v.profile_color_id, { id: v.profile_color_id, name: v.profile_color_name });
      }
    }
    return Array.from(colorMap.values());
  }, [variants]);

  // Получить уникальные значения для каждого параметра размера
  const availableHeights = useMemo(() => {
    const heights = [...new Set(variants.map(v => v.height))].sort((a, b) => a - b);
    return heights;
  }, [variants]);

  const availableWidths = useMemo(() => {
    // Фильтруем по выбранной высоте если она задана
    let filtered = variants;
    if (selectedHeight !== null) {
      filtered = variants.filter(v => v.height === selectedHeight);
    }
    const widths = [...new Set(filtered.map(v => v.width))].sort((a, b) => a - b);
    return widths;
  }, [variants, selectedHeight]);

  const availableDepths = useMemo(() => {
    // Фильтруем по выбранной высоте и ширине
    let filtered = variants;
    if (selectedHeight !== null) {
      filtered = filtered.filter(v => v.height === selectedHeight);
    }
    if (selectedWidth !== null) {
      filtered = filtered.filter(v => v.width === selectedWidth);
    }
    const depths = [...new Set(filtered.map(v => v.depth))].sort((a, b) => a - b);
    return depths;
  }, [variants, selectedHeight, selectedWidth]);

  const fetchProduct = useCallback(async () => {
    setLoading(true);
    try {
      const response = await fetch(`/api/catalog/product/${slug}`);
      const data = await response.json();

      if (data.success) {
        setProduct(data.product);
        setVariants(data.variants);
        setBodyColors(data.bodyColors);
        setProfileColors(data.profileColors);
        setFilling(data.filling);
        setSeries(data.series);

        // Установить начальные значения из первого варианта
        if (data.variants.length > 0) {
          const firstVariant = data.variants[0];
          setSelectedHeight(firstVariant.height);
          setSelectedWidth(firstVariant.width);
          setSelectedDepth(firstVariant.depth);

          // Устанавливаем цвета из первого варианта
          if (firstVariant.body_color_id) {
            const bc = data.bodyColors.find((c: CatalogBodyColor) => c.id === firstVariant.body_color_id);
            if (bc) setSelectedBodyColor(bc);
          }
          if (firstVariant.profile_color_id) {
            const pc = data.profileColors.find((c: { id: number; name: string }) => c.id === firstVariant.profile_color_id);
            if (pc) setSelectedProfileColor(pc);
          }
        }
      }
    } catch (error) {
      console.error('Error fetching product:', error);
    } finally {
      setLoading(false);
    }
  }, [slug]);

  useEffect(() => {
    if (slug) {
      fetchProduct();
    }
  }, [slug, fetchProduct]);

  // При изменении высоты - проверяем доступность ширины и глубины
  useEffect(() => {
    if (selectedHeight === null || variants.length === 0) return;

    // Проверяем, есть ли текущая ширина для новой высоты
    const widthsForHeight = [...new Set(
      variants.filter(v => v.height === selectedHeight).map(v => v.width)
    )];

    if (selectedWidth !== null && !widthsForHeight.includes(selectedWidth)) {
      setSelectedWidth(widthsForHeight[0] || null);
    }
  }, [selectedHeight, variants, selectedWidth]);

  // При изменении ширины - проверяем доступность глубины
  useEffect(() => {
    if (selectedHeight === null || selectedWidth === null || variants.length === 0) return;

    const depthsForSize = [...new Set(
      variants
        .filter(v => v.height === selectedHeight && v.width === selectedWidth)
        .map(v => v.depth)
    )];

    if (selectedDepth !== null && !depthsForSize.includes(selectedDepth)) {
      setSelectedDepth(depthsForSize[0] || null);
    }
  }, [selectedHeight, selectedWidth, variants, selectedDepth]);

  // Найти подходящий вариант при изменении параметров
  useEffect(() => {
    if (selectedHeight === null || selectedWidth === null || selectedDepth === null ||
        !selectedBodyColor || !selectedProfileColor || variants.length === 0) {
      return;
    }

    // Ищем вариант с точным совпадением
    let variant = variants.find(v =>
      v.height === selectedHeight &&
      v.width === selectedWidth &&
      v.depth === selectedDepth &&
      v.body_color_id === selectedBodyColor.id &&
      v.profile_color_id === selectedProfileColor.id
    );

    // Если не нашли точный, ищем с тем же размером и цветом корпуса
    if (!variant) {
      variant = variants.find(v =>
        v.height === selectedHeight &&
        v.width === selectedWidth &&
        v.depth === selectedDepth &&
        v.body_color_id === selectedBodyColor.id
      );
    }

    // Если не нашли, ищем с тем же размером
    if (!variant) {
      variant = variants.find(v =>
        v.height === selectedHeight &&
        v.width === selectedWidth &&
        v.depth === selectedDepth
      );
    }

    // Если ничего не нашли, берём первый
    if (!variant) {
      variant = variants[0];
    }

    setCurrentVariant(variant);
    setMainImage(variant.image_white || PLACEHOLDER_IMAGE);
    setShowInterior(false);
  }, [selectedHeight, selectedWidth, selectedDepth, selectedBodyColor, selectedProfileColor, variants]);

  // Загрузить наполнение при смене размера
  useEffect(() => {
    if (!product || selectedHeight === null || selectedWidth === null || selectedDepth === null) return;

    const loadFilling = async () => {
      try {
        const response = await fetch(`/api/catalog/product/${slug}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            productId: product.id,
            height: selectedHeight,
            width: selectedWidth,
            depth: selectedDepth,
            seriesId: product.series_id,
            doorCount: product.door_count
          })
        });
        const data = await response.json();
        if (data.filling) {
          setFilling(data.filling);
        }
      } catch (error) {
        console.error('Error loading filling:', error);
      }
    };

    loadFilling();
  }, [product, selectedHeight, selectedWidth, selectedDepth, slug]);

  // Получить доступные цвета профилей для текущего размера и цвета корпуса
  const getAvailableProfileColors = useMemo(() => {
    // Фильтруем варианты по выбранным параметрам
    let filtered = variants;

    if (selectedHeight !== null) {
      filtered = filtered.filter(v => v.height === selectedHeight);
    }
    if (selectedWidth !== null) {
      filtered = filtered.filter(v => v.width === selectedWidth);
    }
    if (selectedDepth !== null) {
      filtered = filtered.filter(v => v.depth === selectedDepth);
    }
    if (selectedBodyColor) {
      filtered = filtered.filter(v => v.body_color_id === selectedBodyColor.id);
    }

    // Собираем уникальные цвета профиля из отфильтрованных вариантов
    const colorMap = new Map<number, { id: number; name: string }>();
    for (const v of filtered) {
      if (v.profile_color_id && v.profile_color_name) {
        colorMap.set(v.profile_color_id, { id: v.profile_color_id, name: v.profile_color_name });
      }
    }
    return Array.from(colorMap.values());
  }, [variants, selectedHeight, selectedWidth, selectedDepth, selectedBodyColor]);

  // Получить доступные цвета корпуса для текущего размера
  const getAvailableBodyColors = useMemo(() => {
    // Фильтруем варианты по выбранным размерам
    let filtered = variants;

    if (selectedHeight !== null) {
      filtered = filtered.filter(v => v.height === selectedHeight);
    }
    if (selectedWidth !== null) {
      filtered = filtered.filter(v => v.width === selectedWidth);
    }
    if (selectedDepth !== null) {
      filtered = filtered.filter(v => v.depth === selectedDepth);
    }

    // Собираем уникальные цвета корпуса из отфильтрованных вариантов
    const availableIds = new Set(filtered.map(v => v.body_color_id).filter(Boolean));
    return bodyColors.filter(c => availableIds.has(c.id));
  }, [variants, selectedHeight, selectedWidth, selectedDepth, bodyColors]);

  // Автовыбор цвета корпуса если текущий недоступен
  useEffect(() => {
    if (getAvailableBodyColors.length > 0 && selectedBodyColor) {
      const isAvailable = getAvailableBodyColors.some(c => c.id === selectedBodyColor.id);
      if (!isAvailable) {
        setSelectedBodyColor(getAvailableBodyColors[0]);
      }
    } else if (getAvailableBodyColors.length > 0 && !selectedBodyColor) {
      setSelectedBodyColor(getAvailableBodyColors[0]);
    }
  }, [getAvailableBodyColors, selectedBodyColor]);

  // Автовыбор цвета профиля если текущий недоступен
  useEffect(() => {
    if (getAvailableProfileColors.length > 0 && selectedProfileColor) {
      const isAvailable = getAvailableProfileColors.some(c => c.id === selectedProfileColor.id);
      if (!isAvailable) {
        setSelectedProfileColor(getAvailableProfileColors[0]);
      }
    } else if (getAvailableProfileColors.length > 0 && !selectedProfileColor) {
      setSelectedProfileColor(getAvailableProfileColors[0]);
    }
  }, [getAvailableProfileColors, selectedProfileColor]);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="w-10 h-10 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Товар не найден</h1>
          <Link href="/catalog" className="text-[#62bb46] hover:underline">
            Вернуться в каталог
          </Link>
        </div>
      </div>
    );
  }

  const availableBodyColors = getAvailableBodyColors;
  const availableProfileColors = getAvailableProfileColors;

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Breadcrumbs */}
      <div className="bg-white border-b">
        <div className="max-w-[1348px] mx-auto px-4 py-3">
          <nav className="flex items-center gap-2 text-sm text-gray-500">
            <Link href="/" className="hover:text-[#62bb46]">Главная</Link>
            <span>/</span>
            <Link href="/catalog" className="hover:text-[#62bb46]">Каталог</Link>
            <span>/</span>
            <span className="text-gray-900">{product.name}</span>
          </nav>
        </div>
      </div>

      <div className="max-w-[1348px] mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Галерея */}
          <div className="space-y-4">
            {/* Главное изображение */}
            <div className="bg-white rounded-xl shadow-sm overflow-hidden aspect-square relative">
              <Image
                src={showInterior && currentVariant?.image_interior ? currentVariant.image_interior : mainImage}
                alt={product.name}
                fill
                className="object-contain p-8"
                onError={(e) => {
                  const target = e.target as HTMLImageElement;
                  target.src = PLACEHOLDER_IMAGE;
                }}
              />
            </div>

            {/* Переключатель белый фон / интерьер */}
            {currentVariant?.image_interior && (
              <div className="flex gap-2">
                <button
                  onClick={() => setShowInterior(false)}
                  className={`flex-1 py-3 rounded-lg font-medium transition-colors ${
                    !showInterior
                      ? 'bg-[#62bb46] text-white'
                      : 'bg-white text-gray-700 border border-gray-200 hover:border-[#62bb46]'
                  }`}
                >
                  На белом фоне
                </button>
                <button
                  onClick={() => setShowInterior(true)}
                  className={`flex-1 py-3 rounded-lg font-medium transition-colors ${
                    showInterior
                      ? 'bg-[#62bb46] text-white'
                      : 'bg-white text-gray-700 border border-gray-200 hover:border-[#62bb46]'
                  }`}
                >
                  В интерьере
                </button>
              </div>
            )}
          </div>

          {/* Информация о товаре */}
          <div className="space-y-4">
            {/* Заголовок - фиксированная высота 3 строки */}
            <div>
              <div className="text-sm text-gray-500 mb-1">
                {product.series_name}
              </div>
              <h1 className="text-xl md:text-2xl font-bold text-gray-900 min-h-[4.5rem] line-clamp-3">
                {currentVariant?.full_name || product.name}
              </h1>
            </div>

            {/* Цена */}
            <div className="bg-white rounded-xl shadow-sm p-5">
              <div className="flex items-baseline gap-3 mb-1">
                <span className="text-3xl font-bold text-gray-900">
                  {(basePrice + (includeAssembly ? assemblyPrice : 0)).toLocaleString('ru-RU')} ₽
                </span>
                <span className="text-lg text-gray-400 line-through">
                  {oldPrice.toLocaleString('ru-RU')} ₽
                </span>
              </div>
              <div className="text-sm text-gray-500 mb-4">Цена в базовой комплектации</div>

              {/* Сборка */}
              <label className="flex items-center gap-3 cursor-pointer p-3 rounded-lg border border-gray-200 hover:border-[#62bb46] transition-colors">
                <input
                  type="checkbox"
                  checked={includeAssembly}
                  onChange={(e) => setIncludeAssembly(e.target.checked)}
                  className="w-5 h-5 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
                />
                <div className="flex-1">
                  <div className="font-medium text-gray-900">Добавить сборку</div>
                  <div className="text-sm text-gray-500">Профессиональная сборка изделия</div>
                </div>
                <div className="text-right">
                  <div className="font-bold text-gray-900">+{assemblyPrice.toLocaleString('ru-RU')} ₽</div>
                  <div className="text-xs text-gray-400">12% от стоимости</div>
                </div>
              </label>
            </div>

            {/* Выбрать размер и цвет */}
            <div className="bg-white rounded-xl shadow-sm p-5">
              <h3 className="text-base font-medium text-gray-900 mb-4">Выбрать размер и цвет</h3>

              {/* Размеры - выпадающие списки или информация */}
              <div className="grid grid-cols-3 gap-4 mb-5">
                {/* Ширина */}
                <div>
                  <label className="text-sm text-gray-500 mb-1.5 block">Ширина</label>
                  {availableWidths.length > 1 ? (
                    <select
                      value={selectedWidth || ''}
                      onChange={(e) => setSelectedWidth(Number(e.target.value))}
                      className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                    >
                      {availableWidths.map((width) => (
                        <option key={width} value={width}>{width} мм</option>
                      ))}
                    </select>
                  ) : (
                    <div className="px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg border border-gray-100">
                      {selectedWidth} мм
                    </div>
                  )}
                </div>

                {/* Высота */}
                <div>
                  <label className="text-sm text-gray-500 mb-1.5 block">Высота</label>
                  {availableHeights.length > 1 ? (
                    <select
                      value={selectedHeight || ''}
                      onChange={(e) => setSelectedHeight(Number(e.target.value))}
                      className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                    >
                      {availableHeights.map((height) => (
                        <option key={height} value={height}>{height} мм</option>
                      ))}
                    </select>
                  ) : (
                    <div className="px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg border border-gray-100">
                      {selectedHeight} мм
                    </div>
                  )}
                </div>

                {/* Глубина */}
                <div>
                  <label className="text-sm text-gray-500 mb-1.5 block">Глубина</label>
                  {availableDepths.length > 1 ? (
                    <select
                      value={selectedDepth || ''}
                      onChange={(e) => setSelectedDepth(Number(e.target.value))}
                      className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                    >
                      {availableDepths.map((depth) => (
                        <option key={depth} value={depth}>{depth} мм</option>
                      ))}
                    </select>
                  ) : (
                    <div className="px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg border border-gray-100">
                      {selectedDepth} мм
                    </div>
                  )}
                </div>
              </div>

              {/* Цвета и доп. комплектация */}
              <div className="grid grid-cols-2 gap-4">
                {/* Цвет корпуса */}
                <div>
                  <label className="text-sm text-gray-500 mb-1.5 block">Цвет корпуса</label>
                  <div className="flex items-center justify-between">
                    <button
                      onClick={() => setShowBodyColorModal(true)}
                      className="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:border-[#62bb46] transition-colors"
                    >
                      Выбрать
                    </button>
                    <div className="flex items-center gap-2">
                      {selectedBodyColor?.image_small && (
                        <div className="relative w-6 h-6 rounded overflow-hidden border border-gray-200">
                          <Image src={selectedBodyColor.image_small} alt={selectedBodyColor.name} fill className="object-cover" />
                        </div>
                      )}
                      <span className="text-sm text-gray-600">{selectedBodyColor?.name}</span>
                    </div>
                  </div>
                </div>

                {/* Цвет профиля */}
                {variantProfileColors.length > 0 && (
                  <div>
                    <label className="text-sm text-gray-500 mb-1.5 block">Цвет профиля</label>
                    {availableProfileColors.length > 1 ? (
                      <select
                        value={selectedProfileColor?.id || ''}
                        onChange={(e) => {
                          const color = availableProfileColors.find(c => c.id === Number(e.target.value));
                          if (color) setSelectedProfileColor(color);
                        }}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                      >
                        {availableProfileColors.map((color) => (
                          <option key={color.id} value={color.id}>{color.name.replace(' профиль', '')}</option>
                        ))}
                      </select>
                    ) : (
                      <div className="flex items-center justify-between">
                        <span className="text-sm text-gray-400">—</span>
                        <span className="text-sm text-gray-700">{selectedProfileColor?.name.replace(' профиль', '')}</span>
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* Доп. комплектация */}
              <div className="mt-4 pt-4 border-t border-gray-100">
                <button
                  onClick={() => setShowAdditionalConfig(true)}
                  className="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 rounded-lg text-sm hover:border-[#62bb46] transition-colors"
                >
                  <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                  </svg>
                  Доп. комплектация
                </button>
              </div>
            </div>

            {/* Наполнение */}
            {filling && (
              <div className="bg-white rounded-xl shadow-sm p-6">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="font-bold text-gray-900">Внутреннее наполнение</h3>
                  <button
                    onClick={() => setShowFillingModal(true)}
                    className="text-[#62bb46] text-sm hover:underline"
                  >
                    Подробнее
                  </button>
                </div>
                {filling.short_name && (
                  <p className="text-gray-600">{filling.short_name}</p>
                )}
                {filling.image_plain && (
                  <div className="mt-4 relative h-48 bg-gray-50 rounded-lg overflow-hidden">
                    <Image
                      src={filling.image_plain}
                      alt="Наполнение"
                      fill
                      className="object-contain"
                      onError={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.style.display = 'none';
                      }}
                    />
                  </div>
                )}
              </div>
            )}

            {/* Материалы дверей */}
            {currentVariant?.door_material1 && (
              <div className="bg-white rounded-xl shadow-sm p-6">
                <h3 className="font-bold text-gray-900 mb-4">Материалы дверей</h3>
                <div className="space-y-2">
                  {[
                    currentVariant.door_material1,
                    currentVariant.door_material2,
                    currentVariant.door_material3
                  ]
                    .filter(Boolean)
                    .map((material, index) => (
                      <div key={index} className="flex items-center gap-2">
                        <div className="w-6 h-6 bg-gray-100 rounded flex items-center justify-center text-xs font-medium text-gray-500">
                          {index + 1}
                        </div>
                        <span className="text-gray-600">{material}</span>
                      </div>
                    ))}
                </div>
              </div>
            )}

            {/* Кнопки действий */}
            <div className="flex flex-col sm:flex-row gap-3">
              <button className="flex-1 py-3 bg-[#62bb46] hover:bg-[#4a9935] text-white font-bold rounded-xl transition-colors">
                Добавить в корзину
              </button>
              <button className="flex-1 py-3 border-2 border-[#62bb46] text-[#62bb46] hover:bg-[#62bb46] hover:text-white font-bold rounded-xl transition-colors">
                Купить в 1 клик
              </button>
            </div>

            {/* Артикул внизу мелким шрифтом */}
            {currentVariant && (
              <div className="text-xs text-gray-400 text-center">
                Артикул: {currentVariant.article}
              </div>
            )}
          </div>
        </div>

        {/* Описание серии */}
        {series?.description && (
          <div className="mt-12 bg-white rounded-xl shadow-sm p-8">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">О серии {series.name}</h2>
            <p className="text-gray-600 whitespace-pre-line">{series.description}</p>
          </div>
        )}

        {/* Видео */}
        {(series?.video1 || series?.video2) && (
          <div className="mt-8">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Видео о серии</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {series.video1 && (
                <div className="bg-white rounded-xl shadow-sm overflow-hidden aspect-video">
                  <video
                    src={series.video1}
                    controls
                    className="w-full h-full object-cover"
                  />
                </div>
              )}
              {series.video2 && (
                <div className="bg-white rounded-xl shadow-sm overflow-hidden aspect-video">
                  <video
                    src={series.video2}
                    controls
                    className="w-full h-full object-cover"
                  />
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {/* Модальное окно наполнения */}
      {showFillingModal && filling && (
        <div
          className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={() => setShowFillingModal(false)}
        >
          <div
            className="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="sticky top-0 bg-white border-b p-6 flex items-center justify-between">
              <h2 className="text-xl font-bold text-gray-900">Внутреннее наполнение</h2>
              <button
                onClick={() => setShowFillingModal(false)}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-6">
              {filling.short_name && (
                <h3 className="font-bold text-lg text-gray-900 mb-4">{filling.short_name}</h3>
              )}
              {filling.description && (
                <p className="text-gray-600 mb-6 whitespace-pre-line">{filling.description}</p>
              )}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {filling.image_plain && (
                  <div className="relative aspect-[3/4] bg-gray-50 rounded-lg overflow-hidden">
                    <Image
                      src={filling.image_plain}
                      alt="Схема"
                      fill
                      className="object-contain"
                    />
                    <span className="absolute bottom-2 left-2 bg-white/90 px-2 py-1 rounded text-sm">
                      Схема
                    </span>
                  </div>
                )}
                {filling.image_dimensions && (
                  <div className="relative aspect-[3/4] bg-gray-50 rounded-lg overflow-hidden">
                    <Image
                      src={filling.image_dimensions}
                      alt="С размерами"
                      fill
                      className="object-contain"
                    />
                    <span className="absolute bottom-2 left-2 bg-white/90 px-2 py-1 rounded text-sm">
                      С размерами
                    </span>
                  </div>
                )}
                {filling.image_filled && (
                  <div className="relative aspect-[3/4] bg-gray-50 rounded-lg overflow-hidden">
                    <Image
                      src={filling.image_filled}
                      alt="С вещами"
                      fill
                      className="object-contain"
                    />
                    <span className="absolute bottom-2 left-2 bg-white/90 px-2 py-1 rounded text-sm">
                      С вещами
                    </span>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Модальное окно выбора цвета корпуса */}
      {showBodyColorModal && (
        <div
          className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={() => setShowBodyColorModal(false)}
        >
          <div
            className="bg-white rounded-xl max-w-lg w-full max-h-[80vh] overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="sticky top-0 bg-white border-b p-4 flex items-center justify-between">
              <h2 className="text-lg font-bold text-gray-900">Выберите цвет корпуса</h2>
              <button
                onClick={() => setShowBodyColorModal(false)}
                className="p-1 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-4">
              <div className="grid grid-cols-3 sm:grid-cols-4 gap-3">
                {availableBodyColors.map((color) => (
                  <button
                    key={color.id}
                    onClick={() => {
                      setSelectedBodyColor(color);
                      setShowBodyColorModal(false);
                    }}
                    className={`relative aspect-square rounded-lg overflow-hidden border-2 transition-all ${
                      selectedBodyColor?.id === color.id
                        ? 'border-[#62bb46] ring-2 ring-[#62bb46] ring-offset-2'
                        : 'border-gray-200 hover:border-gray-400'
                    }`}
                  >
                    {color.image_small ? (
                      <Image src={color.image_small} alt={color.name} fill className="object-cover" />
                    ) : (
                      <div className="w-full h-full bg-gray-200 flex items-center justify-center text-lg font-medium text-gray-500">
                        {color.name.charAt(0)}
                      </div>
                    )}
                    <div className="absolute inset-x-0 bottom-0 bg-black/60 px-1 py-1">
                      <span className="text-white text-xs truncate block text-center">{color.name}</span>
                    </div>
                    {selectedBodyColor?.id === color.id && (
                      <div className="absolute top-1 right-1 w-5 h-5 bg-[#62bb46] rounded-full flex items-center justify-center">
                        <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    )}
                  </button>
                ))}
              </div>
              {selectedBodyColor?.description && (
                <div className="mt-4 p-3 bg-gray-50 rounded-lg">
                  <p className="text-sm text-gray-600">{selectedBodyColor.description}</p>
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Модальное окно информации о цвете корпуса */}
      {showBodyColorInfo && selectedBodyColor && (
        <div
          className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={() => setShowBodyColorInfo(false)}
        >
          <div
            className="bg-white rounded-xl max-w-md w-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="p-6">
              <div className="flex items-start justify-between mb-4">
                <h2 className="text-lg font-bold text-gray-900">{selectedBodyColor.name}</h2>
                <button
                  onClick={() => setShowBodyColorInfo(false)}
                  className="p-1 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              {selectedBodyColor.image_large && (
                <div className="relative aspect-square bg-gray-50 rounded-lg overflow-hidden mb-4">
                  <Image
                    src={selectedBodyColor.image_large}
                    alt={selectedBodyColor.name}
                    fill
                    className="object-cover"
                  />
                </div>
              )}
              {selectedBodyColor.description && (
                <p className="text-gray-600 text-sm">{selectedBodyColor.description}</p>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Модальное окно доп. комплектации */}
      {showAdditionalConfig && (
        <div
          className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={() => setShowAdditionalConfig(false)}
        >
          <div
            className="bg-white rounded-xl max-w-lg w-full"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="border-b p-4 flex items-center justify-between">
              <h2 className="text-lg font-bold text-gray-900">Дополнительная комплектация</h2>
              <button
                onClick={() => setShowAdditionalConfig(false)}
                className="p-1 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-6">
              <div className="text-center text-gray-500 py-8">
                <svg className="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p>Дополнительные товары скоро появятся</p>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
