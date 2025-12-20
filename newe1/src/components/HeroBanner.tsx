'use client';

import Link from 'next/link';
import { useState, useEffect } from 'react';

// E1 Logo for banner
function E1LogoSmall() {
  return (
    <svg viewBox="0 0 80 56" className="h-10 w-auto inline-block ml-2 align-middle">
      <g fill="#62BB46">
        <polygon points="76.82,9.79 76.82,51.74 65.78,51.74 65.78,15.47 69.73,15.47 58.69,9.94 58.69,4.26 65.78,4.26" />
        <polygon points="47.48,15.47 11.05,15.47 0,10.03 0,4.26 36.43,4.26 47.48,9.79" />
        <polygon points="11.06,15.47 11.06,22.4 36.43,22.4 47.48,27.92 47.48,33.6 11.22,33.6 0,28.08 0,15.47" />
        <polygon points="47.48,46.06 47.48,51.74 11.05,51.74 0,46.22 0,33.6 11.22,33.6 11.22,40.53 36.43,40.53" />
      </g>
    </svg>
  );
}

// Santa Hat SVG for the -60% badge
function SantaHat() {
  return (
    <svg viewBox="0 0 60 50" className="absolute -top-8 -left-2 w-14 h-14 z-10">
      {/* Hat body (red) */}
      <path
        d="M5 45 Q10 25 20 20 L45 15 Q55 25 50 45 Z"
        fill="#e32636"
        stroke="#c41e2d"
        strokeWidth="1"
      />
      {/* White fur trim at bottom */}
      <ellipse cx="28" cy="45" rx="25" ry="6" fill="white" />
      {/* White pompom at top */}
      <circle cx="48" cy="12" r="7" fill="white" />
      {/* Hat tip curve */}
      <path
        d="M45 15 Q52 8 48 12"
        fill="#e32636"
        stroke="#c41e2d"
        strokeWidth="1"
      />
    </svg>
  );
}

const slides = [
  {
    id: 1,
    title: 'НОВЫЙ ГОД – НОВЫЙ ШКАФ!',
    subtitle: 'Новогодние скидки в',
    hasLogo: true,
    discount: '-65%',
    hasSantaHat: true,
    description: 'Серия Экспресс, фасад Зеркало',
    details: 'В декорах Белый снег, Бетон, Крафт Табачный',
    date: '1-31 декабря',
    buttonText: 'СМОТРЕТЬ',
    buttonLink: '/sale',
    finePrint: [
      'Акция действует с 01.12.25 по 31.12.25,',
      'на шкафы серии «Экспресс» 2-дв. фасад зеркало',
      'в декоре «Белый снег» и «Бетон», 3-дв. фасад',
      'зеркало в декоре «Крафт Табачный».',
      'Рекламодатель: ООО «Е1-ЮГ» г. Белореченск,',
      'ул. Луценко, 125. ОГРН 1222300027344',
    ],
  },
  {
    id: 2,
    title: 'СЕРИЯ ПРАЙМ',
    subtitle: 'Премиальное качество по выгодной цене',
    discount: '-60%',
    description: 'Шкафы-купе серии Прайм',
    details: 'Современный дизайн и надежная фурнитура',
    buttonText: 'ПОДРОБНЕЕ',
    buttonLink: '/catalog/shkafi-kupe',
  },
  {
    id: 3,
    title: 'ГАРДЕРОБНЫЕ СИСТЕМЫ',
    subtitle: 'Функциональные решения для хранения',
    discount: '-40%',
    description: 'Системы хранения на заказ',
    details: 'Индивидуальный подход к каждому клиенту',
    buttonText: 'ЗАКАЗАТЬ',
    buttonLink: '/dressing_room',
  },
  {
    id: 4,
    title: 'ШКАФЫ-КУПЕ НА ЗАКАЗ',
    subtitle: 'Производство в Москве и Белореченске',
    discount: '-50%',
    description: 'Любые размеры и наполнение',
    details: 'Доставка от 24 часов, гарантия до 10 лет',
    buttonText: 'ЗАКАЗАТЬ',
    buttonLink: '/custom_cabinets',
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
    <section className="bg-[#f0f0f0] py-4">
      <div className="container-custom">
        <div className="relative bg-white rounded-lg overflow-hidden shadow-sm">
          <div className="grid grid-cols-1 lg:grid-cols-2 min-h-[450px] lg:min-h-[520px]">
            {/* Left side - Content */}
            <div className="p-8 lg:p-12 flex flex-col justify-center">
              <h1 className="text-3xl lg:text-4xl xl:text-[42px] font-bold text-[#333] mb-3 leading-tight">
                {slide.title}
              </h1>
              <div className="text-xl lg:text-2xl text-[#333] mb-8" style={{ fontFamily: 'Georgia, serif', fontStyle: 'italic' }}>
                {slide.subtitle}
                {slide.hasLogo && <E1LogoSmall />}
              </div>

              <div className="flex items-start gap-4 mb-6">
                {/* Discount badge with Santa hat */}
                <div className="relative">
                  {slide.hasSantaHat && <SantaHat />}
                  <div className="text-[#62bb46] text-6xl lg:text-7xl xl:text-8xl font-bold leading-none">
                    {slide.discount}
                  </div>
                </div>
                <div className="border-l-2 border-[#62bb46] pl-4 pt-1">
                  <div className="font-bold text-lg text-[#333]">{slide.description}</div>
                  <div className="text-[#666] text-sm">{slide.details}</div>
                  {slide.date && (
                    <div className="text-[#333] font-medium mt-2 text-lg">{slide.date}</div>
                  )}
                </div>
              </div>

              <div className="mb-6">
                <Link
                  href={slide.buttonLink}
                  className="inline-block px-10 py-4 bg-[#62bb46] text-white font-bold rounded hover:bg-[#75c35c] transition-colors uppercase tracking-wide text-lg"
                >
                  {slide.buttonText}
                </Link>
              </div>

              {slide.finePrint && (
                <div className="text-xs text-[#999] leading-relaxed">
                  {slide.finePrint.map((line, index) => (
                    <span key={index}>
                      {line}
                      {index < slide.finePrint.length - 1 && <br />}
                    </span>
                  ))}
                </div>
              )}
            </div>

            {/* Right side - Image */}
            <div className="relative hidden lg:block">
              {/* Background image placeholder - wardrobe with fireplace */}
              <div
                className="absolute inset-0 bg-cover bg-center bg-no-repeat"
                style={{
                  backgroundImage: `url('/images/banner-wardrobe.jpg')`,
                  backgroundColor: '#e8e4e0',
                }}
              >
                {/* Fallback content when image is not available */}
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="text-center p-8">
                    {/* Decorative wardrobe illustration */}
                    <svg className="w-64 h-80 mx-auto text-[#666]" viewBox="0 0 200 280" fill="none" stroke="currentColor">
                      {/* Wardrobe frame */}
                      <rect x="20" y="20" width="160" height="240" rx="4" strokeWidth="2" fill="#f5f5f5"/>
                      {/* Left door */}
                      <rect x="25" y="25" width="75" height="230" rx="2" strokeWidth="1.5" fill="#e0e0e0"/>
                      {/* Right door (mirror effect) */}
                      <rect x="100" y="25" width="75" height="230" rx="2" strokeWidth="1.5" fill="#d0d8e0"/>
                      {/* Mirror reflection lines */}
                      <line x1="110" y1="35" x2="165" y2="35" strokeWidth="0.5" opacity="0.5"/>
                      <line x1="115" y1="50" x2="160" y2="50" strokeWidth="0.5" opacity="0.5"/>
                      {/* Door handles */}
                      <rect x="90" y="130" width="4" height="20" rx="2" fill="#888"/>
                      <rect x="106" y="130" width="4" height="20" rx="2" fill="#888"/>
                      {/* Vertical divider line */}
                      <line x1="100" y1="25" x2="100" y2="255" strokeWidth="1"/>
                    </svg>
                  </div>
                </div>
              </div>

              {/* Christmas decoration overlay */}
              <div className="absolute top-0 right-0 w-20 h-20 opacity-70">
                <svg viewBox="0 0 100 100" className="w-full h-full">
                  {/* Christmas garland hint */}
                  <path d="M0 20 Q25 30 50 20 Q75 10 100 20" stroke="#228B22" strokeWidth="8" fill="none"/>
                  <circle cx="25" cy="22" r="5" fill="#c41e2d"/>
                  <circle cx="75" cy="18" r="5" fill="#ffd700"/>
                </svg>
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
                  index === currentSlide
                    ? 'bg-[#62bb46] w-6'
                    : 'bg-gray-300 hover:bg-gray-400'
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
