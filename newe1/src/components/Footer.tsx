'use client';

import { useState } from 'react';
import Link from 'next/link';
import DirectorContactModal from './DirectorContactModal';
import { useCity } from '@/context/CityContext';

export default function Footer() {
  const [isDirectorModalOpen, setIsDirectorModalOpen] = useState(false);
  const { city } = useCity();

  const links = [
    { href: '/catalog', label: 'Каталог' },
    { href: '/reviews', label: 'Отзывы' },
    { href: '/stores', label: 'Адреса салонов' },
    { href: '/service/delivery', label: 'География доставки' },
    { href: '/promotions', label: 'Акции' },
    { href: '/service/purchase-terms', label: 'Условия покупки' },
    { href: '/service/installment', label: 'Рассрочка' },
    { href: '/service/instructions', label: 'Инструкции к мебели' },
    { href: 'https://booking.e-1.ru/status/', label: 'Проверить статус', external: true },
    { href: 'https://booking.e-1.ru/service/', label: 'Чат с отделом доставки', external: true },
  ];

  // B2B menu items
  const b2bLinks = [
    { href: '/business/designers', label: 'Дизайнерам и архитекторам' },
    { href: '/business/wholesale', label: 'Оптовые продажи' },
    { href: '/business/marketplace', label: 'Продавцам на маркетплейсах' },
    { href: '/business/franchise', label: 'Франшиза' },
    { href: '/business/suppliers', label: 'Поставщикам' },
  ];

  return (
    <>
      <DirectorContactModal isOpen={isDirectorModalOpen} onClose={() => setIsDirectorModalOpen(false)} />
      <footer className="bg-[#3d3d3d] text-white mt-8 pt-10 pb-6">
        <div className="container-custom">
          {/* Links grid */}
          <div className="grid grid-cols-2 md:grid-cols-5 gap-x-4 gap-y-2 mb-6 text-center md:text-left">
            {links.map((link) => (
              link.external ? (
                <a
                  key={link.href}
                  href={link.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors"
                >
                  {link.label}
                </a>
              ) : (
                <Link
                  key={link.href}
                  href={link.href}
                  className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors"
                >
                  {link.label}
                </Link>
              )
            ))}
          </div>

          {/* B2B Section */}
          <div className="mb-8 pb-6 border-b border-[#555555]">
            <h3 className="text-white font-bold text-sm mb-3 text-center md:text-left">Сотрудничество</h3>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-x-4 gap-y-2 text-center md:text-left">
              {b2bLinks.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors"
                >
                  {link.label}
                </Link>
              ))}
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

            {/* Messengers and socials */}
            <div className="flex items-center justify-center md:justify-end gap-2">
              <a href="https://wa.me/79384222111" target="_blank" rel="noopener noreferrer" className="w-9 h-9 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="WhatsApp">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </a>
              <a href="https://t.me/+79384222111" target="_blank" rel="noopener noreferrer" className="w-9 h-9 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="Telegram">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                </svg>
              </a>
              <a href="https://vk.com/e_odin" target="_blank" rel="noopener noreferrer" className="w-9 h-9 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="ВКонтакте">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.263c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/>
                </svg>
              </a>
              <a href="https://rutube.ru/channel/e1shkafy" target="_blank" rel="noopener noreferrer" className="w-9 h-9 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="Rutube">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12.76 2H6.86C5.28 2 4 3.28 4 4.86v14.28C4 20.72 5.28 22 6.86 22h10.28c1.58 0 2.86-1.28 2.86-2.86V9.24L12.76 2zM16 18H8v-2h8v2zm0-4H8v-2h8v2zm-2-4V4l6 6h-6z"/>
                </svg>
              </a>

              <button
                onClick={() => setIsDirectorModalOpen(true)}
                className="ml-2 text-[#62bb46] hover:text-white text-sm font-medium hover:bg-[#62bb46] px-3 py-1.5 rounded border border-[#62bb46] transition-colors"
              >
                Связь с директором
              </button>
            </div>
          </div>

          {/* Bottom section */}
          <div className="text-center md:text-left">
            <div className="text-[#d1d5db] text-sm mb-3">
              2007–2025 © Мебельная компания Е1 – шкафы и гардеробные в г. {city.name}
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
