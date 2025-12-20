'use client';

import Link from 'next/link';
import { useState } from 'react';

// E1 Logo SVG component
function Logo() {
  return (
    <svg viewBox="0 0 80 56" className="h-11 w-auto">
      <g fill="#62BB46">
        <polygon points="76.82,9.79 76.82,51.74 65.78,51.74 65.78,15.47 69.73,15.47 58.69,9.94 58.69,4.26 65.78,4.26" />
        <polygon points="47.48,15.47 11.05,15.47 0,10.03 0,4.26 36.43,4.26 47.48,9.79" />
        <polygon points="11.06,15.47 11.06,22.4 36.43,22.4 47.48,27.92 47.48,33.6 11.22,33.6 0,28.08 0,15.47" />
        <polygon points="47.48,46.06 47.48,51.74 11.05,51.74 0,46.22 0,33.6 11.22,33.6 11.22,40.53 36.43,40.53" />
      </g>
    </svg>
  );
}

// Service submenu structure
const serviceSubmenu = [
  {
    title: 'Доставка',
    items: [
      { label: 'Доставка и сборка', href: '/service/delivery' },
      { label: 'Путь заказа', href: '/service/order-path' },
      { label: 'Сроки изготовления', href: '/service/production-time' },
    ]
  },
  {
    title: 'Покупка и гарантии',
    items: [
      { label: 'Условия покупки', href: '/service/purchase-terms' },
      { label: 'Гарантия', href: '/service/warranty' },
      { label: 'Рассрочка', href: '/service/installment' },
      { label: 'Возврат товара', href: '/service/returns' },
    ]
  },
  {
    title: 'Помощь в выборе',
    items: [
      { label: 'О шкафах', href: '/service/about-wardrobes' },
      { label: 'Каталог фотопечати', href: '/service/photo-print' },
      { label: 'Вопросы и ответы', href: '/service/faq' },
      { label: 'Советы от Е1', href: '/service/tips' },
      { label: 'Наши работы', href: '/service/portfolio' },
      { label: 'Брошюра', href: '/service/brochure' },
    ]
  },
  {
    title: 'О компании',
    items: [
      { label: 'Производство', href: '/about/production' },
      { label: 'Качество сервиса', href: '/about/quality' },
      { label: 'Вакансии', href: '/about/careers' },
      { label: 'Партнерство', href: '/about/partnership' },
    ]
  },
  {
    title: 'Инструкции к мебели',
    href: '/service/instructions',
    items: []
  },
];

const menuItems = [
  { label: 'КАТАЛОГ', href: '/catalog' },
  { label: 'АКЦИИ', href: '/sales', hasLightning: true },
  { label: 'СЕРИИ', href: '/series' },
  { label: 'ШКАФЫ НА ЗАКАЗ', href: '/custom' },
  { label: 'ГАРДЕРОБНЫЕ', href: '/catalog/garderobnye' },
  { label: 'СЕРВИС', href: '/service', hasSubmenu: true },
  { label: 'ОТЗЫВЫ', href: '/reviews' },
  { label: 'АДРЕСА САЛОНОВ', href: '/stores' },
  { label: 'ГЕОГРАФИЯ ДОСТАВКИ', href: '/delivery' },
];

export default function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  return (
    <header className="sticky top-0 z-50">
      {/* Top bar - graphite color */}
      <div className="bg-[#3d4543] text-white">
        <div className="container-custom">
          <div className="flex items-center justify-between py-2 text-xs">
            {/* Left side badges */}
            <div className="hidden lg:flex items-center gap-3 xl:gap-5">
              {/* City selector */}
              <button className="flex items-center gap-1.5 hover:text-[#62bb46] transition-colors px-2 py-1 rounded hover:bg-white/10">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Город: <strong>Москва</strong></span>
                <span className="text-[#62bb46] underline ml-1">сменить</span>
              </button>

              <div className="w-px h-4 bg-white/20"></div>

              {/* Sales badge */}
              <Link href="/sales" className="flex items-center gap-1.5 hover:text-[#62bb46] transition-colors px-2 py-1 rounded hover:bg-white/10">
                <svg className="w-4 h-4 text-[#f5b800]" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
                <span className="text-white font-bold">Скидки недели</span>
              </Link>

              <div className="w-px h-4 bg-white/20"></div>

              {/* Fast delivery */}
              <div className="flex items-center gap-1.5 px-2 py-1">
                <svg className="w-4 h-4 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Быстрые сроки доставки</span>
              </div>

              <div className="w-px h-4 bg-white/20"></div>

              {/* Installment */}
              <div className="flex items-center gap-1.5 px-2 py-1">
                <svg className="w-4 h-4 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Рассрочка 0-0-6</span>
              </div>

              <div className="w-px h-4 bg-white/20"></div>

              {/* Order status */}
              <a
                href="https://booking.e-1.ru/check/"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-1.5 hover:text-[#62bb46] transition-colors px-2 py-1 rounded hover:bg-white/10"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span className="text-white font-bold">Проверить статус заказа</span>
              </a>
            </div>

            {/* Right side - phone (desktop) */}
            <div className="hidden lg:flex items-center gap-2 ml-auto bg-[#62bb46] px-4 py-1.5 rounded">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <a href="tel:+78001001211" className="font-bold hover:underline">
                8-800-100-12-11
              </a>
            </div>

            {/* Mobile: order status link */}
            <a
              href="https://booking.e-1.ru/check/"
              target="_blank"
              rel="noopener noreferrer"
              className="lg:hidden flex items-center gap-1 hover:text-[#62bb46] transition-colors text-[11px]"
            >
              <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <span>Статус заказа</span>
            </a>

            {/* Mobile: phone */}
            <a href="tel:+78001001211" className="lg:hidden font-bold text-[11px] hover:text-[#62bb46] transition-colors">
              8-800-100-12-11
            </a>
          </div>
        </div>
      </div>

      {/* Main header row - white */}
      <div className="bg-white border-b border-gray-100 shadow-sm">
        <div className="container-custom">
          <div className="flex items-center py-3 gap-3 xl:gap-5">
            {/* Mobile menu button */}
            <button
              className="lg:hidden p-2 -ml-2"
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              aria-label="Меню"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {isMobileMenuOpen ? (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                ) : (
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                )}
              </svg>
            </button>

            {/* Logo + Brand */}
            <Link href="/" className="flex items-center gap-3 shrink-0">
              <Logo />
              <div className="hidden sm:flex flex-col leading-tight">
                <span className="text-[#3d4543] font-black text-lg tracking-tight">ШКАФЫ</span>
                <span className="text-[#3d4543] font-black text-lg tracking-tight">ГАРДЕРОБНЫЕ</span>
              </div>
            </Link>

            {/* Catalog button */}
            <Link
              href="/catalog"
              className="hidden lg:flex items-center gap-2 px-5 py-2.5 bg-[#62bb46] text-white font-bold rounded hover:bg-[#55a83d] transition-colors text-sm ml-4"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              КАТАЛОГ
            </Link>

            {/* Search */}
            <div className="hidden lg:flex flex-1 max-w-[400px] xl:max-w-[480px] mx-4">
              <div className="flex w-full border border-gray-300 rounded overflow-hidden focus-within:border-[#62bb46] transition-colors">
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Поиск по каталогу..."
                  className="flex-1 px-4 py-2.5 text-sm focus:outline-none"
                />
                <button className="px-5 py-2.5 bg-[#62bb46] text-white font-medium hover:bg-[#55a83d] transition-colors">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </button>
              </div>
            </div>

            {/* Messengers + Contact */}
            <div className="flex items-center gap-2 shrink-0 ml-auto lg:ml-0">
              {/* Telegram */}
              <a href="https://t.me/+79384222111" target="_blank" rel="noopener noreferrer" className="w-9 h-9 bg-[#54a9eb] rounded-full flex items-center justify-center hover:scale-110 transition-transform" title="Telegram">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="white">
                  <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                </svg>
              </a>
              {/* WhatsApp */}
              <a href="https://wa.me/79384222111" target="_blank" rel="noopener noreferrer" className="w-9 h-9 bg-[#60d669] rounded-full flex items-center justify-center hover:scale-110 transition-transform" title="WhatsApp">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="white">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </a>
              {/* Max */}
              <a href="https://max.ru/79384222111" target="_blank" rel="noopener noreferrer" className="w-9 h-9 rounded-full flex items-center justify-center hover:scale-110 transition-transform overflow-hidden" title="Max" style={{ background: 'linear-gradient(135deg, #00c6ff 0%, #7c3aed 50%, #db2777 100%)' }}>
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="white">
                  <path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.38 5.07L2 22l4.93-1.38C8.42 21.5 10.15 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 3.5c2.49 0 4.5 2.01 4.5 4.5s-2.01 4.5-4.5 4.5-4.5-2.01-4.5-4.5 2.01-4.5 4.5-4.5z"/>
                </svg>
              </a>
            </div>

            {/* Contact info */}
            <div className="hidden xl:flex flex-col text-right shrink-0 ml-2">
              <span className="text-[11px] text-gray-500 uppercase tracking-wide">Напишите нам</span>
              <a href="tel:+79384222111" className="text-sm text-[#3d4543] hover:text-[#62bb46] transition-colors font-bold">
                8-938-422-21-11
              </a>
            </div>

            {/* Right icons */}
            <div className="hidden sm:flex items-center gap-1 ml-2">
              {/* Compare / Favorites */}
              <Link href="/compare" className="flex flex-col items-center justify-center w-12 h-12 hover:text-[#62bb46] transition-colors relative group">
                <svg className="w-6 h-6 text-gray-500 group-hover:text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span className="absolute top-1 right-1 bg-[#62bb46] text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
              </Link>

              {/* Wishlist */}
              <Link href="/wishlist" className="flex flex-col items-center justify-center w-12 h-12 hover:text-[#62bb46] transition-colors relative group">
                <svg className="w-6 h-6 text-gray-500 group-hover:text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span className="absolute top-1 right-1 bg-[#62bb46] text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
              </Link>

              {/* Cart */}
              <Link href="/cart" className="flex flex-col items-center justify-center w-12 h-12 hover:text-[#62bb46] transition-colors relative group">
                <svg className="w-6 h-6 text-gray-500 group-hover:text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span className="absolute top-1 right-1 bg-[#62bb46] text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
              </Link>
            </div>

            {/* Mobile cart only */}
            <Link href="/cart" className="sm:hidden flex flex-col items-center justify-center w-10 h-10 hover:text-[#62bb46] transition-colors relative">
              <svg className="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span className="absolute top-0 right-0 bg-[#62bb46] text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
            </Link>
          </div>
        </div>
      </div>

      {/* Green Navigation Menu */}
      <nav className="bg-[#62bb46] hidden lg:block">
        <div className="container-custom">
          <ul className="flex items-center justify-between">
            {menuItems.map((item) => (
              <li key={item.href} className={item.hasSubmenu ? 'relative group' : ''}>
                <Link
                  href={item.href}
                  className="flex items-center gap-1.5 px-2 xl:px-3 py-3 font-bold hover:bg-[#55a83d] transition-colors text-[12px] xl:text-[13px] whitespace-nowrap"
                  style={{ color: 'white' }}
                >
                  {item.hasLightning && (
                    <svg className="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="#f5b800">
                      <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  )}
                  {item.label}
                  {item.hasSubmenu && (
                    <svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  )}
                </Link>
                {/* Dropdown for СЕРВИС */}
                {item.hasSubmenu && (
                  <div className="absolute left-0 top-full bg-white shadow-xl rounded-b-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[700px] -ml-4">
                    <div className="flex p-4 gap-6">
                      {serviceSubmenu.map((section) => (
                        <div key={section.title} className="min-w-[120px]">
                          {section.items.length > 0 ? (
                            <>
                              <div className="font-bold text-[#3d4543] text-sm mb-2 pb-1 border-b border-gray-200">{section.title}</div>
                              <ul className="space-y-1">
                                {section.items.map((subItem) => (
                                  <li key={subItem.href}>
                                    <Link
                                      href={subItem.href}
                                      className="block text-[13px] text-gray-600 hover:text-[#62bb46] transition-colors py-0.5"
                                    >
                                      {subItem.label}
                                    </Link>
                                  </li>
                                ))}
                              </ul>
                            </>
                          ) : (
                            <Link
                              href={section.href || '#'}
                              className="font-bold text-[#3d4543] text-sm hover:text-[#62bb46] transition-colors"
                            >
                              {section.title}
                            </Link>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </li>
            ))}
          </ul>
        </div>
      </nav>

      {/* Mobile menu */}
      {isMobileMenuOpen && (
        <div className="lg:hidden border-t border-gray-200 bg-white">
          {/* Mobile search */}
          <div className="container-custom py-3">
            <div className="flex border border-gray-300 rounded overflow-hidden">
              <input
                type="text"
                placeholder="Поиск по каталогу..."
                className="flex-1 px-4 py-2.5 text-sm focus:outline-none"
              />
              <button className="px-4 py-2.5 bg-[#62bb46] text-white">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </div>
          </div>

          {/* Mobile nav */}
          <nav className="bg-[#62bb46]">
            <ul className="divide-y divide-[#55a83d]">
              {menuItems.map((item) => (
                <li key={item.href}>
                  <Link
                    href={item.href}
                    className="flex items-center gap-2 px-4 py-3 font-bold"
                    style={{ color: 'white' }}
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    {item.hasLightning && (
                      <svg className="w-4 h-4" viewBox="0 0 24 24" fill="#f5b800">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    )}
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
        </div>
      )}
    </header>
  );
}
