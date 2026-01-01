'use client';

import { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import Image from 'next/image';

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

export default function CatalogPage() {
  const [products, setProducts] = useState<CatalogProduct[]>([]);
  const [series, setSeries] = useState<CatalogSeries[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedSeries, setSelectedSeries] = useState<string>('');
  const [total, setTotal] = useState(0);
  const [offset, setOffset] = useState(0);
  const limit = 12;

  const fetchProducts = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (selectedSeries) params.set('series', selectedSeries);
      params.set('limit', limit.toString());
      params.set('offset', offset.toString());

      const response = await fetch(`/api/catalog?${params}`);
      const data = await response.json();

      if (data.success) {
        setProducts(data.products);
        setSeries(data.series);
        setTotal(data.total);
      }
    } catch (error) {
      console.error('Error fetching catalog:', error);
    } finally {
      setLoading(false);
    }
  }, [selectedSeries, offset]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const handleSeriesChange = (slug: string) => {
    setSelectedSeries(slug);
    setOffset(0);
  };

  const totalPages = Math.ceil(total / limit);
  const currentPage = Math.floor(offset / limit) + 1;

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero section */}
      <div className="bg-gradient-to-r from-[#62bb46] to-[#4a9935] text-white py-12">
        <div className="max-w-[1348px] mx-auto px-4">
          <h1 className="text-3xl md:text-4xl font-bold mb-2">Каталог шкафов-купе</h1>
          <p className="text-white/80 text-lg">Выберите идеальный шкаф для вашего интерьера</p>
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
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {products.map((product) => (
              <Link
                key={product.id}
                href={`/product/${product.slug}`}
                className="bg-white rounded-xl shadow-sm overflow-hidden group hover:shadow-lg transition-shadow"
              >
                <div className="aspect-[4/3] relative bg-gray-100">
                  <Image
                    src={product.default_image || PLACEHOLDER_IMAGE}
                    alt={product.name}
                    fill
                    className="object-contain p-4 group-hover:scale-105 transition-transform"
                    onError={(e) => {
                      const target = e.target as HTMLImageElement;
                      target.src = PLACEHOLDER_IMAGE;
                    }}
                  />
                </div>
                <div className="p-4">
                  <div className="text-xs text-[#62bb46] font-medium mb-1">
                    {product.series_name}
                  </div>
                  <h3 className="font-medium text-gray-900 mb-2 line-clamp-2 group-hover:text-[#62bb46] transition-colors">
                    {product.name}
                  </h3>
                  <div className="flex items-center justify-between text-sm text-gray-500">
                    {product.door_type_name && (
                      <span>{product.door_type_name}</span>
                    )}
                    {product.variants_count && product.variants_count > 1 && (
                      <span className="text-[#62bb46]">{product.variants_count} вариантов</span>
                    )}
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="flex justify-center gap-2 mt-8">
            <button
              onClick={() => setOffset(Math.max(0, offset - limit))}
              disabled={offset === 0}
              className="px-4 py-2 rounded-lg bg-white shadow-sm border border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <div className="flex items-center gap-2">
              {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                let pageNum;
                if (totalPages <= 5) {
                  pageNum = i + 1;
                } else if (currentPage <= 3) {
                  pageNum = i + 1;
                } else if (currentPage >= totalPages - 2) {
                  pageNum = totalPages - 4 + i;
                } else {
                  pageNum = currentPage - 2 + i;
                }

                return (
                  <button
                    key={i}
                    onClick={() => setOffset((pageNum - 1) * limit)}
                    className={`w-10 h-10 rounded-lg font-medium transition-colors ${
                      currentPage === pageNum
                        ? 'bg-[#62bb46] text-white'
                        : 'bg-white shadow-sm border border-gray-200 hover:bg-gray-50'
                    }`}
                  >
                    {pageNum}
                  </button>
                );
              })}
            </div>

            <button
              onClick={() => setOffset(Math.min((totalPages - 1) * limit, offset + limit))}
              disabled={currentPage >= totalPages}
              className="px-4 py-2 rounded-lg bg-white shadow-sm border border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
