'use client';

import { useState } from 'react';
import Link from 'next/link';

const reviews = [
  {
    id: 1,
    name: 'Анна Смирнова',
    date: '15 декабря 2024',
    rating: 5,
    text: 'Заказывала шкаф-купе в спальню. Очень довольна результатом! Замерщик приехал вовремя, помог определиться с наполнением. Шкаф сделали за 2 недели, установили быстро и аккуратно.',
    product: 'Шкаф-купе "Модерн"',
  },
  {
    id: 2,
    name: 'Михаил Петров',
    date: '10 декабря 2024',
    rating: 5,
    text: 'Отличная компания! Заказывал гардеробную комнату. Дизайнер предложил несколько вариантов, выбрали оптимальный. Качество материалов на высоте, фурнитура работает отлично.',
    product: 'Гардеробная система',
  },
  {
    id: 3,
    name: 'Елена Козлова',
    date: '5 декабря 2024',
    rating: 4,
    text: 'Хороший шкаф за разумные деньги. Немного задержали доставку на пару дней, но в остальном всё отлично. Рекомендую!',
    product: 'Встроенный шкаф',
  },
  {
    id: 4,
    name: 'Дмитрий Волков',
    date: '1 декабря 2024',
    rating: 5,
    text: 'Уже второй раз обращаюсь в эту компанию. Первый раз заказывал шкаф в детскую, теперь в прихожую. Качество неизменно высокое!',
    product: 'Прихожая "Классик"',
  },
];

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

export default function ReviewsSection() {
  const [visibleCount, setVisibleCount] = useState(3);

  return (
    <section className="py-16 md:py-20">
      <div className="container-custom">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
          <div>
            <h2 className="text-3xl md:text-4xl font-bold mb-4">Отзывы клиентов</h2>
            <p className="text-text-medium">
              Узнайте, что говорят о нас наши клиенты
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

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {reviews.slice(0, visibleCount).map((review) => (
            <div
              key={review.id}
              className="bg-white border border-border rounded-xl p-6 hover:shadow-md transition-shadow"
            >
              <div className="flex items-start justify-between mb-4">
                <div>
                  <div className="font-bold text-lg">{review.name}</div>
                  <div className="text-text-muted text-sm">{review.date}</div>
                </div>
                <StarRating rating={review.rating} />
              </div>
              <p className="text-text-medium mb-4">{review.text}</p>
              <div className="pt-4 border-t border-border-light">
                <span className="text-sm text-text-muted">Товар: </span>
                <span className="text-sm font-medium">{review.product}</span>
              </div>
            </div>
          ))}
        </div>

        {visibleCount < reviews.length && (
          <div className="text-center mt-8">
            <button
              onClick={() => setVisibleCount(reviews.length)}
              className="btn-primary"
            >
              Показать ещё
            </button>
          </div>
        )}

        {/* Review CTA */}
        <div className="mt-12 bg-bg-gray rounded-xl p-8 text-center">
          <h3 className="text-2xl font-bold mb-4">Оставьте свой отзыв</h3>
          <p className="text-text-medium mb-6 max-w-xl mx-auto">
            Мы ценим мнение каждого клиента. Поделитесь своим опытом сотрудничества с нами.
          </p>
          <button className="btn-primary">
            Написать отзыв
          </button>
        </div>
      </div>
    </section>
  );
}
