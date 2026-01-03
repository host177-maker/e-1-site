'use client';

import { Suspense, useEffect, useState, useCallback, useRef } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { useCity } from '@/context/CityContext';
import CatalogFilter from '@/components/CatalogFilter';

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
  doorTypes: { id: number; name: string; slug: string }[];
  series: { id: number; name: string; slug: string }[];
  widthRange: { min: number; max: number };
  heights: number[];
  depths: number[];
  priceRange: { min: number; max: number };
}

interface FilterValues {
  doorType: string;
  series: string;
  widthMin: number;
  widthMax: number;
  heights: number[];
  depths: number[];
  priceMin: number;
  priceMax: number;
}

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

const DEFAULT_FILTERS: FilterValues = {
  doorType: '',
  series: '',
  widthMin: 80,
  widthMax: 300,
  heights: [],
  depths: [],
  priceMin: 2000,
  priceMax: 170000,
};

function CatalogPageContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const isInitialLoad = useRef(true);
  const { city } = useCity();

  // Читаем параметры из URL при инициализации
  const getInitialFilters = useCallback((): FilterValues => {
    if (!searchParams) return DEFAULT_FILTERS;
    return {
      doorType: searchParams.get('doorType') || '',
      series: searchParams.get('series') || '',
      widthMin: parseInt(searchParams.get('widthMin') || '') || DEFAULT_FILTERS.widthMin,
      widthMax: parseInt(searchParams.get('widthMax') || '') || DEFAULT_FILTERS.widthMax,
      heights: searchParams.get('heights')?.split(',').map(h => parseInt(h)).filter(h => !isNaN(h)) || [],
      depths: searchParams.get('depths')?.split(',').map(d => parseInt(d)).filter(d => !isNaN(d)) || [],
      priceMin: parseInt(searchParams.get('priceMin') || '') || DEFAULT_FILTERS.priceMin,
      priceMax: parseInt(searchParams.get('priceMax') || '') || DEFAULT_FILTERS.priceMax,
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

  const limit = 20;

  const fetchProducts = useCallback(async (append = false, page = 1) => {
    if (append) {
      setLoadingMore(true);
    } else {
      setLoading(true);
    }
    try {
      const params = new URLSearchParams();
      if (filters.series) params.set('series', filters.series);
      if (filters.doorType) params.set('doorType', filters.doorType);
      if (filters.widthMin !== DEFAULT_FILTERS.widthMin) params.set('widthMin', filters.widthMin.toString());
      if (filters.widthMax !== DEFAULT_FILTERS.widthMax) params.set('widthMax', filters.widthMax.toString());
      if (filters.heights.length > 0) params.set('heights', filters.heights.join(','));
      if (filters.depths.length > 0) params.set('depths', filters.depths.join(','));
      if (filters.priceMin !== DEFAULT_FILTERS.priceMin) params.set('priceMin', filters.priceMin.toString());
      if (filters.priceMax !== DEFAULT_FILTERS.priceMax) params.set('priceMax', filters.priceMax.toString());

      params.set('limit', limit.toString());
      params.set('offset', ((page - 1) * limit).toString());

      // Запросим filterOptions только при первой загрузке
      if (!filterOptions) {
        params.set('includeFilters', 'true');
      }

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
    }
  }, [filters, filterOptions]);

  useEffect(() => {
    fetchProducts(false, currentPage);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filters, currentPage]);

  // Обновляем URL при изменении фильтров
  useEffect(() => {
    if (isInitialLoad.current) return;

    const params = new URLSearchParams();
    if (filters.doorType) params.set('doorType', filters.doorType);
    if (filters.series) params.set('series', filters.series);
    if (filters.widthMin !== DEFAULT_FILTERS.widthMin) params.set('widthMin', filters.widthMin.toString());
    if (filters.widthMax !== DEFAULT_FILTERS.widthMax) params.set('widthMax', filters.widthMax.toString());
    if (filters.heights.length > 0) params.set('heights', filters.heights.join(','));
    if (filters.depths.length > 0) params.set('depths', filters.depths.join(','));
    if (filters.priceMin !== DEFAULT_FILTERS.priceMin) params.set('priceMin', filters.priceMin.toString());
    if (filters.priceMax !== DEFAULT_FILTERS.priceMax) params.set('priceMax', filters.priceMax.toString());
    if (currentPage > 1) params.set('page', currentPage.toString());

    const queryString = params.toString();
    const newUrl = queryString ? `/catalog?${queryString}` : '/catalog';
    window.history.replaceState(null, '', newUrl);
  }, [filters, currentPage]);

  const handleFiltersChange = (newFilters: FilterValues) => {
    setFilters(newFilters);
    setCurrentPage(1);
  };

  const handleLoadMore = () => {
    const nextPage = Math.floor(products.length / limit) + 1;
    fetchProducts(true, nextPage + 1);
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const hasMore = products.length < total;
  const totalPages = Math.ceil(total / limit);

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
    if (filters.series) {
      const selectedSeries = series.find(s => s.slug === filters.series);
      if (selectedSeries) {
        return `${selectedSeries.name} в г. ${city.name}`;
      }
    }
    return `Шкафы в г. ${city.name}`;
  };

  // Count active filters
  const activeFiltersCount = [
    filters.doorType,
    filters.series,
    filters.heights.length > 0,
    filters.depths.length > 0,
    filterOptions && filters.widthMin !== filterOptions.widthRange.min,
    filterOptions && filters.widthMax !== filterOptions.widthRange.max,
    filterOptions && filters.priceMin !== filterOptions.priceRange.min,
    filterOptions && filters.priceMax !== filterOptions.priceRange.max,
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
          {/* Sidebar filter - desktop */}
          <div className="hidden lg:block w-64 flex-shrink-0">
            <CatalogFilter
              filterOptions={filterOptions}
              filters={filters}
              onFiltersChange={handleFiltersChange}
              isOpen={false}
              onClose={() => {}}
            />
          </div>

          {/* Main content */}
          <div className="flex-1 min-w-0">
            {/* Mobile filter button and results count */}
            <div className="mb-6 flex items-center justify-between gap-4">
              <div className="text-gray-600">
                Найдено товаров: <strong>{total}</strong>
              </div>
              <button
                onClick={() => setMobileFilterOpen(true)}
                className="lg:hidden flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium"
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
                {products.map((product) => (
                  <div key={product.id} className="group bg-white rounded-lg p-3 hover:shadow-md transition-shadow">
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
                    <button
                      onClick={(e) => {
                        e.preventDefault();
                        // TODO: Add to cart logic
                      }}
                      className="w-full py-2 bg-[#62bb46] text-white text-sm font-medium rounded hover:bg-[#55a83d] transition-colors"
                    >
                      Купить
                    </button>
                  </div>
                ))}
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

      {/* Mobile filter modal */}
      <CatalogFilter
        filterOptions={filterOptions}
        filters={filters}
        onFiltersChange={handleFiltersChange}
        isOpen={mobileFilterOpen}
        onClose={() => setMobileFilterOpen(false)}
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
