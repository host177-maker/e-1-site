'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';
import MeasurementModal from './MeasurementModal';

const categories = [
  {
    id: 1,
    title: 'Шкафы',
    subtitle: 'В наличии и под заказ • подбор по размерам',
    badge: 'ХИТ',
    badgeStyle: 'bg-black text-white',
    image: '/uploads/frame_shkafi.jpg',
    buttonText: 'Смотреть витрину',
    buttonHref: '/catalog/',
    buttonStyle: 'primary',
    secondaryLink: { text: 'Быстрый подбор', href: '/catalog/', icon: 'search' },
  },
  {
    id: 2,
    title: 'Гардеробные',
    subtitle: 'Проекты хранения • наполнение • стиль',
    badge: 'НОВОЕ',
    badgeStyle: 'bg-[#62bb46] text-white',
    image: '/uploads/frame_gard.jpg',
    buttonText: 'Популярные решения',
    buttonHref: '/catalog/garderobnye',
    buttonStyle: 'primary',
  },
  {
    id: 3,
    title: 'На заказ под нишу',
    subtitle: 'Встроенный • нестандарт • замер и проект',
    badge: 'ИНДИВИДУАЛЬНО',
    badgeStyle: 'bg-transparent border border-white text-white',
    image: '/uploads/frame_zakaz.jpg',
    buttonText: 'Вызвать замерщика',
    buttonAction: 'modal',
    buttonStyle: 'outline',
    secondaryLink: { text: 'Быстрый расчет', href: '/custom', icon: 'calculator' },
  },
  {
    id: 4,
    title: 'Распродажа',
    subtitle: 'Самые выгодные позиции тут',
    badge: 'ВЫГОДНО',
    badgeStyle: 'bg-[#62bb46] text-white',
    image: '/uploads/frame_sale.jpg',
    buttonText: 'Смотреть скидки',
    buttonHref: '/sales',
    buttonStyle: 'primary',
  },
];

export default function FeaturesSection() {
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <>
      <MeasurementModal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} />
      <section className="py-4 bg-[#f5f5f5]">
        <div className="container-custom">
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
            {categories.map((category) => (
              <div
                key={category.id}
                className="relative overflow-hidden h-[200px] sm:h-[220px] group"
              >
                {/* Background image */}
                <Image
                  src={category.image}
                  alt={category.title}
                  fill
                  className="object-cover"
                />


                {/* Content */}
                <div className="absolute inset-0 p-3 sm:p-4 flex flex-col">
                  {/* Badge */}
                  <div className="mb-auto">
                    <span className={`inline-block px-2 sm:px-3 py-0.5 sm:py-1 text-[10px] sm:text-xs font-bold ${category.badgeStyle}`}>
                      {category.badge}
                    </span>
                  </div>

                  {/* Text content */}
                  <div className="mt-auto">
                    <h3 className="text-base sm:text-xl font-bold text-white mb-0.5 sm:mb-1">
                      {category.title}
                    </h3>
                    <p className="text-white/80 text-[10px] sm:text-xs mb-2 sm:mb-3 line-clamp-2">
                      {category.subtitle}
                    </p>

                    {/* Main button */}
                    {category.buttonAction === 'modal' ? (
                      <button
                        onClick={() => setIsModalOpen(true)}
                        className={`block w-full text-center py-2 px-3 font-bold text-xs sm:text-sm transition-all ${
                          category.buttonStyle === 'primary'
                            ? 'bg-[#62bb46] text-white hover:bg-[#55a83d]'
                            : 'bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900'
                        }`}
                      >
                        {category.buttonText}
                      </button>
                    ) : (
                      <Link
                        href={category.buttonHref || '#'}
                        className={`block text-center py-2 px-3 font-bold text-xs sm:text-sm transition-all ${
                          category.buttonStyle === 'primary'
                            ? 'bg-[#62bb46] text-white hover:bg-[#55a83d]'
                            : 'bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900'
                        }`}
                      >
                        {category.buttonText}
                      </Link>
                    )}

                    {/* Secondary link */}
                    {category.secondaryLink && (
                      <Link
                        href={category.secondaryLink.href}
                        className="flex items-center justify-center gap-1 mt-2 text-white/70 hover:text-white text-[10px] sm:text-xs transition-colors"
                      >
                        {category.secondaryLink.icon === 'search' && (
                          <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                          </svg>
                        )}
                        {category.secondaryLink.icon === 'calculator' && (
                          <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                          </svg>
                        )}
                        {category.secondaryLink.text}
                      </Link>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
