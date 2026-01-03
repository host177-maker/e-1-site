'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

interface Promotion {
  id: number;
  title: string;
  slug: string;
  content: string;
  images: string[];
  start_date: string;
  end_date: string;
  published_at: string | null;
  created_at: string;
}

interface PromotionsData {
  data: Promotion[];
  pagination: {
    total: number;
    limit: number;
    offset: number;
    hasMore: boolean;
  };
}

function formatDateRange(startDate: string, endDate: string): string {
  const start = new Date(startDate);
  const end = new Date(endDate);

  const startStr = start.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
  });

  const endStr = end.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });

  return `${startStr} — ${endStr}`;
}

function PromotionCard({ promotion }: { promotion: Promotion }) {
  const [showFullText, setShowFullText] = useState(false);
  const [lightboxPhoto, setLightboxPhoto] = useState<string | null>(null);

  const isLongText = promotion.content.length > 300;
  const displayText = showFullText ? promotion.content : promotion.content.slice(0, 300);

  return (
    <div className="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
      {/* Images */}
      {promotion.images && promotion.images.length > 0 && (
        <div className="relative h-48 bg-gray-100">
          <img
            src={promotion.images[0]}
            alt={promotion.title}
            className="w-full h-full object-cover"
          />
          {promotion.images.length > 1 && (
            <div className="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
              +{promotion.images.length - 1} фото
            </div>
          )}
        </div>
      )}

      <div className="p-6">
        {/* Date badge */}
        <div className="flex items-center gap-2 mb-3">
          <span className="inline-flex items-center gap-1 px-2 py-1 bg-[#7cb342]/10 text-[#7cb342] text-sm rounded">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {formatDateRange(promotion.start_date, promotion.end_date)}
          </span>
        </div>

        {/* Title */}
        <h2 className="text-xl font-bold text-[#3d4543] mb-3">{promotion.title}</h2>

        {/* Content */}
        <div className="text-gray-700 mb-4">
          <p className="whitespace-pre-wrap">
            {displayText}
            {isLongText && !showFullText && '...'}
          </p>
          {isLongText && (
            <button
              onClick={() => setShowFullText(!showFullText)}
              className="text-[#7cb342] hover:underline mt-2"
            >
              {showFullText ? 'Свернуть' : 'Читать полностью'}
            </button>
          )}
        </div>

        {/* All photos (if more than one) */}
        {promotion.images && promotion.images.length > 1 && showFullText && (
          <div className="flex flex-wrap gap-2 mb-4">
            {promotion.images.map((photo, index) => (
              <button
                key={index}
                onClick={() => setLightboxPhoto(photo)}
                className="relative w-20 h-20 rounded-lg overflow-hidden hover:opacity-80 transition-opacity"
              >
                <img src={photo} alt={`Фото ${index + 1}`} className="w-full h-full object-cover" />
              </button>
            ))}
          </div>
        )}

      </div>

      {/* Lightbox */}
      {lightboxPhoto && (
        <div
          className="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4"
          onClick={() => setLightboxPhoto(null)}
        >
          <div className="relative max-w-4xl max-h-[90vh]">
            <img
              src={lightboxPhoto}
              alt="Увеличенное фото"
              className="max-w-full max-h-[90vh] object-contain"
            />
            <button
              onClick={() => setLightboxPhoto(null)}
              className="absolute top-4 right-4 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/40 transition-colors"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default function PromotionsPage() {
  const [promotionsData, setPromotionsData] = useState<PromotionsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);

  const fetchPromotions = async (offset = 0, append = false) => {
    try {
      const res = await fetch(`/api/sales?limit=10&offset=${offset}`);
      const data = await res.json();

      if (data.success) {
        if (append && promotionsData) {
          setPromotionsData({
            ...data,
            data: [...promotionsData.data, ...data.data],
          });
        } else {
          setPromotionsData(data);
        }
      }
    } catch (error) {
      console.error('Error fetching promotions:', error);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };

  useEffect(() => {
    fetchPromotions();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const loadMore = () => {
    if (!promotionsData || loadingMore) return;
    setLoadingMore(true);
    fetchPromotions(promotionsData.data.length, true);
  };

  return (
    <div className="bg-white font-[var(--font-open-sans)]">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-8">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543]">Акции и спецпредложения</h1>
        </div>
      </div>

      <div className="container-custom py-8">
        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : promotionsData && promotionsData.data.length > 0 ? (
          <>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {promotionsData.data.map((promotion) => (
                <PromotionCard key={promotion.id} promotion={promotion} />
              ))}
            </div>

            {promotionsData.pagination.hasMore && (
              <div className="text-center pt-8">
                <button
                  onClick={loadMore}
                  disabled={loadingMore}
                  className="px-8 py-3 bg-[#7cb342] text-white font-medium rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
                >
                  {loadingMore ? 'Загрузка...' : 'Показать ещё'}
                </button>
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-16">
            <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
              </svg>
            </div>
            <h2 className="text-xl font-bold text-gray-900 mb-2">Нет активных акций</h2>
            <p className="text-gray-500 mb-6">В данный момент нет активных акций. Следите за обновлениями!</p>
            <Link
              href="/"
              className="inline-flex items-center gap-2 px-6 py-3 bg-[#7cb342] text-white font-medium rounded-lg hover:bg-[#689f38] transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              На главную
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
