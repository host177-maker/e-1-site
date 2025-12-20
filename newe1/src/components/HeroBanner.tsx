'use client';

import Link from 'next/link';
import { useState, useEffect } from 'react';

// E1 Logo for banner
function E1LogoSmall() {
  return (
    <svg viewBox="0 0 80 56" className="h-8 w-auto inline-block ml-2">
      <g fill="#62BB46">
        <polygon points="76.82,9.79 76.82,51.74 65.78,51.74 65.78,15.47 69.73,15.47 58.69,9.94 58.69,4.26 65.78,4.26" />
        <polygon points="47.48,15.47 11.05,15.47 0,10.03 0,4.26 36.43,4.26 47.48,9.79" />
        <polygon points="11.06,15.47 11.06,22.4 36.43,22.4 47.48,27.92 47.48,33.6 11.22,33.6 0,28.08 0,15.47" />
        <polygon points="47.48,46.06 47.48,51.74 11.05,51.74 0,46.22 0,33.6 11.22,33.6 11.22,40.53 36.43,40.53" />
      </g>
    </svg>
  );
}

const slides = [
  {
    id: 1,
    title: 'НОВЫЙ ГОД – НОВЫЙ ШКАФ!',
    subtitle: 'Новогодние скидки в',
    hasLogo: true,
    discount: '-60%',
    description: 'Серия Экспресс, фасад Зеркало',
    details: 'В декорах Белый снег, Бетон, Крафт Табачный',
    date: '1-28 декабря',
    buttonText: 'СМОТРЕТЬ',
    buttonLink: '/sales',
  },
  {
    id: 2,
    title: 'ГАРДЕРОБНЫЕ СИСТЕМЫ',
    subtitle: 'Функциональные решения',
    discount: '-40%',
    description: 'Системы хранения на заказ',
    details: 'Индивидуальный подход к каждому клиенту',
    buttonText: 'ПОДРОБНЕЕ',
    buttonLink: '/catalog/garderobnye',
  },
  {
    id: 3,
    title: 'ШКАФЫ-КУПЕ НА ЗАКАЗ',
    subtitle: 'Производство в Москве',
    discount: '-30%',
    description: 'Любые размеры и наполнение',
    details: 'Гарантия до 10 лет',
    buttonText: 'ЗАКАЗАТЬ',
    buttonLink: '/catalog/shkafi-kupe',
  },
];

export default function HeroBanner() {
  const [currentSlide, setCurrentSlide] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 6000);
    return () => clearInterval(timer);
  }, []);

  const slide = slides[currentSlide];

  return (
    <section className="relative bg-[#f5f5f5]">
      <div className="container-custom">
        <div className="relative bg-white rounded-lg overflow-hidden shadow-sm">
          <div className="grid grid-cols-1 lg:grid-cols-2 min-h-[400px] lg:min-h-[500px]">
            {/* Left side - Content */}
            <div className="p-8 lg:p-12 flex flex-col justify-center">
              <h1 className="text-3xl lg:text-4xl xl:text-5xl font-bold text-text-dark mb-4">
                {slide.title}
              </h1>
              <div className="text-xl lg:text-2xl text-text-dark mb-6 italic font-light">
                {slide.subtitle}
                {slide.hasLogo && <E1LogoSmall />}
              </div>

              <div className="flex items-start gap-6 mb-6">
                {/* Discount badge */}
                <div className="text-[#62bb46] text-6xl lg:text-7xl font-bold leading-none">
                  {slide.discount}
                </div>
                <div className="border-l-2 border-[#62bb46] pl-4">
                  <div className="font-bold text-lg text-text-dark">{slide.description}</div>
                  <div className="text-text-medium text-sm">{slide.details}</div>
                  {slide.date && (
                    <div className="text-text-dark font-medium mt-1">{slide.date}</div>
                  )}
                </div>
              </div>

              <div className="mb-6">
                <Link
                  href={slide.buttonLink}
                  className="inline-block px-8 py-4 bg-[#62bb46] text-white font-bold rounded hover:bg-[#75c35c] transition-colors uppercase tracking-wide"
                >
                  {slide.buttonText}
                </Link>
              </div>

              {slide.id === 1 && (
                <div className="text-xs text-text-muted leading-relaxed">
                  Акция действует с 01.12.25 по 28.12.25,<br />
                  на шкафы серии «Экспресс» 2-дв. фасад зеркало<br />
                  в декоре «Белый снег» и «Бетон», 3-дв. фасад<br />
                  зеркало в декоре «Крафт Табачный».<br />
                  Рекламодатель: ООО «Е1-ЮГ» г. Белореченск
                </div>
              )}
            </div>

            {/* Right side - Image placeholder */}
            <div className="relative bg-gradient-to-br from-gray-100 to-gray-200 hidden lg:flex items-center justify-center">
              <div className="text-center text-text-muted">
                <svg className="w-24 h-24 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p className="text-sm">Изображение шкафа</p>
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
                  index === currentSlide ? 'bg-[#62bb46] w-6' : 'bg-gray-300 hover:bg-gray-400'
                }`}
                aria-label={`Слайд ${index + 1}`}
              />
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
