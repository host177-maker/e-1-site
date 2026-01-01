'use client';

import { useEffect, useState, useCallback, useMemo, useRef } from 'react';
import { useParams, useSearchParams } from 'next/navigation';
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
  id: number;
  height: number;
  width: number;
  depth: number;
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
  const searchParams = useSearchParams();
  const slug = (params?.slug as string) || '';
  const isInitialLoad = useRef(true);
  const urlParamsApplied = useRef(false);

  // Сохраняем начальные параметры URL в ref, чтобы не перезагружать при изменении
  const initialUrlParams = useRef<{
    h: string | null;
    w: string | null;
    d: string | null;
    bc: string | null;
    pc: string | null;
  } | null>(null);

  // Читаем параметры URL только один раз при первом рендере
  if (initialUrlParams.current === null && searchParams) {
    initialUrlParams.current = {
      h: searchParams.get('h'),
      w: searchParams.get('w'),
      d: searchParams.get('d'),
      bc: searchParams.get('bc'),
      pc: searchParams.get('pc'),
    };
  }

  const [loading, setLoading] = useState(true);
  const [product, setProduct] = useState<CatalogProduct | null>(null);
  const [variants, setVariants] = useState<CatalogVariant[]>([]);
  const [bodyColors, setBodyColors] = useState<CatalogBodyColor[]>([]);
  // profileColors загружается для начальной инициализации, но далее используется variantProfileColors
  const [profileColors, setProfileColors] = useState<{ id: number; name: string }[]>([]);
  const [filling, setFilling] = useState<CatalogFilling | null>(null);
  const [fillings, setFillings] = useState<CatalogFilling[]>([]);
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

  // Временный выбор цвета корпуса в модальном окне (до нажатия "Применить")
  const [tempBodyColor, setTempBodyColor] = useState<CatalogBodyColor | null>(null);
  // ID цвета для показа описания в модальном окне
  const [showColorInfoId, setShowColorInfoId] = useState<number | null>(null);

  // Модальное окно выбора наполнения
  const [showFillingSelectModal, setShowFillingSelectModal] = useState(false);
  const [tempFilling, setTempFilling] = useState<CatalogFilling | null>(null);
  const [showFillingInfoId, setShowFillingInfoId] = useState<number | null>(null);

  // Сборка
  const [includeAssembly, setIncludeAssembly] = useState(false);

  // Цены (пока заглушки)
  const basePrice = 35990;
  const oldPrice = 72000;
  const assemblyPrice = Math.round(basePrice * 0.12);

  // Все уникальные цвета профиля из вариантов (для проверки наличия)
  // Связываем с полными данными из profileColors
  const variantProfileColors = useMemo(() => {
    const variantColorIds = new Set<number>();
    for (const v of variants) {
      if (v.profile_color_id) {
        variantColorIds.add(v.profile_color_id);
      }
    }
    // Возвращаем полные объекты CatalogProfileColor для цветов, которые есть в вариантах
    return profileColors.filter(c => variantColorIds.has(c.id));
  }, [variants, profileColors]);

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
        // Устанавливаем наполнение: берём первое из массива если есть
        const fillingsList = data.fillings || [];
        setFillings(fillingsList);
        setFilling(data.filling || fillingsList[0] || null);
        setSeries(data.series);

        // Читаем параметры из сохраненных начальных значений URL
        const urlParams = initialUrlParams.current;
        const urlHeight = urlParams?.h;
        const urlWidth = urlParams?.w;
        const urlDepth = urlParams?.d;
        const urlBodyColor = urlParams?.bc;
        const urlProfileColor = urlParams?.pc;

        // Установить значения из URL или из первого варианта
        if (data.variants.length > 0) {
          const firstVariant = data.variants[0];

          // Размеры
          const height = urlHeight ? Number(urlHeight) : firstVariant.height;
          const width = urlWidth ? Number(urlWidth) : firstVariant.width;
          const depth = urlDepth ? Number(urlDepth) : firstVariant.depth;

          // Проверяем, что такой вариант существует
          const variantExists = data.variants.some((v: CatalogVariant) =>
            v.height === height && v.width === width && v.depth === depth
          );

          if (variantExists) {
            setSelectedHeight(height);
            setSelectedWidth(width);
            setSelectedDepth(depth);
          } else {
            setSelectedHeight(firstVariant.height);
            setSelectedWidth(firstVariant.width);
            setSelectedDepth(firstVariant.depth);
          }

          // Цвет корпуса
          if (urlBodyColor) {
            const bc = data.bodyColors.find((c: CatalogBodyColor) => c.id === Number(urlBodyColor));
            if (bc) setSelectedBodyColor(bc);
            else if (firstVariant.body_color_id) {
              const defaultBc = data.bodyColors.find((c: CatalogBodyColor) => c.id === firstVariant.body_color_id);
              if (defaultBc) setSelectedBodyColor(defaultBc);
            }
          } else if (firstVariant.body_color_id) {
            const bc = data.bodyColors.find((c: CatalogBodyColor) => c.id === firstVariant.body_color_id);
            if (bc) setSelectedBodyColor(bc);
          }

          // Цвет профиля
          if (urlProfileColor) {
            const pc = data.profileColors.find((c: { id: number; name: string }) => c.id === Number(urlProfileColor));
            if (pc) setSelectedProfileColor(pc);
            else if (firstVariant.profile_color_id) {
              const defaultPc = data.profileColors.find((c: { id: number; name: string }) => c.id === firstVariant.profile_color_id);
              if (defaultPc) setSelectedProfileColor(defaultPc);
            }
          } else if (firstVariant.profile_color_id) {
            const pc = data.profileColors.find((c: { id: number; name: string }) => c.id === firstVariant.profile_color_id);
            if (pc) setSelectedProfileColor(pc);
          }
        }

        urlParamsApplied.current = true;
      }
    } catch (error) {
      console.error('Error fetching product:', error);
    } finally {
      setLoading(false);
      isInitialLoad.current = false;
    }
  }, [slug]);

  useEffect(() => {
    if (slug) {
      fetchProduct();
    }
  }, [slug, fetchProduct]);

  // Обновляем URL при изменении параметров (без перезагрузки страницы)
  useEffect(() => {
    if (!urlParamsApplied.current || isInitialLoad.current) return;
    if (selectedHeight === null || selectedWidth === null || selectedDepth === null) return;

    const params = new URLSearchParams();
    params.set('h', String(selectedHeight));
    params.set('w', String(selectedWidth));
    params.set('d', String(selectedDepth));
    if (selectedBodyColor) {
      params.set('bc', String(selectedBodyColor.id));
    }
    if (selectedProfileColor) {
      params.set('pc', String(selectedProfileColor.id));
    }

    const newUrl = `/product/${slug}?${params.toString()}`;
    // Используем history API напрямую, чтобы избежать перезагрузки
    window.history.replaceState(null, '', newUrl);
  }, [selectedHeight, selectedWidth, selectedDepth, selectedBodyColor, selectedProfileColor, slug]);

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
  // Ref для отслеживания первой загрузки наполнения
  const isFirstFillingLoad = useRef(true);

  useEffect(() => {
    if (!product || selectedHeight === null || selectedWidth === null || selectedDepth === null) return;

    // Пропускаем первую загрузку - данные уже есть из GET запроса
    if (isFirstFillingLoad.current) {
      isFirstFillingLoad.current = false;
      return;
    }

    const loadFilling = async () => {
      try {
        console.log('loadFilling запрос:', {
          seriesId: product.series_id,
          height: selectedHeight,
          width: selectedWidth,
          depth: selectedDepth
        });
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
        console.log('loadFilling ответ:', { fillingsCount: data.fillings?.length, filling: data.filling, success: data.success, details: data.details });

        // Если запрос не успешен - не очищаем текущие наполнения
        if (!data.success) {
          console.error('loadFilling ошибка сервера:', data.error, data.details);
          return; // Сохраняем текущее состояние
        }

        // Устанавливаем варианты наполнения для выбранного размера
        if (data.fillings && data.fillings.length > 0) {
          setFillings(data.fillings);
          setFilling(data.fillings[0]); // Первый вариант по умолчанию
        } else {
          // Если нет наполнений - очищаем только если запрос успешен
          setFillings([]);
          setFilling(null);
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
    const availableIds = new Set(filtered.map(v => v.profile_color_id).filter(Boolean));
    return profileColors.filter(c => availableIds.has(c.id));
  }, [variants, selectedHeight, selectedWidth, selectedDepth, selectedBodyColor, profileColors]);

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

  // Если товар есть, но нет вариантов - показываем информацию
  const hasNoVariants = variants.length === 0;

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
        {/* Заголовок - мобильная версия (над изображением) */}
        <div className="lg:hidden mb-4">
          <div className="text-sm text-gray-500 mb-1">
            {product.series_name}
          </div>
          <h1 className="text-xl font-bold text-gray-900 line-clamp-2">
            {product.name}
          </h1>
        </div>

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
            {/* Заголовок - только десктоп (скрыт на мобильных) */}
            <div className="hidden lg:block">
              <div className="text-sm text-gray-500 mb-1">
                {product.series_name}
              </div>
              <h1 className="text-2xl font-bold text-gray-900 min-h-[3rem] line-clamp-2">
                {product.name}
              </h1>
            </div>

            {/* Сообщение о недоступности вариантов */}
            {hasNoVariants && (
              <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div className="flex items-start gap-3">
                  <svg className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <div>
                    <div className="font-medium text-amber-800">Варианты товара недоступны</div>
                    <p className="text-sm text-amber-700 mt-1">
                      Для этого товара не найдены варианты в базе данных. Возможно, требуется повторный импорт данных.
                    </p>
                  </div>
                </div>
              </div>
            )}

            {/* Цена - показываем только если есть варианты */}
            {!hasNoVariants && (
              <div className="bg-white rounded-xl shadow-sm p-5">
                <div className="flex items-baseline gap-3 mb-4">
                  <span className="text-3xl font-bold text-gray-900">
                    {(basePrice + (includeAssembly ? assemblyPrice : 0)).toLocaleString('ru-RU')} ₽
                  </span>
                  <span className="text-lg text-gray-400 line-through">
                    {oldPrice.toLocaleString('ru-RU')} ₽
                  </span>
                </div>

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
            )}

            {/* Наполнение и размер/цвет в одном ряду */}
            {!hasNoVariants && (
              <div className="flex gap-4">
                {/* Наполнение - левый блок 1/3 */}
                {filling && (
                  <div className="w-1/3 bg-white rounded-xl shadow-sm p-4">
                    <div className="flex items-center justify-between mb-3">
                      <h3 className="font-bold text-gray-900 text-sm">Внутреннее наполнение</h3>
                      <button
                        onClick={() => setShowFillingModal(true)}
                        className="text-[#62bb46] text-xs hover:underline"
                      >
                        Подробнее
                      </button>
                    </div>
                    {/* Кнопка выбора наполнения */}
                    {fillings.length > 1 ? (
                      <button
                        onClick={() => {
                          setTempFilling(filling);
                          setShowFillingSelectModal(true);
                        }}
                        className="w-full flex items-center gap-2 px-2 py-1.5 border border-gray-200 rounded text-xs hover:border-[#62bb46] transition-colors text-left mb-2"
                      >
                        <span className="text-gray-700 truncate flex-1">
                          {filling.short_name || `${filling.width}×${filling.height}×${filling.depth}`}
                        </span>
                        <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                      </button>
                    ) : filling.short_name ? (
                      <p className="text-gray-600 text-xs mb-2">{filling.short_name}</p>
                    ) : null}
                    {filling.image_plain && (
                      <div className="relative h-32 bg-gray-50 rounded-lg overflow-hidden">
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

                {/* Выбрать размер и цвет - правый блок 2/3 */}
                <div className={`${filling ? 'w-2/3' : 'w-full'} bg-white rounded-xl shadow-sm p-4`}>
                  <h3 className="text-sm font-medium text-gray-900 mb-3">Выбрать размер и цвет</h3>

                  {/* Размеры в ряд */}
                  <div className="grid grid-cols-3 gap-2 mb-3">
                    <div>
                      <label className="text-xs text-gray-500 mb-1 block">Ширина</label>
                      {availableWidths.length > 1 ? (
                        <select
                          value={selectedWidth || ''}
                          onChange={(e) => setSelectedWidth(Number(e.target.value))}
                          className="w-full px-2 py-1.5 border border-gray-200 rounded text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                        >
                          {availableWidths.map((width) => (
                            <option key={width} value={width}>{width} мм</option>
                          ))}
                        </select>
                      ) : (
                        <div className="px-2 py-1.5 text-sm text-gray-700 bg-gray-50 rounded border border-gray-100">
                          {selectedWidth} мм
                        </div>
                      )}
                    </div>
                    <div>
                      <label className="text-xs text-gray-500 mb-1 block">Высота</label>
                      {availableHeights.length > 1 ? (
                        <select
                          value={selectedHeight || ''}
                          onChange={(e) => setSelectedHeight(Number(e.target.value))}
                          className="w-full px-2 py-1.5 border border-gray-200 rounded text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                        >
                          {availableHeights.map((height) => (
                            <option key={height} value={height}>{height} мм</option>
                          ))}
                        </select>
                      ) : (
                        <div className="px-2 py-1.5 text-sm text-gray-700 bg-gray-50 rounded border border-gray-100">
                          {selectedHeight} мм
                        </div>
                      )}
                    </div>
                    <div>
                      <label className="text-xs text-gray-500 mb-1 block">Глубина</label>
                      {availableDepths.length > 1 ? (
                        <select
                          value={selectedDepth || ''}
                          onChange={(e) => setSelectedDepth(Number(e.target.value))}
                          className="w-full px-2 py-1.5 border border-gray-200 rounded text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                        >
                          {availableDepths.map((depth) => (
                            <option key={depth} value={depth}>{depth} мм</option>
                          ))}
                        </select>
                      ) : (
                        <div className="px-2 py-1.5 text-sm text-gray-700 bg-gray-50 rounded border border-gray-100">
                          {selectedDepth} мм
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Цвета */}
                  <div className="grid grid-cols-2 gap-2 mb-3">
                    {/* Цвет корпуса */}
                    <div>
                      <label className="text-xs text-gray-500 mb-1 block">Цвет корпуса</label>
                      <button
                        onClick={() => {
                          setTempBodyColor(selectedBodyColor);
                          setShowBodyColorModal(true);
                        }}
                        className="w-full flex items-center gap-2 px-2 py-1.5 border border-gray-200 rounded text-sm hover:border-[#62bb46] transition-colors text-left"
                      >
                        {selectedBodyColor?.image_small && (
                          <div className="relative w-5 h-5 rounded overflow-hidden border border-gray-200 flex-shrink-0">
                            <Image src={selectedBodyColor.image_small} alt={selectedBodyColor.name} fill className="object-cover" />
                          </div>
                        )}
                        <span className="text-gray-700 truncate flex-1">{selectedBodyColor?.name || 'Выбрать'}</span>
                        <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                      </button>
                    </div>

                    {/* Цвет профиля */}
                    {variantProfileColors.length > 0 && (
                      <div>
                        <label className="text-xs text-gray-500 mb-1 block">Цвет профиля</label>
                        {availableProfileColors.length > 1 ? (
                          <select
                            value={selectedProfileColor?.id || ''}
                            onChange={(e) => {
                              const color = availableProfileColors.find(c => c.id === Number(e.target.value));
                              if (color) setSelectedProfileColor(color);
                            }}
                            className="w-full px-2 py-1.5 border border-gray-200 rounded text-sm bg-white focus:outline-none focus:border-[#62bb46] cursor-pointer"
                          >
                            {availableProfileColors.map((color) => (
                              <option key={color.id} value={color.id}>{color.name.replace(' профиль', '')}</option>
                            ))}
                          </select>
                        ) : (
                          <div className="px-2 py-1.5 text-sm text-gray-700 bg-gray-50 rounded border border-gray-100">
                            {selectedProfileColor?.name.replace(' профиль', '') || '—'}
                          </div>
                        )}
                      </div>
                    )}
                  </div>

                  {/* Доп. комплектация */}
                  <button
                    onClick={() => setShowAdditionalConfig(true)}
                    className="w-full flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded text-sm hover:border-[#62bb46] transition-colors"
                  >
                    <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Доп. комплектация
                  </button>
                </div>
              </div>
            )}

            {/* Кнопки действий - показываем только если есть варианты */}
            {!hasNoVariants && (
              <div className="flex flex-col sm:flex-row gap-3">
                <button className="flex-1 py-3 bg-[#62bb46] hover:bg-[#4a9935] text-white font-bold rounded-xl transition-colors">
                  Добавить в корзину
                </button>
                <button className="flex-1 py-3 border-2 border-[#62bb46] text-[#62bb46] hover:bg-[#62bb46] hover:text-white font-bold rounded-xl transition-colors">
                  Купить в 1 клик
                </button>
              </div>
            )}

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
              <div className="space-y-3">
                {availableBodyColors.map((color) => (
                  <div key={color.id}>
                    <div
                      onClick={() => setTempBodyColor(color)}
                      className={`flex items-center gap-3 p-2 rounded-lg cursor-pointer transition-all ${
                        tempBodyColor?.id === color.id
                          ? 'bg-[#62bb46]/10 ring-2 ring-[#62bb46]'
                          : 'bg-gray-50 hover:bg-gray-100'
                      }`}
                    >
                      {/* Миниатюра цвета */}
                      <div className="relative w-12 h-12 rounded-lg overflow-hidden border border-gray-200 flex-shrink-0">
                        {color.image_small ? (
                          <Image src={color.image_small} alt={color.name} fill className="object-cover" />
                        ) : (
                          <div className="w-full h-full bg-gray-200 flex items-center justify-center text-lg font-medium text-gray-500">
                            {color.name.charAt(0)}
                          </div>
                        )}
                      </div>
                      {/* Название цвета */}
                      <span className="flex-1 text-sm font-medium text-gray-900">{color.name}</span>
                      {/* Кнопка информации */}
                      {color.description && (
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            setShowColorInfoId(showColorInfoId === color.id ? null : color.id);
                          }}
                          className="w-6 h-6 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center text-gray-600 flex-shrink-0"
                        >
                          <span className="text-xs font-bold">i</span>
                        </button>
                      )}
                      {/* Галочка выбора */}
                      {tempBodyColor?.id === color.id && (
                        <div className="w-6 h-6 bg-[#62bb46] rounded-full flex items-center justify-center flex-shrink-0">
                          <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                          </svg>
                        </div>
                      )}
                    </div>
                    {/* Описание цвета (раскрывается при нажатии на i) */}
                    {showColorInfoId === color.id && color.description && (
                      <div className="mt-2 ml-15 p-3 bg-gray-100 rounded-lg">
                        <p className="text-sm text-gray-600">{color.description}</p>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
            {/* Кнопка применить */}
            <div className="sticky bottom-0 bg-white border-t p-4">
              <button
                onClick={() => {
                  if (tempBodyColor) {
                    setSelectedBodyColor(tempBodyColor);
                  }
                  setShowBodyColorModal(false);
                }}
                className="w-full py-3 bg-[#62bb46] hover:bg-[#4a9935] text-white font-bold rounded-lg transition-colors"
              >
                Применить
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Модальное окно выбора наполнения */}
      {showFillingSelectModal && fillings.length > 0 && (
        <div
          className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
          onClick={() => setShowFillingSelectModal(false)}
        >
          <div
            className="bg-white rounded-xl max-w-2xl w-full max-h-[85vh] overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="sticky top-0 bg-white border-b p-4 flex items-center justify-between z-10">
              <h2 className="text-lg font-bold text-gray-900">Выберите наполнение</h2>
              <button
                onClick={() => setShowFillingSelectModal(false)}
                className="p-1 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-4">
              <div className="space-y-4">
                {fillings.map((f) => (
                  <div key={f.id}>
                    <div
                      onClick={() => setTempFilling(f)}
                      className={`flex gap-4 p-3 rounded-lg cursor-pointer transition-all ${
                        tempFilling?.id === f.id
                          ? 'bg-[#62bb46]/10 ring-2 ring-[#62bb46]'
                          : 'bg-gray-50 hover:bg-gray-100'
                      }`}
                    >
                      {/* Изображение наполнения */}
                      <div className="relative w-24 h-24 rounded-lg overflow-hidden border border-gray-200 flex-shrink-0 bg-white">
                        {f.image_plain ? (
                          <Image src={f.image_plain} alt={f.short_name || 'Наполнение'} fill className="object-contain" />
                        ) : (
                          <div className="w-full h-full bg-gray-100 flex items-center justify-center">
                            <svg className="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                          </div>
                        )}
                      </div>
                      {/* Информация */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between gap-2">
                          <h3 className="font-medium text-gray-900 text-sm">
                            {f.short_name || `Наполнение ${f.width}×${f.height}×${f.depth}`}
                          </h3>
                          {/* Галочка выбора */}
                          {tempFilling?.id === f.id && (
                            <div className="w-6 h-6 bg-[#62bb46] rounded-full flex items-center justify-center flex-shrink-0">
                              <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                              </svg>
                            </div>
                          )}
                        </div>
                        <p className="text-xs text-gray-500 mt-1">
                          Размер: {f.width}×{f.height}×{f.depth} мм
                        </p>
                        {f.description && (
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              setShowFillingInfoId(showFillingInfoId === f.id ? null : f.id);
                            }}
                            className="mt-2 text-xs text-[#62bb46] hover:underline"
                          >
                            {showFillingInfoId === f.id ? 'Скрыть описание' : 'Показать описание'}
                          </button>
                        )}
                      </div>
                    </div>
                    {/* Описание наполнения (раскрывается) */}
                    {showFillingInfoId === f.id && f.description && (
                      <div className="mt-2 ml-28 p-3 bg-gray-100 rounded-lg">
                        <p className="text-sm text-gray-600 whitespace-pre-line">{f.description}</p>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            </div>
            {/* Кнопка применить */}
            <div className="sticky bottom-0 bg-white border-t p-4">
              <button
                onClick={() => {
                  if (tempFilling) {
                    setFilling(tempFilling);
                  }
                  setShowFillingSelectModal(false);
                }}
                className="w-full py-3 bg-[#62bb46] hover:bg-[#4a9935] text-white font-bold rounded-lg transition-colors"
              >
                Применить
              </button>
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
