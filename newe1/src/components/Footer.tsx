'use client';

import { useState } from 'react';
import Link from 'next/link';
import DirectorContactModal from './DirectorContactModal';
import { useCity } from '@/context/CityContext';

export default function Footer() {
  const [isDirectorModalOpen, setIsDirectorModalOpen] = useState(false);
  const { city } = useCity();

  // Column 1: Каталог, Адреса салонов, Акции, Рассрочка, Отзывы
  const column1Links = [
    { href: '/catalog', label: 'Каталог' },
    { href: '/stores', label: 'Адреса салонов' },
    { href: '/promotions', label: 'Акции' },
    { href: '/service/installment', label: 'Рассрочка' },
    { href: '/reviews', label: 'Отзывы' },
  ];

  // Column 2: География доставки, Условия покупки, Инструкции к мебели, Проверить статус заказа, Чат с отделом доставки
  const column2Links = [
    { href: '/service/delivery', label: 'География доставки' },
    { href: '/service/purchase-terms', label: 'Условия покупки' },
    { href: '/service/instructions', label: 'Инструкции к мебели' },
    { href: 'https://booking.e-1.ru/status/', label: 'Проверить статус заказа', external: true },
    { href: 'https://booking.e-1.ru/service/', label: 'Чат с отделом доставки', external: true },
  ];

  // Column 3: B2B links
  const column3Links = [
    { href: '/business/designers', label: 'Дизайнерам и архитекторам' },
    { href: '/business/wholesale', label: 'Оптовые продажи' },
    { href: '/business/marketplace', label: 'Продавцам на маркетплейсах' },
    { href: '/business/franchise', label: 'Франшиза' },
    { href: '/business/suppliers', label: 'Поставщикам' },
  ];

  // All links combined for mobile
  const allLinks = [...column1Links, ...column2Links, ...column3Links];

  const renderLink = (link: { href: string; label: string; external?: boolean }) => {
    if (link.external) {
      return (
        <a
          key={link.href}
          href={link.href}
          target="_blank"
          rel="noopener noreferrer"
          className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors"
        >
          {link.label}
        </a>
      );
    }
    return (
      <Link
        key={link.href}
        href={link.href}
        className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors"
      >
        {link.label}
      </Link>
    );
  };

  return (
    <>
      <DirectorContactModal isOpen={isDirectorModalOpen} onClose={() => setIsDirectorModalOpen(false)} />
      <footer className="bg-[#3d3d3d] text-white mt-8 pt-10 pb-6">
        <div className="container-custom">
          {/* Mobile: 1 column centered */}
          <div className="md:hidden flex flex-col items-center gap-2 mb-8 pb-6 border-b border-[#555555]">
            {allLinks.map(renderLink)}
          </div>

          {/* Desktop: 3 columns */}
          <div className="hidden md:grid md:grid-cols-3 gap-8 mb-8 pb-6 border-b border-[#555555]">
            {/* Column 1 */}
            <div className="flex flex-col gap-2">
              {column1Links.map(renderLink)}
            </div>

            {/* Column 2 */}
            <div className="flex flex-col gap-2">
              {column2Links.map(renderLink)}
            </div>

            {/* Column 3 - B2B */}
            <div className="flex flex-col gap-2">
              <span className="text-white font-bold text-sm mb-1">Сотрудничество</span>
              {column3Links.map(renderLink)}
            </div>
          </div>

          {/* Contacts row */}
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 pb-6 border-b border-[#555555]">
            {/* Phone and hours */}
            <div className="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 text-center md:text-left">
              <a href="tel:+78001001211" className="text-white font-bold text-xl hover:text-[#62bb46] transition-colors">
                8-800-100-12-11
              </a>
              <span className="text-[#9ca3af] text-sm">с 07:00 до 20:00 мск</span>
            </div>

            {/* Contact with director */}
            <div className="flex items-center justify-center md:justify-end">
              <button
                onClick={() => setIsDirectorModalOpen(true)}
                className="text-[#62bb46] hover:text-white text-sm font-medium hover:bg-[#62bb46] px-3 py-1.5 rounded border border-[#62bb46] transition-colors"
              >
                Связь с директором
              </button>
            </div>
          </div>

          {/* Bottom section */}
          <div className="text-center md:text-left">
            <div className="text-[#d1d5db] text-sm mb-3">
              2007–2026 © Мебельная компания Е1 – шкафы и гардеробные в г. {city.name}
            </div>

            <div className="flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1 text-xs mb-4">
              <Link href="/privacy-policy" className="text-[#62bb46] hover:underline">
                Политика конфиденциальности
              </Link>
              <Link href="/security-policy" className="text-[#62bb46] hover:underline">
                Политика безопасности
              </Link>
              <Link href="/public-offer" className="text-[#62bb46] hover:underline">
                Публичная оферта
              </Link>
            </div>

            <div className="text-[#9ca3af] text-xs leading-relaxed space-y-0.5">
              <p>Информация на сайте не является публичной офертой. Копирование материалов без согласования запрещено.</p>
              <p>Изображения товаров могут отличаться от фактического внешнего вида.</p>
              <p>*Рассрочка — кредитный продукт ООО «Хоум Кредит энд Финанс Банк». Лицензия №316.</p>
            </div>
          </div>
        </div>
      </footer>
    </>
  );
}
