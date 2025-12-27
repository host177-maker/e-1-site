'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';

interface Review {
  id: number;
  name: string;
  review_text: string;
  company_response: string | null;
  rating: number | null;
  created_at: string;
}

function StarRating({ rating }: { rating: number }) {
  return (
    <div className="flex gap-1">
      {[1, 2, 3, 4, 5].map((star) => (
        <svg
          key={star}
          className={`w-5 h-5 ${star <= rating ? 'text-yellow-400' : 'text-gray-300'}`}
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      ))}
    </div>
  );
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

// Fallback reviews in case API is unavailable
const fallbackReviews: Review[] = [
  {
    id: 1,
    name: 'Анна Смирнова',
    created_at: '2024-12-15T00:00:00Z',
    rating: 5,
    review_text: 'Заказывала шкаф-купе в спальню. Очень довольна результатом! Замерщик приехал вовремя, помог определиться с наполнением. Шкаф сделали за 2 недели, установили быстро и аккуратно.',
    company_response: null,
  },
  {
    id: 2,
    name: 'Михаил Петров',
    created_at: '2024-12-10T00:00:00Z',
    rating: 5,
    review_text: 'Отличная компания! Заказывал гардеробную комнату. Дизайнер предложил несколько вариантов, выбрали оптимальный. Качество материалов на высоте, фурнитура работает отлично.',
    company_response: null,
  },
  {
    id: 3,
    name: 'Елена Козлова',
    created_at: '2024-12-05T00:00:00Z',
    rating: 4,
    review_text: 'Хороший шкаф за разумные деньги. Немного задержали доставку на пару дней, но в остальном всё отлично. Рекомендую!',
    company_response: null,
  },
];

export default function ReviewsSection() {
  const [reviews, setReviews] = useState<Review[]>(fallbackReviews);
  const [loading, setLoading] = useState(true);
  const [visibleCount, setVisibleCount] = useState(3);
  const [stats, setStats] = useState<{ averageRating: number | null; totalRated: number }>({
    averageRating: null,
    totalRated: 0,
  });

  useEffect(() => {
    fetch('/api/reviews?limit=6')
      .then((res) => res.json())
      .then((data) => {
        if (data.success && data.data.length > 0) {
          setReviews(data.data);
          setStats(data.stats);
        }
      })
      .catch((error) => {
        console.error('Error fetching reviews:', error);
        // Keep fallback reviews
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  return (
    <section className="py-16 md:py-20">
      <div className="container-custom">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
          <div>
            <h2 className="text-3xl md:text-4xl font-bold mb-4">Отзывы клиентов</h2>
            <p className="text-text-medium">
              Узнайте, что говорят о нас наши клиенты
              {stats.averageRating && (
                <span className="ml-2">
                  · Средняя оценка{' '}
                  <span className="text-yellow-500 font-bold">{stats.averageRating.toFixed(1)}</span>
                </span>
              )}
            </p>
          </div>
          <Link
            href="/reviews"
            className="text-primary hover:text-primary-hover font-medium inline-flex items-center gap-2"
          >
            Все отзывы
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </Link>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {reviews.slice(0, visibleCount).map((review) => (
              <div
                key={review.id}
                className="bg-white border border-border rounded-xl p-6 hover:shadow-md transition-shadow"
              >
                <div className="flex items-start justify-between mb-4">
                  <div>
                    <div className="font-bold text-lg">{review.name}</div>
                    <div className="text-text-muted text-sm">{formatDate(review.created_at)}</div>
                  </div>
                  {review.rating && <StarRating rating={review.rating} />}
                </div>
                <p className="text-text-medium mb-4 line-clamp-4">{review.review_text}</p>
                {review.company_response && (
                  <div className="pt-4 border-t border-border-light">
                    <div className="flex items-center gap-2 mb-2">
                      <div className="w-6 h-6 bg-[#7cb342] rounded-full flex items-center justify-center">
                        <svg className="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                          <path
                            fillRule="evenodd"
                            d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                            clipRule="evenodd"
                          />
                        </svg>
                      </div>
                      <span className="text-sm font-medium text-[#3d4543]">Ответ компании</span>
                    </div>
                    <p className="text-sm text-gray-600 line-clamp-2">{review.company_response}</p>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}

        {visibleCount < reviews.length && (
          <div className="text-center mt-8">
            <button onClick={() => setVisibleCount(reviews.length)} className="btn-primary">
              Показать ещё
            </button>
          </div>
        )}
      </div>
    </section>
  );
}
