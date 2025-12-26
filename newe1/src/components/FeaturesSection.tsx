'use client';

import { useState } from 'react';
import Link from 'next/link';
import MeasurementModal from './MeasurementModal';

// Decorative icons for cards
const WardrobeIcon = () => (
  <svg className="w-12 h-12 text-gray-100" viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1">
    <rect x="8" y="6" width="32" height="36" rx="2" />
    <line x1="24" y1="6" x2="24" y2="42" />
    <line x1="18" y1="20" x2="18" y2="28" />
    <line x1="30" y1="20" x2="30" y2="28" />
  </svg>
);

const ClosetIcon = () => (
  <svg className="w-12 h-12 text-gray-100" viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1">
    <rect x="6" y="8" width="36" height="32" rx="2" />
    <line x1="6" y1="16" x2="42" y2="16" />
    <line x1="18" y1="16" x2="18" y2="40" />
    <line x1="30" y1="16" x2="30" y2="40" />
    <rect x="10" y="20" width="4" height="6" rx="1" />
    <rect x="34" y="20" width="4" height="6" rx="1" />
  </svg>
);

const CustomIcon = () => (
  <svg className="w-12 h-12 text-gray-100" viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1">
    <rect x="10" y="4" width="28" height="40" rx="2" />
    <line x1="10" y1="14" x2="38" y2="14" />
    <line x1="24" y1="14" x2="24" y2="44" />
    <path d="M16 8 L20 12 L16 12 Z" fill="currentColor" />
    <path d="M32 8 L28 12 L32 12 Z" fill="currentColor" />
  </svg>
);

const SeriesIcon = () => (
  <svg className="w-12 h-12 text-gray-100" viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1">
    <rect x="4" y="10" width="16" height="28" rx="2" />
    <rect x="24" y="10" width="20" height="28" rx="2" />
    <line x1="12" y1="10" x2="12" y2="38" />
    <line x1="34" y1="10" x2="34" y2="38" />
  </svg>
);

const SaleIcon = () => (
  <svg className="w-12 h-12 text-gray-100" viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1">
    <circle cx="24" cy="24" r="18" />
    <text x="24" y="30" textAnchor="middle" fontSize="16" fill="currentColor" stroke="none">%</text>
  </svg>
);

export default function FeaturesSection() {
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <>
    <MeasurementModal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} />
    <section className="py-4 bg-[#f5f5f5]">
      <div className="container-custom">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          {/* Card 1 - Шкафы в наличии */}
          <div className="relative bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all overflow-hidden">
            <div className="absolute -right-2 -top-2 opacity-50">
              <WardrobeIcon />
            </div>
            <div className="relative z-10">
              <span className="inline-block bg-[#62bb46] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
                ХИТ
              </span>
              <h3 className="text-base font-bold text-[#3d4543] mb-1">Шкафы</h3>
              <p className="text-xs text-gray-500 mb-3 leading-relaxed">
                В наличии и под заказ • подбор по размерам
              </p>
              <div className="space-y-1.5">
                <Link
                  href="/catalog/shkafi"
                  className="block w-full text-center bg-[#62bb46] text-white text-xs font-bold py-2 px-3 rounded-full hover:bg-[#4a9e36] transition-colors"
                >
                  Смотреть витрину
                </Link>
                <Link
                  href="/catalog/shkafi?filter=size"
                  className="flex items-center justify-center gap-1.5 w-full text-center text-gray-500 text-xs py-1.5 hover:text-[#62bb46] transition-colors"
                >
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  Быстрый подбор
                </Link>
              </div>
            </div>
          </div>

          {/* Card 2 - Гардеробные */}
          <div className="relative bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all overflow-hidden">
            <div className="absolute -right-2 -top-2 opacity-50">
              <ClosetIcon />
            </div>
            <div className="relative z-10">
              <span className="inline-block bg-[#3d4543] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
                НОВОЕ
              </span>
              <h3 className="text-base font-bold text-[#3d4543] mb-1">Гардеробные</h3>
              <p className="text-xs text-gray-500 mb-3 leading-relaxed">
                Проекты хранения • наполнение • стиль
              </p>
              <Link
                href="/catalog/garderobnye"
                className="block w-full text-center bg-[#62bb46] text-white text-xs font-bold py-2 px-3 rounded-full hover:bg-[#4a9e36] transition-colors"
              >
                Популярные решения
              </Link>
            </div>
          </div>

          {/* Card 3 - Мебель по вашим размерам */}
          <div className="relative bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all overflow-hidden">
            <div className="absolute -right-2 -top-2 opacity-50">
              <CustomIcon />
            </div>
            <div className="relative z-10">
              <span className="inline-block border border-[#3d4543] text-[#3d4543] text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
                ИНДИВИДУАЛЬНО
              </span>
              <h3 className="text-base font-bold text-[#3d4543] mb-1">На заказ под нишу</h3>
              <p className="text-xs text-gray-500 mb-3 leading-relaxed">
                Встроенный • нестандарт • замер и проект
              </p>
              <div className="space-y-1.5">
                <button
                  onClick={() => setIsModalOpen(true)}
                  className="block w-full text-center bg-transparent border-2 border-[#62bb46] text-[#62bb46] text-xs font-bold py-1.5 px-3 rounded-full hover:bg-[#4a9e36] hover:text-white hover:border-[#4a9e36] transition-all cursor-pointer"
                >
                  Вызвать замерщика
                </button>
                <Link
                  href="/quiz"
                  className="flex items-center justify-center gap-1.5 w-full text-center text-gray-500 text-xs py-1.5 hover:text-[#62bb46] transition-colors"
                >
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  Быстрый расчёт
                </Link>
              </div>
            </div>
          </div>

          {/* Card 4 - Серии и стили */}
          <div className="relative bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all overflow-hidden">
            <div className="absolute -right-2 -top-2 opacity-50">
              <SeriesIcon />
            </div>
            <div className="relative z-10">
              <span className="inline-block bg-[#ff6b6b] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
                -10%
              </span>
              <h3 className="text-base font-bold text-[#3d4543] mb-1">Серии и стили</h3>
              <p className="text-xs text-gray-500 mb-3 leading-relaxed">
                Эста • Локер • Прайм • Оптим
              </p>
              <div className="flex gap-1.5">
                <Link
                  href="/series"
                  className="flex-1 text-center bg-[#62bb46] text-white text-xs font-bold py-2 px-2 rounded-full hover:bg-[#4a9e36] transition-colors"
                >
                  Открыть
                </Link>
                <Link
                  href="/sales"
                  className="flex-1 text-center bg-transparent border-2 border-[#62bb46] text-[#62bb46] text-xs font-bold py-1.5 px-2 rounded-full hover:bg-[#4a9e36] hover:text-white hover:border-[#4a9e36] transition-all"
                >
                  Акции
                </Link>
              </div>
            </div>
          </div>

          {/* Card 5 - Распродажа */}
          <div className="relative bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all overflow-hidden sm:col-span-2 lg:col-span-1">
            <div className="absolute -right-2 -top-2 opacity-50">
              <SaleIcon />
            </div>
            <div className="relative z-10">
              <span className="inline-block bg-[#f5b800] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
                ВЫГОДНО
              </span>
              <h3 className="text-base font-bold text-[#3d4543] mb-1">Распродажа</h3>
              <p className="text-xs text-gray-500 mb-3 leading-relaxed">
                Самые выгодные позиции тут
              </p>
              <Link
                href="/sales"
                className="block w-full text-center bg-[#f5b800] text-white text-xs font-bold py-2 px-3 rounded-full hover:bg-[#dba000] transition-colors"
              >
                Смотреть скидки
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>
    </>
  );
}
