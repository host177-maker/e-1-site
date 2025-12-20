'use client';

import Image from 'next/image';
import Link from 'next/link';
import { useState, useEffect, useCallback } from 'react';

const banners = [
  {
    id: 1,
    image: '/images/banners/banner-50.jpg',
    alt: '-50% на весь ассортимент - Новогоднее чудо с Е1!',
    link: '/sales',
  },
  {
    id: 2,
    image: '/images/banners/banner-loker.jpg',
    alt: 'Серия Локер в декоре Белый снег -60%',
    link: '/catalog/loker',
  },
  {
    id: 3,
    image: '/images/banners/banner-express.jpg',
    alt: 'Серия Экспресс, фасад Зеркало -60%',
    link: '/catalog/express',
  },
];

export default function HeroBanner() {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isAutoPlay, setIsAutoPlay] = useState(true);

  const nextSlide = useCallback(() => {
    setCurrentSlide((prev) => (prev + 1) % banners.length);
  }, []);

  const prevSlide = useCallback(() => {
    setCurrentSlide((prev) => (prev - 1 + banners.length) % banners.length);
  }, []);

  useEffect(() => {
    if (!isAutoPlay) return;

    const timer = setInterval(() => {
      nextSlide();
    }, 5000);

    return () => clearInterval(timer);
  }, [isAutoPlay, nextSlide]);

  const handleMouseEnter = () => setIsAutoPlay(false);
  const handleMouseLeave = () => setIsAutoPlay(true);

  return (
    <section className="relative bg-[#f5f5f5]">
      <div
        className="relative overflow-hidden"
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
      >
        {/* Slides container */}
        <div
          className="flex transition-transform duration-500 ease-out"
          style={{ transform: `translateX(-${currentSlide * 100}%)` }}
        >
          {banners.map((banner) => (
            <div key={banner.id} className="w-full flex-shrink-0">
              <Link href={banner.link} className="block relative">
                <div className="relative w-full" style={{ paddingBottom: '37.5%' }}>
                  <Image
                    src={banner.image}
                    alt={banner.alt}
                    fill
                    className="object-cover object-center"
                    priority={banner.id === 1}
                    sizes="100vw"
                  />
                </div>
              </Link>
            </div>
          ))}
        </div>

        {/* Navigation arrows */}
        <button
          onClick={prevSlide}
          className="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all z-10 group"
          aria-label="Предыдущий слайд"
        >
          <svg className="w-6 h-6 text-[#3d4543] group-hover:text-[#62bb46] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <button
          onClick={nextSlide}
          className="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all z-10 group"
          aria-label="Следующий слайд"
        >
          <svg className="w-6 h-6 text-[#3d4543] group-hover:text-[#62bb46] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
          </svg>
        </button>

        {/* Dots navigation */}
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
          {banners.map((_, index) => (
            <button
              key={index}
              onClick={() => setCurrentSlide(index)}
              className={`h-3 rounded-full transition-all ${
                index === currentSlide
                  ? 'bg-[#62bb46] w-8'
                  : 'bg-white/70 hover:bg-white w-3'
              }`}
              aria-label={`Слайд ${index + 1}`}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
