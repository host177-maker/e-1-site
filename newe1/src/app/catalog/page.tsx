'use client';

import { Suspense, useEffect, useState, useCallback, useRef, Fragment } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { useCity } from '@/context/CityContext';
import { useWishlist } from '@/context/WishlistContext';
import CatalogFilter from '@/components/CatalogFilter';
import MeasurementModal from '@/components/MeasurementModal';

interface CatalogSeries {
  id: number;
  name: string;
  slug: string;
}

interface CatalogProduct {
  id: number;
  name: string;
  slug: string;
  series_id: number;
  series_name?: string;
  door_type_name?: string;
  door_count?: number;
  variants_count?: number;
  default_image?: string;
}

interface FilterOptions {
  doorTypes: { id: number; name: string; slug: string; count: number }[];
  series: { id: number; name: string; slug: string; count: number }[];
  widthRange: { min: number; max: number };
  widthRangeCounts?: { key: string; count: number }[];
  heights: { value: number; count: number }[];
  depths: { value: number; count: number }[];
  depthRangeCounts?: { key: string; count: number }[];
  priceRange: { min: number; max: number };
}

interface FilterValues {
  doorTypes: string[];
  series: string[];
  widthRanges: string[];
  heights: number[];
  depthRanges: string[];
  priceRanges: string[];
}

// Градации цены
const priceRangesList = [
  { key: '0-19999', label: 'до 19 999 ₽', min: 0, max: 19999 },
  { key: '20000-34999', label: '20 000 - 34 999 ₽', min: 20000, max: 34999 },
  { key: '35000-49999', label: '35 000 - 49 999 ₽', min: 35000, max: 49999 },
  { key: '50000-79999', label: '50 000 - 79 999 ₽', min: 50000, max: 79999 },
  { key: '80000-109999', label: '80 000 - 109 999 ₽', min: 80000, max: 109999 },
  { key: '110000-999999', label: 'от 110 000 ₽', min: 110000, max: 999999 },
];

// Градации ширины (в мм для поиска в БД)
const widthRangesList = [
  { key: '0-1090', label: 'до 109 см', min: 0, max: 1090 },
  { key: '1100-1390', label: '110-139 см', min: 1100, max: 1390 },
  { key: '1400-1610', label: '140-161 см', min: 1400, max: 1610 },
  { key: '1620-2000', label: '162-200 см', min: 1620, max: 2000 },
  { key: '2010-2390', label: '201-239 см', min: 2010, max: 2390 },
  { key: '2400-9990', label: 'от 240 см', min: 2400, max: 9990 },
];

// Градации глубины (в мм для поиска в БД)
const depthRangesList = [
  { key: '0-450', label: 'до 45 см', min: 0, max: 450 },
  { key: '450-530', label: '45-53 см', min: 450, max: 530 },
  { key: '530-9990', label: 'свыше 53 см', min: 530, max: 9990 },
];

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

const DEFAULT_FILTERS: FilterValues = {
  doorTypes: [],
  series: [],
  widthRanges: [],
  heights: [],
  depthRanges: [],
  priceRanges: [],
};

function CatalogPageContent() {
  const searchParams = useSearchParams();
  const isInitialLoad = useRef(true);
  const filterRef = useRef<HTMLDivElement>(null);
  const savedScrollPosition = useRef<number | null>(null);
  const { city } = useCity();
  const { isInWishlist, addToWishlist, removeByProductId } = useWishlist();

  // Читаем параметры из URL при инициализации
  const getInitialFilters = useCallback((): FilterValues => {
    if (!searchParams) return DEFAULT_FILTERS;
    return {
      doorTypes: searchParams.get('doorTypes')?.split(',').filter(Boolean) || [],
      series: searchParams.get('series')?.split(',').filter(Boolean) || [],
      widthRanges: searchParams.get('widthRanges')?.split(',').filter(Boolean) || [],
      heights: searchParams.get('heights')?.split(',').map(h => parseInt(h)).filter(h => !isNaN(h)) || [],
      depthRanges: searchParams.get('depthRanges')?.split(',').filter(Boolean) || [],
      priceRanges: searchParams.get('priceRanges')?.split(',').filter(Boolean) || [],
    };
  }, [searchParams]);

  const [products, setProducts] = useState<CatalogProduct[]>([]);
  const [series, setSeries] = useState<CatalogSeries[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [total, setTotal] = useState(0);
  const [currentPage, setCurrentPage] = useState(parseInt(searchParams?.get('page') || '1') || 1);
  const [filters, setFilters] = useState<FilterValues>(getInitialFilters);
  const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
  const [mobileFilterOpen, setMobileFilterOpen] = useState(false);
  const [measurementModalOpen, setMeasurementModalOpen] = useState(false);
  const [isLoadMoreMode, setIsLoadMoreMode] = useState(false); // Режим "показать ещё" - без рекламного модуля

  // Базовый лимит для отображения (включая рекламный модуль)
  const baseDisplayLimit = 20;
  // Рекламный модуль занимает 1 место, поэтому грузим на 1 товар меньше (кроме режима "показать ещё")
  const getProductsLimit = (forLoadMore: boolean) => forLoadMore ? baseDisplayLimit : baseDisplayLimit - 1;

  const fetchProducts = useCallback(async (append = false, page = 1, customOffset?: number) => {
    const limit = getProductsLimit(append);
    if (append) {
      setLoadingMore(true);
    } else {
      // Сохраняем позицию скролла относительно фильтра перед загрузкой
      if (filterRef.current) {
        const filterRect = filterRef.current.getBoundingClientRect();
        savedScrollPosition.current = filterRect.top;
      }
      setLoading(true);
    }
    try {
      const params = new URLSearchParams();
      if (filters.series.length > 0) params.set('series', filters.series.join(','));
      if (filters.doorTypes.length > 0) params.set('doorTypes', filters.doorTypes.join(','));

      // Парсим диапазоны ширины (мультивыбор)
      if (filters.widthRanges.length > 0) {
        let minWidth = Infinity;
        let maxWidth = 0;
        filters.widthRanges.forEach(key => {
          const range = widthRangesList.find(r => r.key === key);
          if (range) {
            minWidth = Math.min(minWidth, range.min);
            maxWidth = Math.max(maxWidth, range.max);
          }
        });
        if (minWidth !== Infinity) params.set('widthMin', minWidth.toString());
        if (maxWidth > 0) params.set('widthMax', maxWidth.toString());
      }

      if (filters.heights.length > 0) params.set('heights', filters.heights.join(','));

      // Парсим диапазоны глубины (мультивыбор)
      if (filters.depthRanges.length > 0) {
        let minDepth = Infinity;
        let maxDepth = 0;
        filters.depthRanges.forEach(key => {
          const range = depthRangesList.find(r => r.key === key);
          if (range) {
            minDepth = Math.min(minDepth, range.min);
            maxDepth = Math.max(maxDepth, range.max);
          }
        });
        if (minDepth !== Infinity) params.set('depthMin', minDepth.toString());
        if (maxDepth > 0) params.set('depthMax', maxDepth.toString());
      }

      // Парсим диапазоны цены (мультивыбор)
      if (filters.priceRanges.length > 0) {
        let minPrice = Infinity;
        let maxPrice = 0;
        filters.priceRanges.forEach(key => {
          const range = priceRangesList.find(r => r.key === key);
          if (range) {
            minPrice = Math.min(minPrice, range.min);
            maxPrice = Math.max(maxPrice, range.max);
          }
        });
        if (minPrice !== Infinity) params.set('priceMin', minPrice.toString());
        if (maxPrice > 0) params.set('priceMax', maxPrice.toString());
      }

      params.set('limit', limit.toString());
      // Offset: используем customOffset для append, иначе рассчитываем от страницы
      const offset = customOffset !== undefined ? customOffset : (page - 1) * limit;
      params.set('offset', offset.toString());

      // Всегда запрашиваем filterOptions для обновления счётчиков
      params.set('includeFilters', 'true');

      const response = await fetch(`/api/catalog?${params}`);
      const data = await response.json();

      if (data.success) {
        if (append) {
          setProducts(prev => [...prev, ...data.products]);
        } else {
          setProducts(data.products);
        }
        setSeries(data.series);
        setTotal(data.total);
        if (data.filterOptions) {
          setFilterOptions(data.filterOptions);
        }
      }
    } catch (error) {
      console.error('Error fetching catalog:', error);
    } finally {
      setLoading(false);
      setLoadingMore(false);
      isInitialLoad.current = false;

      // Восстанавливаем позицию скролла относительно фильтра после загрузки
      if (savedScrollPosition.current !== null && filterRef.current && !append) {
        requestAnimationFrame(() => {
          if (filterRef.current && savedScrollPosition.current !== null) {
            const filterRect = filterRef.current.getBoundingClientRect();
            const scrollDelta = filterRect.top - savedScrollPosition.current;
            if (Math.abs(scrollDelta) > 10) { // Только если есть значительный прыжок
              window.scrollBy({ top: scrollDelta, behavior: 'instant' });
            }
            savedScrollPosition.current = null;
          }
        });
      }
    }
  }, [filters]);

  useEffect(() => {
    fetchProducts(false, currentPage);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters, currentPage]);

  // Обновляем URL при изменении фильтров
  useEffect(() => {
    if (isInitialLoad.current) return;

    const params = new URLSearchParams();
    if (filters.doorTypes.length > 0) params.set('doorTypes', filters.doorTypes.join(','));
    if (filters.series.length > 0) params.set('series', filters.series.join(','));
    if (filters.widthRanges.length > 0) params.set('widthRanges', filters.widthRanges.join(','));
    if (filters.heights.length > 0) params.set('heights', filters.heights.join(','));
    if (filters.depthRanges.length > 0) params.set('depthRanges', filters.depthRanges.join(','));
    if (filters.priceRanges.length > 0) params.set('priceRanges', filters.priceRanges.join(','));
    if (currentPage > 1) params.set('page', currentPage.toString());

    const queryString = params.toString();
    const newUrl = queryString ? `/catalog?${queryString}` : '/catalog';
    window.history.replaceState(null, '', newUrl);
  }, [filters, currentPage]);

  const handleFiltersChange = (newFilters: FilterValues) => {
    setFilters(newFilters);
    setCurrentPage(1);
    setIsLoadMoreMode(false); // Сброс режима при смене фильтров
  };

  const handleLoadMore = () => {
    setIsLoadMoreMode(true); // Включаем режим "показать ещё"
    // Передаём offset = количество уже загруженных товаров
    fetchProducts(true, 1, products.length);
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    setIsLoadMoreMode(false); // Сброс режима при переходе на другую страницу
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const hasMore = products.length < total;
  // Для пагинации используем базовый лимит
  const totalPages = Math.ceil(total / baseDisplayLimit);

  // Generate page numbers to display
  const getPageNumbers = () => {
    const pages: (number | string)[] = [];
    const maxVisiblePages = 5;

    if (totalPages <= maxVisiblePages + 2) {
      for (let i = 1; i <= totalPages; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);

      if (currentPage > 3) {
        pages.push('...');
      }

      const start = Math.max(2, currentPage - 1);
      const end = Math.min(totalPages - 1, currentPage + 1);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      if (currentPage < totalPages - 2) {
        pages.push('...');
      }

      pages.push(totalPages);
    }

    return pages;
  };

  // Get title based on filters
  const getPageTitle = () => {
    if (filters.series.length === 1) {
      const selectedSeries = series.find(s => s.slug === filters.series[0]);
      if (selectedSeries) {
        return `${selectedSeries.name} в г. ${city.name}`;
      }
    }
    return `Шкафы в г. ${city.name}`;
  };

  // Count active filters
  const activeFiltersCount = [
    filters.doorTypes.length > 0,
    filters.series.length > 0,
    filters.heights.length > 0,
    filters.depthRanges.length > 0,
    filters.widthRanges.length > 0,
    filters.priceRanges.length > 0,
  ].filter(Boolean).length;

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 py-6">
        <div className="max-w-[1440px] mx-auto px-4">
          <h1 className="text-2xl md:text-3xl font-bold text-gray-900">{getPageTitle()}</h1>
        </div>
      </div>

      <div className="max-w-[1440px] mx-auto px-4 py-8">
        <div className="flex gap-6">
          {/* Sidebar filter - desktop only */}
          <div ref={filterRef} className="hidden lg:block w-64 flex-shrink-0">
            <CatalogFilter
              filterOptions={filterOptions}
              filters={filters}
              onFiltersChange={handleFiltersChange}
              isOpen={false}
              onClose={() => {}}
              totalProducts={total}
            />
          </div>

          {/* Main content */}
          <div className="flex-1 min-w-0 min-h-[600px]">
            {/* Mobile filter button */}
            <div className="mb-6 flex items-center justify-end lg:hidden">
              <button
                onClick={() => setMobileFilterOpen(true)}
                className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Фильтры
                {activeFiltersCount > 0 && (
                  <span className="bg-[#62bb46] text-white text-xs px-1.5 py-0.5 rounded-full">
                    {activeFiltersCount}
                  </span>
                )}
              </button>
            </div>

            {/* Products grid */}
            {loading ? (
              <div className="flex justify-center py-16">
                <div className="w-10 h-10 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
              </div>
            ) : products.length === 0 ? (
              <div className="bg-white rounded-xl shadow-sm p-12 text-center">
                <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <h3 className="text-xl font-medium text-gray-900 mb-2">Товары не найдены</h3>
                <p className="text-gray-500">Попробуйте изменить фильтры</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                {products.map((product, index) => {
                  const inWishlist = isInWishlist(product.id);

                  // Рекламный модуль на 4-й позиции (только если товаров >= 3 и не режим "показать ещё")
                  const showPromoCard = products.length >= 3 && index === 3 && !isLoadMoreMode;

                  return (
                    <Fragment key={product.id}>
                      {showPromoCard && (
                        <div
                          onClick={() => setMeasurementModalOpen(true)}
                          className="group bg-gradient-to-br from-[#78c47d] to-[#4a9b50] rounded-lg p-4 cursor-pointer hover:shadow-lg transition-all relative flex flex-col justify-between min-h-[280px]"
                        >
                          {/* Декоративные элементы */}
                          <div className="absolute top-3 right-3 w-16 h-16 bg-white/10 rounded-full" />
                          <div className="absolute bottom-6 left-3 w-8 h-8 bg-white/10 rounded-full" />

                          <div className="relative z-10">
                            <div className="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-3">
                              <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                              </svg>
                            </div>
                            <h3 className="text-white font-bold text-lg leading-tight mb-2">
                              Шкафы по вашим размерам
                            </h3>
                            <p className="text-white/80 text-sm leading-snug">
                              Замер и консультация на дому
                            </p>
                          </div>

                          <div className="relative z-10 mt-4">
                            <span className="inline-flex items-center gap-2 px-4 py-2 bg-white text-[#4a9b50] text-sm font-bold rounded-lg group-hover:bg-gray-100 transition-colors">
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              Вызвать замерщика
                            </span>
                          </div>
                        </div>
                      )}

                      <div className="group bg-white rounded-lg p-3 hover:shadow-md transition-shadow relative">
                        {/* Сердечко избранного */}
                        <button
                          onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (inWishlist) {
                              removeByProductId(product.id);
                            } else {
                              addToWishlist({
                                id: Date.now(),
                                productId: product.id,
                                name: product.name,
                                slug: product.slug,
                                image: product.default_image || PLACEHOLDER_IMAGE,
                                price: 35990,
                              });
                            }
                          }}
                          className="absolute top-4 right-4 z-10 p-1.5 rounded-full bg-white/80 hover:bg-white shadow-sm transition-colors"
                          title={inWishlist ? 'Убрать из избранного' : 'Добавить в избранное'}
                        >
                          <svg
                            className={`w-5 h-5 transition-colors ${inWishlist ? 'text-red-500 fill-red-500' : 'text-gray-400 hover:text-red-400'}`}
                            fill={inWishlist ? 'currentColor' : 'none'}
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                            />
                          </svg>
                        </button>

                        <Link href={`/product/${product.slug}`}>
                          <div className="aspect-square relative mb-2 overflow-hidden">
                            <Image
                              src={product.default_image || PLACEHOLDER_IMAGE}
                              alt={product.name}
                              fill
                              className="object-contain group-hover:scale-105 transition-transform"
                              onError={(e) => {
                                const target = e.target as HTMLImageElement;
                                target.src = PLACEHOLDER_IMAGE;
                              }}
                            />
                          </div>
                          <h3 className="text-xs text-gray-700 leading-tight group-hover:text-[#62bb46] transition-colors line-clamp-2 mb-2 h-8 font-[var(--font-open-sans)] font-normal">
                            {product.name}
                          </h3>
                        </Link>
                        <div className="flex items-baseline gap-2 mb-2">
                          <span className="text-sm font-bold text-gray-900">35 990 ₽</span>
                          <span className="text-xs text-gray-400 line-through">72 000 ₽</span>
                        </div>
                        <Link
                          href={`/product/${product.slug}`}
                          className="block w-full py-2 bg-[#62bb46] text-white text-sm font-medium rounded hover:bg-[#55a83d] transition-colors text-center"
                        >
                          Купить
                        </Link>
                      </div>
                    </Fragment>
                  );
                })}
              </div>
            )}

            {/* Load more button AND Pagination */}
            {!loading && total > 0 && totalPages > 1 && (
              <div className="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                {/* Load more button */}
                {hasMore && (
                  <button
                    onClick={handleLoadMore}
                    disabled={loadingMore}
                    className="px-6 py-2.5 bg-white border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-[#62bb46] hover:text-[#62bb46] transition-colors disabled:opacity-50 text-sm uppercase tracking-wide"
                  >
                    {loadingMore ? (
                      <span className="flex items-center gap-2">
                        <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
                        Загрузка...
                      </span>
                    ) : (
                      'Показать ещё'
                    )}
                  </button>
                )}

                {/* Pagination */}
                <div className="flex items-center gap-1">
                  {/* Page numbers */}
                  {getPageNumbers().map((page, index) => (
                    typeof page === 'number' ? (
                      <button
                        key={index}
                        onClick={() => handlePageChange(page)}
                        className={`w-9 h-9 rounded-lg font-medium transition-colors text-sm ${
                          currentPage === page
                            ? 'bg-[#62bb46] text-white'
                            : 'bg-white border border-gray-300 hover:border-[#62bb46] text-gray-700'
                        }`}
                      >
                        {page}
                      </button>
                    ) : (
                      <span key={index} className="px-1 text-gray-400">...</span>
                    )
                  ))}

                  {/* Next button */}
                  {currentPage < totalPages && (
                    <button
                      onClick={() => handlePageChange(currentPage + 1)}
                      className="p-2 rounded-lg border border-gray-300 hover:border-[#62bb46]"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Mobile filter modal - only renders on mobile */}
      <CatalogFilter
        filterOptions={filterOptions}
        filters={filters}
        onFiltersChange={handleFiltersChange}
        isOpen={mobileFilterOpen}
        onClose={() => setMobileFilterOpen(false)}
        isMobileOnly={true}
        totalProducts={total}
      />

      {/* Measurement modal */}
      <MeasurementModal
        isOpen={measurementModalOpen}
        onClose={() => setMeasurementModalOpen(false)}
      />
    </div>
  );
}

export default function CatalogPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-gray-50">
        <div className="bg-white border-b border-gray-200 py-6">
          <div className="max-w-[1440px] mx-auto px-4">
            <div className="h-8 w-48 bg-gray-200 rounded animate-pulse" />
          </div>
        </div>
        <div className="flex justify-center py-16">
          <div className="w-10 h-10 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
        </div>
      </div>
    }>
      <CatalogPageContent />
    </Suspense>
  );
}
