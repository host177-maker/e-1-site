'use client';

import Link from 'next/link';
import { useState, useEffect, useCallback } from 'react';

interface Banner {
  id: number;
  desktop_image: string;
  mobile_image: string;
  link: string | null;
}

export default function HeroBanner() {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isAutoPlay, setIsAutoPlay] = useState(true);
  const [loading, setLoading] = useState(true);
  const [isMobile, setIsMobile] = useState(false);

  // Fetch banners from API
  useEffect(() => {
    const fetchBanners = async () => {
      try {
        const response = await fetch('/api/banners');
        const data = await response.json();
        if (data.success && data.data.length > 0) {
          setBanners(data.data);
        }
      } catch (error) {
        console.error('Error fetching banners:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchBanners();
  }, []);

  // Detect mobile
  useEffect(() => {
    const checkMobile = () => {
      setIsMobile(window.innerWidth < 768);
    };

    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  const nextSlide = useCallback(() => {
    if (banners.length === 0) return;
    setCurrentSlide((prev) => (prev + 1) % banners.length);
  }, [banners.length]);

  const prevSlide = useCallback(() => {
    if (banners.length === 0) return;
    setCurrentSlide((prev) => (prev - 1 + banners.length) % banners.length);
  }, [banners.length]);

  useEffect(() => {
    if (!isAutoPlay || banners.length <= 1) return;

    const timer = setInterval(() => {
      nextSlide();
    }, 5000);

    return () => clearInterval(timer);
  }, [isAutoPlay, nextSlide, banners.length]);

  const handleMouseEnter = () => setIsAutoPlay(false);
  const handleMouseLeave = () => setIsAutoPlay(true);

  // Loading state
  if (loading) {
    return (
      <section className="relative bg-[#f5f5f5]">
        <div className="w-full h-[300px] sm:h-[350px] md:h-[400px] lg:h-[450px] xl:h-[500px] bg-gray-200 animate-pulse" />
      </section>
    );
  }

  // No banners
  if (banners.length === 0) {
    return null;
  }

  return (
    <section className="relative bg-[#f5f5f5]">
      <div
        className="relative overflow-hidden w-full"
        onMouseEnter={handleMouseEnter}
        onMouseLeave={handleMouseLeave}
      >
        {/* Slides container */}
        <div
          className="flex transition-transform duration-500 ease-out"
          style={{ transform: `translateX(-${currentSlide * 100}%)` }}
        >
          {banners.map((banner, index) => {
            const imageUrl = isMobile ? banner.mobile_image : banner.desktop_image;
            const content = (
              <div className="relative w-full aspect-square md:aspect-auto md:h-[400px] lg:h-[450px] xl:h-[500px]">
                <img
                  src={imageUrl}
                  alt={`Баннер ${index + 1}`}
                  className="w-full h-full object-cover object-center"
                />
              </div>
            );

            return (
              <div key={banner.id} className="w-full flex-shrink-0">
                {banner.link ? (
                  <Link href={banner.link} className="block">
                    {content}
                  </Link>
                ) : (
                  content
                )}
              </div>
            );
          })}
        </div>

        {/* Navigation arrows - only show if more than 1 banner */}
        {banners.length > 1 && (
          <>
            <button
              onClick={prevSlide}
              className="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all z-10 group"
              aria-label="Предыдущий слайд"
            >
              <svg className="w-5 h-5 md:w-6 md:h-6 text-[#3d4543] group-hover:text-[#62bb46] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <button
              onClick={nextSlide}
              className="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-all z-10 group"
              aria-label="Следующий слайд"
            >
              <svg className="w-5 h-5 md:w-6 md:h-6 text-[#3d4543] group-hover:text-[#62bb46] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>

            {/* Dots navigation */}
            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
              {banners.map((_, index) => (
                <button
                  key={index}
                  onClick={() => setCurrentSlide(index)}
                  className={`h-2.5 rounded-full transition-all ${
                    index === currentSlide
                      ? 'bg-[#62bb46] w-6'
                      : 'bg-white/70 hover:bg-white w-2.5'
                  }`}
                  aria-label={`Слайд ${index + 1}`}
                />
              ))}
            </div>
          </>
        )}
      </div>
    </section>
  );
}
