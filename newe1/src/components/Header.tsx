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

const menuItems = [
  { label: 'КАТАЛОГ', href: '/catalog', hasMenu: true },
  { label: 'ГАРДЕРОБНЫЕ', href: '/catalog/garderobnye' },
  { label: 'ШКАФЫ НА ЗАКАЗ', href: '/catalog/shkafi-na-zakaz' },
  { label: 'АДРЕСА САЛОНОВ', href: '/stores' },
  { label: 'ПОКУПАТЕЛЮ', href: '/buyers' },
  { label: 'ОТЗЫВЫ', href: '/reviews' },
  { label: 'АКЦИИ', href: '/sales', hasLightning: true },
  { label: 'ПАРТНЕРСТВО', href: '/partnership' },
];

export default function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');

  return (
    <header className="bg-white sticky top-0 z-50">
      {/* Main header row */}
      <div className="border-b border-gray-100">
        <div className="container-custom">
          <div className="flex items-center py-2.5 gap-3 xl:gap-4">
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
            <Link href="/" className="flex items-center gap-2 shrink-0">
              <Logo />
              <p className="hidden sm:block text-sm text-[#333] leading-tight max-w-[180px]">
                Шкафы купе
              </p>
            </Link>

            {/* City selector */}
            <button className="hidden md:flex items-center gap-1 text-sm text-[#333] shrink-0 hover:text-[#62bb46] transition-colors ml-2">
              <span>Москва</span>
              <svg className="w-2.5 h-2.5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {/* Search */}
            <div className="hidden lg:flex flex-1 max-w-[320px] xl:max-w-[380px] mx-2">
              <div className="flex w-full">
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Поиск"
                  className="flex-1 px-4 py-2 border border-gray-300 border-r-0 rounded-l text-sm focus:outline-none focus:border-[#62bb46]"
                />
                <button className="px-5 py-2 bg-[#62bb46] text-white font-medium rounded-r hover:bg-[#55a83d] transition-colors text-sm">
                  Найти
                </button>
              </div>
            </div>

            {/* Working hours & Phone */}
            <div className="hidden xl:flex flex-col items-end text-right shrink-0 ml-auto">
              <div className="text-[11px] text-gray-500 leading-tight">с 07:00 до 20:00 мск</div>
              <a href="tel:+78001001211" className="font-bold text-[17px] text-[#333] hover:text-[#62bb46] transition-colors">
                8-800-100-12-11
              </a>
              <button className="text-[#62bb46] text-[11px] font-medium hover:underline uppercase tracking-wide">
                Заказать звонок
              </button>
            </div>

            {/* Messengers */}
            <div className="hidden xl:flex items-center gap-1.5 shrink-0">
              {/* Telegram */}
              <a href="https://t.me/+79384222111" target="_blank" rel="noopener noreferrer" className="w-8 h-8 bg-[#54a9eb] rounded flex items-center justify-center text-white hover:bg-[#3d96d9] transition-colors" title="Telegram">
                <svg className="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                </svg>
              </a>
              {/* WhatsApp */}
              <a href="https://wa.me/79384222111" target="_blank" rel="noopener noreferrer" className="w-8 h-8 bg-[#60d669] rounded-full flex items-center justify-center text-white hover:bg-[#4bc054] transition-colors" title="WhatsApp">
                <svg className="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </a>
            </div>

            {/* Additional contact */}
            <div className="hidden 2xl:flex flex-col text-right shrink-0">
              <span className="text-[11px] text-gray-500">Напишите нам</span>
              <a href="tel:+79384222111" className="text-[13px] text-[#333] hover:text-[#62bb46] transition-colors font-medium">
                +7 (938) 422-21-11
              </a>
            </div>

            {/* Right icons */}
            <div className="flex items-center gap-0.5 ml-auto xl:ml-0">
              {/* Order Status */}
              <a
                href="https://booking.e-1.ru/check/"
                target="_blank"
                rel="noopener noreferrer"
                className="flex flex-col items-center justify-center w-14 h-12 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span className="text-[9px] text-gray-500 mt-0.5 whitespace-nowrap">Статус заказа</span>
              </a>

              {/* Compare */}
              <Link href="/compare" className="hidden sm:flex flex-col items-center justify-center w-12 h-12 hover:text-[#62bb46] transition-colors relative">
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span className="absolute top-1 right-1 bg-[#62bb46] text-white text-[10px] font-medium rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
              </Link>

              {/* Wishlist */}
              <Link href="/wishlist" className="hidden sm:flex flex-col items-center justify-center w-12 h-12 hover:text-[#62bb46] transition-colors relative">
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span className="absolute top-1 right-1 bg-[#62bb46] text-white text-[10px] font-medium rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
              </Link>

              {/* Cart */}
              <Link href="/cart" className="flex flex-col items-center justify-center w-12 h-12 hover:text-[#62bb46] transition-colors relative">
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span className="absolute top-1 right-1 bg-[#62bb46] text-white text-[10px] font-medium rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">0</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Green Navigation Menu */}
      <nav className="bg-[#62bb46] hidden lg:block">
        <div className="container-custom">
          <ul className="flex items-center">
            {menuItems.map((item, index) => (
              <li key={item.href}>
                <Link
                  href={item.href}
                  className={`flex items-center gap-2 px-5 py-3 text-white font-semibold hover:bg-[#55a83d] transition-colors text-sm tracking-wide ${
                    index === 0 ? 'border-l border-r border-[#4a9a35]' : 'border-r border-[#4a9a35]'
                  }`}
                >
                  {item.hasMenu && (
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                  )}
                  {item.hasLightning && (
                    <svg className="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  )}
                  {item.label}
                </Link>
              </li>
            ))}
            <li className="ml-auto">
              <button className="px-4 py-2.5 text-white hover:bg-[#55a83d] transition-colors">
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <circle cx="5" cy="12" r="2" />
                  <circle cx="12" cy="12" r="2" />
                  <circle cx="19" cy="12" r="2" />
                </svg>
              </button>
            </li>
          </ul>
        </div>
      </nav>

      {/* Mobile menu */}
      {isMobileMenuOpen && (
        <div className="lg:hidden border-t border-gray-200 bg-white">
          {/* Mobile search */}
          <div className="container-custom py-3">
            <div className="flex">
              <input
                type="text"
                placeholder="Поиск"
                className="flex-1 px-4 py-2 border border-gray-300 border-r-0 rounded-l text-sm focus:outline-none"
              />
              <button className="px-4 py-2 bg-[#62bb46] text-white rounded-r text-sm font-medium">
                Найти
              </button>
            </div>
          </div>
          {/* Mobile nav */}
          <nav className="bg-[#62bb46]">
            <ul className="divide-y divide-[#4a9a35]">
              {menuItems.map((item) => (
                <li key={item.href}>
                  <Link
                    href={item.href}
                    className="flex items-center gap-2 px-4 py-3 text-white font-medium"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    {item.hasLightning && (
                      <svg className="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    )}
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
          {/* Mobile contacts */}
          <div className="container-custom py-4 space-y-3">
            <a href="tel:+78001001211" className="flex items-center gap-2 font-bold text-lg text-[#333]">
              8-800-100-12-11
            </a>
            <div className="text-sm text-gray-500">с 07:00 до 20:00 мск</div>
            <button className="text-[#62bb46] font-medium text-sm uppercase">Заказать звонок</button>
          </div>
        </div>
      )}
    </header>
  );
}
