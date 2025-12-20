'use client';

import Link from 'next/link';
import { useState } from 'react';

// E1 Logo SVG component (green color)
function Logo() {
  return (
    <svg viewBox="0 0 80 56" className="h-12 w-auto">
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
  { label: 'КАТАЛОГ', href: '/catalog', hasIcon: true },
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
    <header className="bg-white sticky top-0 z-50 shadow-sm">
      {/* Main header row */}
      <div className="container-custom">
        <div className="flex items-center justify-between py-3 gap-4">
          {/* Mobile menu button */}
          <button
            className="lg:hidden p-2"
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
            <div className="hidden sm:block text-[#62bb46] font-bold text-xl leading-tight">
              <div>ШКАФЫ</div>
              <div>КУПЕ</div>
            </div>
          </Link>

          {/* City selector */}
          <div className="hidden md:flex items-center gap-1 text-sm text-text-dark shrink-0">
            <svg className="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            </svg>
            <span>Москва</span>
            <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          {/* Search */}
          <div className="hidden lg:flex flex-1 max-w-md">
            <div className="flex w-full">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Поиск"
                className="flex-1 px-4 py-2 border border-r-0 border-border rounded-l focus:outline-none focus:border-[#62bb46]"
              />
              <button className="px-6 py-2 bg-[#62bb46] text-white font-medium rounded-r hover:bg-[#75c35c] transition-colors">
                Найти
              </button>
            </div>
          </div>

          {/* Working hours & Phone */}
          <div className="hidden xl:flex flex-col items-end text-sm shrink-0">
            <div className="text-text-muted text-xs">с 07:00 до 20:00 мск</div>
            <div className="text-text-muted text-xs">Горячая линия</div>
            <a href="tel:+74957447233" className="font-bold text-lg text-text-dark hover:text-[#62bb46]">
              8 (495) 744-72-33
            </a>
            <button className="text-[#62bb46] text-xs font-medium hover:underline uppercase">
              Заказать звонок
            </button>
          </div>

          {/* Messengers */}
          <div className="hidden xl:flex items-center gap-2 shrink-0">
            <a href="#" className="w-8 h-8 bg-[#0088cc] rounded flex items-center justify-center text-white hover:opacity-80">
              <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
              </svg>
            </a>
            <a href="#" className="w-8 h-8 bg-[#25D366] rounded flex items-center justify-center text-white hover:opacity-80">
              <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
              </svg>
            </a>
          </div>

          {/* Icons */}
          <div className="flex items-center gap-1 sm:gap-3">
            {/* Login */}
            <Link href="/login" className="hidden sm:flex flex-col items-center p-2 hover:text-[#62bb46]">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span className="text-xs hidden xl:block">войти</span>
            </Link>

            {/* Compare */}
            <Link href="/compare" className="hidden sm:flex flex-col items-center p-2 hover:text-[#62bb46] relative">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span className="absolute -top-1 -right-1 bg-[#62bb46] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
            </Link>

            {/* Wishlist */}
            <Link href="/wishlist" className="hidden sm:flex flex-col items-center p-2 hover:text-[#62bb46] relative">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
              <span className="absolute -top-1 -right-1 bg-[#62bb46] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
            </Link>

            {/* Cart */}
            <Link href="/cart" className="flex flex-col items-center p-2 hover:text-[#62bb46] relative">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span className="absolute -top-1 -right-1 bg-[#62bb46] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
            </Link>
          </div>
        </div>
      </div>

      {/* Green Navigation Menu */}
      <nav className="bg-[#62bb46] hidden lg:block">
        <div className="container-custom">
          <ul className="flex items-center">
            {menuItems.map((item, index) => (
              <li key={item.href} className={`border-r border-[#408931] ${index === 0 ? 'border-l' : ''}`}>
                <Link
                  href={item.href}
                  className="flex items-center gap-2 px-4 py-3 text-white font-medium hover:bg-[#75c35c] transition-colors text-sm"
                >
                  {item.hasIcon && (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
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
              <button className="px-4 py-3 text-white hover:bg-[#75c35c] transition-colors">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                </svg>
              </button>
            </li>
          </ul>
        </div>
      </nav>

      {/* Mobile menu */}
      {isMobileMenuOpen && (
        <div className="lg:hidden border-t border-border-light bg-white">
          {/* Mobile search */}
          <div className="container-custom py-3">
            <div className="flex">
              <input
                type="text"
                placeholder="Поиск"
                className="flex-1 px-4 py-2 border border-r-0 border-border rounded-l focus:outline-none"
              />
              <button className="px-4 py-2 bg-[#62bb46] text-white rounded-r">
                Найти
              </button>
            </div>
          </div>
          {/* Mobile nav */}
          <nav className="bg-[#62bb46]">
            <ul className="divide-y divide-[#408931]">
              {menuItems.map((item) => (
                <li key={item.href}>
                  <Link
                    href={item.href}
                    className="flex items-center gap-2 px-4 py-3 text-white"
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
          <div className="container-custom py-4 space-y-2">
            <a href="tel:+74957447233" className="flex items-center gap-2 font-bold text-lg">
              8 (495) 744-72-33
            </a>
            <div className="text-sm text-text-muted">с 07:00 до 20:00 мск</div>
            <button className="text-[#62bb46] font-medium">Заказать звонок</button>
          </div>
        </div>
      )}
    </header>
  );
}
