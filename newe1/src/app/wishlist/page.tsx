'use client';

import { useWishlist } from '@/context/WishlistContext';
import { useCart } from '@/context/CartContext';
import Link from 'next/link';
import Image from 'next/image';

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

export default function WishlistPage() {
  const { items, removeFromWishlist, clearWishlist } = useWishlist();
  const { addToCart } = useCart();

  const handleAddToCart = (item: typeof items[0]) => {
    addToCart({
      productId: item.productId,
      name: item.name,
      slug: item.slug,
      image: item.image,
      price: item.price,
      oldPrice: item.oldPrice,
      width: item.width,
      height: item.height,
      depth: item.depth,
      bodyColor: item.bodyColor,
      profileColor: item.profileColor,
      filling: item.filling,
      assemblyPrice: Math.round(item.price * 0.12),
    });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 py-6">
        <div className="max-w-[1348px] mx-auto px-4">
          <h1 className="text-2xl md:text-3xl font-bold text-gray-900">Избранное</h1>
        </div>
      </div>

      <div className="max-w-[1348px] mx-auto px-4 py-8">
        {items.length === 0 ? (
          <div className="bg-white rounded-xl shadow-sm p-12 text-center">
            <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </div>
            <h3 className="text-xl font-medium text-gray-900 mb-2">Список избранного пуст</h3>
            <p className="text-gray-500 mb-6">Добавляйте товары в избранное, чтобы не потерять их</p>
            <Link
              href="/catalog"
              className="inline-block px-6 py-3 bg-[#62bb46] text-white font-medium rounded-lg hover:bg-[#55a83d] transition-colors"
            >
              Перейти в каталог
            </Link>
          </div>
        ) : (
          <>
            {/* Actions */}
            <div className="flex justify-between items-center mb-6">
              <p className="text-gray-600">
                Товаров: <strong>{items.length}</strong>
              </p>
              <button
                onClick={clearWishlist}
                className="text-sm text-red-500 hover:text-red-600 transition-colors"
              >
                Очистить все
              </button>
            </div>

            {/* Items grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {items.map((item) => (
                <div key={item.id} className="bg-white rounded-xl shadow-sm p-4">
                  <div className="flex gap-4">
                    {/* Image */}
                    <Link href={`/product/${item.slug}`} className="flex-shrink-0">
                      <div className="w-24 h-24 relative">
                        <Image
                          src={item.image || PLACEHOLDER_IMAGE}
                          alt={item.name}
                          fill
                          className="object-contain"
                          onError={(e) => {
                            const target = e.target as HTMLImageElement;
                            target.src = PLACEHOLDER_IMAGE;
                          }}
                        />
                      </div>
                    </Link>

                    {/* Info */}
                    <div className="flex-1 min-w-0">
                      <Link href={`/product/${item.slug}`}>
                        <h3 className="text-sm font-normal text-gray-900 hover:text-[#62bb46] transition-colors line-clamp-2 mb-2">
                          {item.name}
                        </h3>
                      </Link>

                      {/* Params */}
                      {(item.width || item.height || item.depth) && (
                        <p className="text-xs text-gray-500 mb-1">
                          Размер: {item.width}×{item.height}×{item.depth} мм
                        </p>
                      )}
                      {item.bodyColor && (
                        <p className="text-xs text-gray-500 mb-1">
                          Цвет корпуса: {item.bodyColor}
                        </p>
                      )}
                      {item.filling && (
                        <p className="text-xs text-gray-500 mb-1">
                          Наполнение: {item.filling}
                        </p>
                      )}

                      {/* Price */}
                      <div className="flex items-baseline gap-2 mt-2">
                        <span className="text-lg font-bold text-gray-900">
                          {item.price.toLocaleString('ru-RU')} ₽
                        </span>
                        {item.oldPrice && (
                          <span className="text-sm text-gray-400 line-through">
                            {item.oldPrice.toLocaleString('ru-RU')} ₽
                          </span>
                        )}
                      </div>
                    </div>

                    {/* Remove button */}
                    <button
                      onClick={() => removeFromWishlist(item.id)}
                      className="p-1 text-gray-400 hover:text-red-500 transition-colors self-start"
                      title="Удалить из избранного"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>

                  {/* Add to cart button */}
                  <button
                    onClick={() => handleAddToCart(item)}
                    className="w-full mt-4 py-2 bg-[#62bb46] text-white font-medium rounded-lg hover:bg-[#55a83d] transition-colors"
                  >
                    В корзину
                  </button>
                </div>
              ))}
            </div>
          </>
        )}
      </div>
    </div>
  );
}
