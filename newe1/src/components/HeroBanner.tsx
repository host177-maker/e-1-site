'use client';

import Link from 'next/link';
import { useState, useEffect } from 'react';

const slides = [
  {
    id: 1,
    title: 'Шкафы-купе на заказ',
    subtitle: 'Индивидуальный дизайн под ваш интерьер',
    description: 'Производство шкафов-купе любой сложности с гарантией 5 лет',
    buttonText: 'Рассчитать стоимость',
    buttonLink: '/calculator',
    bgColor: 'from-gray-900 to-gray-700',
  },
  {
    id: 2,
    title: 'Гардеробные системы',
    subtitle: 'Максимум функциональности',
    description: 'Эргономичные решения для хранения вашей одежды',
    buttonText: 'Смотреть каталог',
    buttonLink: '/catalog/garderobnye',
    bgColor: 'from-primary to-primary-dark',
  },
  {
    id: 3,
    title: 'Скидка 20% на все',
    subtitle: 'Только до конца месяца',
    description: 'Успейте заказать мебель по выгодной цене',
    buttonText: 'Узнать подробнее',
    buttonLink: '/sales',
    bgColor: 'from-gray-800 to-gray-600',
  },
];

export default function HeroBanner() {
  const [currentSlide, setCurrentSlide] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 5000);
    return () => clearInterval(timer);
  }, []);

  const slide = slides[currentSlide];

  return (
    <section className={`relative bg-gradient-to-r ${slide.bgColor} text-white transition-all duration-500`}>
      <div className="container-custom py-16 md:py-24 lg:py-32">
        <div className="max-w-2xl">
          <span className="inline-block px-4 py-1 bg-white/20 rounded-full text-sm mb-4">
            {slide.subtitle}
          </span>
          <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 text-white">
            {slide.title}
          </h1>
          <p className="text-lg md:text-xl mb-8 text-white/80">
            {slide.description}
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Link
              href={slide.buttonLink}
              className="btn-primary inline-block text-center"
            >
              {slide.buttonText}
            </Link>
            <Link
              href="/catalog"
              className="inline-block px-6 py-3 border-2 border-white text-white rounded hover:bg-white hover:text-gray-900 transition-colors text-center"
            >
              Весь каталог
            </Link>
          </div>
        </div>
      </div>

      {/* Slide indicators */}
      <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
        {slides.map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentSlide(index)}
            className={`w-3 h-3 rounded-full transition-all ${
              index === currentSlide ? 'bg-white w-8' : 'bg-white/50'
            }`}
            aria-label={`Слайд ${index + 1}`}
          />
        ))}
      </div>

      {/* Navigation arrows */}
      <button
        onClick={() => setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length)}
        className="absolute left-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 rounded-full hover:bg-white/40 transition-colors"
        aria-label="Предыдущий слайд"
      >
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button
        onClick={() => setCurrentSlide((prev) => (prev + 1) % slides.length)}
        className="absolute right-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 rounded-full hover:bg-white/40 transition-colors"
        aria-label="Следующий слайд"
      >
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </section>
  );
}
