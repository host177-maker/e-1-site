'use client';

import { Suspense, useEffect, useState, useCallback, useRef } from 'react';
import { useSearchParams } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { useCity } from '@/context/CityContext';

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

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

function CatalogPageContent() {
  const searchParams = useSearchParams();
  const isInitialLoad = useRef(true);
  const { city } = useCity();

  // Читаем начальные параметры из URL только один раз
  const initialUrlParams = useRef<{
    series: string | null;
  } | null>(null);

  if (initialUrlParams.current === null && searchParams) {
    initialUrlParams.current = {
      series: searchParams.get('series'),
    };
  }

  const [products, setProducts] = useState<CatalogProduct[]>([]);
  const [series, setSeries] = useState<CatalogSeries[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [selectedSeries, setSelectedSeries] = useState<string>(initialUrlParams.current?.series || '');
  const [total, setTotal] = useState(0);

  // Responsive limit: 20 for desktop, 12 for mobile
  const [limit, setLimit] = useState(20);

  useEffect(() => {
    const updateLimit = () => {
      setLimit(window.innerWidth >= 1024 ? 20 : 12);
    };
    updateLimit();
    window.addEventListener('resize', updateLimit);
    return () => window.removeEventListener('resize', updateLimit);
  }, []);

  const fetchProducts = useCallback(async (append = false) => {
    if (append) {
      setLoadingMore(true);
    } else {
      setLoading(true);
    }
    try {
      const params = new URLSearchParams();
      if (selectedSeries) params.set('series', selectedSeries);
      params.set('limit', limit.toString());
      params.set('offset', append ? products.length.toString() : '0');

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
      }
    } catch (error) {
      console.error('Error fetching catalog:', error);
    } finally {
      setLoading(false);
      setLoadingMore(false);
      isInitialLoad.current = false;
    }
  }, [selectedSeries, limit, products.length]);

  useEffect(() => {
    fetchProducts(false);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedSeries, limit]);

  // Обновляем URL при изменении фильтров (без перезагрузки страницы)
  useEffect(() => {
    if (isInitialLoad.current) return;

    const params = new URLSearchParams();
    if (selectedSeries) {
      params.set('series', selectedSeries);
    }

    const queryString = params.toString();
    const newUrl = queryString ? `/catalog?${queryString}` : '/catalog';
    window.history.replaceState(null, '', newUrl);
  }, [selectedSeries]);

  const handleSeriesChange = (slug: string) => {
    setSelectedSeries(slug);
  };

  const handleLoadMore = () => {
    fetchProducts(true);
  };

  const hasMore = products.length < total;

  // Get title based on selected series
  const getPageTitle = () => {
    if (selectedSeries) {
      const selectedSeriesData = series.find(s => s.slug === selectedSeries);
      if (selectedSeriesData) {
        return `${selectedSeriesData.name} в г. ${city.name}`;
      }
    }
    return `Шкафы в г. ${city.name}`;
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Simple header */}
      <div className="bg-white border-b border-gray-200 py-6">
        <div className="max-w-[1348px] mx-auto px-4">
          <h1 className="text-2xl md:text-3xl font-bold text-gray-900">{getPageTitle()}</h1>
        </div>
      </div>

      <div className="max-w-[1348px] mx-auto px-4 py-8">
        {/* Filters */}
        <div className="bg-white rounded-xl shadow-sm p-4 mb-8">
          <div className="flex flex-wrap gap-2">
            <button
              onClick={() => handleSeriesChange('')}
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                selectedSeries === ''
                  ? 'bg-[#62bb46] text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              Все серии
            </button>
            {series.map((s) => (
              <button
                key={s.id}
                onClick={() => handleSeriesChange(s.slug)}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  selectedSeries === s.slug
                    ? 'bg-[#62bb46] text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                {s.name}
              </button>
            ))}
          </div>
        </div>

        {/* Results count */}
        <div className="mb-6 text-gray-600">
          Найдено товаров: <strong>{total}</strong>
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
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
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
                  <h3 className="text-xs text-gray-700 leading-tight group-hover:text-[#62bb46] transition-colors line-clamp-2 mb-2">
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

        {/* Load more button */}
        {hasMore && !loading && (
          <div className="flex justify-center mt-8">
            <button
              onClick={handleLoadMore}
              disabled={loadingMore}
              className="px-8 py-3 bg-white border-2 border-[#62bb46] text-[#62bb46] font-medium rounded-lg hover:bg-[#62bb46] hover:text-white transition-colors disabled:opacity-50"
            >
              {loadingMore ? (
                <span className="flex items-center gap-2">
                  <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
                  Загрузка...
                </span>
              ) : (
                `Показать ещё (${total - products.length})`
              )}
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

export default function CatalogPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-gray-50">
        <div className="bg-white border-b border-gray-200 py-6">
          <div className="max-w-[1348px] mx-auto px-4">
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
