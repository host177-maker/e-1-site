'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { useWishlist } from '@/context/WishlistContext';

interface CatalogProduct {
  id: number;
  name: string;
  slug: string;
  series_name?: string;
  door_type_name?: string;
  default_image?: string;
}

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

export default function TopSalesSection() {
  const [products, setProducts] = useState<CatalogProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const { isInWishlist, addToWishlist, removeByProductId } = useWishlist();

  useEffect(() => {
    const fetchProducts = async () => {
      try {
        // Загружаем 12 товаров (3 ряда по 4 на десктопе)
        const res = await fetch('/api/catalog?limit=12&offset=0');
        const data = await res.json();
        if (data.success && data.products) {
          setProducts(data.products);
        }
      } catch (error) {
        console.error('Error fetching top sales:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, []);

  if (loading) {
    return (
      <section className="py-8 bg-[#f5f5f5]">
        <div className="container-custom">
          <h2 className="text-2xl font-bold text-gray-900 mb-6">Топ продаж</h2>
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            {[...Array(12)].map((_, i) => (
              <div key={i} className="bg-white rounded-lg p-3 animate-pulse">
                <div className="aspect-square bg-gray-200 rounded mb-2"></div>
                <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div className="h-4 bg-gray-200 rounded w-1/2"></div>
              </div>
            ))}
          </div>
        </div>
      </section>
    );
  }

  if (products.length === 0) {
    return null;
  }

  return (
    <section className="py-8 bg-[#f5f5f5]">
      <div className="container-custom">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">Топ продаж</h2>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          {products.map((product) => {
            const inWishlist = isInWishlist(product.id);

            return (
              <div key={product.id} className="group bg-white rounded-lg p-3 hover:shadow-md transition-shadow relative">
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
            );
          })}
        </div>

        {/* Кнопка "Перейти в каталог" */}
        <div className="mt-8 text-center">
          <Link
            href="/catalog"
            className="inline-flex items-center gap-2 px-8 py-3 bg-[#62bb46] text-white font-bold rounded-lg hover:bg-[#55a83d] transition-colors"
          >
            Перейти в каталог
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </Link>
        </div>
      </div>
    </section>
  );
}
