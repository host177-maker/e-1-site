'use client';

import Link from 'next/link';
import { useState } from 'react';
import { useCity } from '@/context/CityContext';
import CitySelector from './CitySelector';
import MobileCitySelector from './MobileCitySelector';
import MessengerModal from './MessengerModal';

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

// Service submenu structure - vertical list
const serviceSubmenu = [
  { label: 'Условия покупки', href: '/service/purchase-terms' },
  { label: 'Рассрочка', href: '/service/installment' },
  { label: 'Инструкции к мебели', href: '/service/instructions' },
  { label: 'Проверить статус заказа', href: 'https://booking.e-1.ru/check/', external: true },
  { label: 'Чат с отделом доставки', href: 'https://booking.e-1.ru/service/', external: true },
];

const menuItems = [
  { label: 'КАТАЛОГ', href: '/catalog' },
  { label: 'СЕРИИ', href: '/series' },
  { label: 'ШКАФЫ НА ЗАКАЗ', href: '/custom' },
  { label: 'ГАРДЕРОБНЫЕ', href: '/catalog/garderobnye' },
  { label: 'АКЦИИ', href: '/sales', hasLightning: true },
  { label: 'ПОКУПАТЕЛЮ', href: '/service', hasSubmenu: true },
  { label: 'ОТЗЫВЫ', href: '/reviews' },
  { label: 'АДРЕСА САЛОНОВ', href: '/stores' },
  { label: 'ГЕОГРАФИЯ ДОСТАВКИ', href: '/delivery' },
];

// Mobile-only menu item
const mobileOnlyItems = [
  { label: 'УЗНАТЬ СТАТУС ЗАКАЗА', href: 'https://booking.e-1.ru/check/', external: true },
];

export default function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [mobileServiceOpen, setMobileServiceOpen] = useState(false);
  const [isCitySelectorOpen, setIsCitySelectorOpen] = useState(false);
  const [isMobileCitySelectorOpen, setIsMobileCitySelectorOpen] = useState(false);
  const [isMessengerModalOpen, setIsMessengerModalOpen] = useState(false);
  const { city, isLoading } = useCity();

  return (
    <header className="sticky top-0 z-50">
      {/* Top bar - graphite color */}
      <div className="bg-[#3d4543] text-white">
        <div className="container-custom">
          <div className="flex items-center justify-between py-2 text-xs">
            {/* Left side badges */}
            <div className="hidden lg:flex items-center gap-3 xl:gap-5">
              {/* City selector */}
              <button
                onClick={() => setIsCitySelectorOpen(true)}
                className="flex items-center gap-1.5 hover:text-[#62bb46] transition-colors px-2 py-1 rounded hover:bg-white/10"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Город: <strong>{isLoading ? '...' : city.name}</strong></span>
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
              <Link href="/service/installment" className="flex items-center gap-1.5 hover:text-[#62bb46] transition-colors px-2 py-1 rounded hover:bg-white/10">
                <svg className="w-4 h-4 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Рассрочка 0-0-6</span>
              </Link>

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
            <div className="hidden lg:flex items-center gap-3 ml-auto">
              <span className="text-gray-400 text-[11px]">7:00–20:00</span>
              <div className="flex items-center gap-2 bg-[#62bb46] px-4 py-1.5 rounded">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <a href="tel:+78001001211" className="font-bold hover:underline">
                  8-800-100-12-11
                </a>
              </div>
            </div>

            {/* Mobile: city selector + installment + phone */}
            <div className="lg:hidden flex items-center justify-between w-full text-[11px]">
              {/* City selector - left */}
              <button
                onClick={() => setIsMobileCitySelectorOpen(true)}
                className="flex items-center gap-1 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{isLoading ? '...' : city.name}</span>
                <span className="text-[#62bb46] underline">сменить</span>
              </button>

              {/* Installment - center */}
              <div className="flex items-center gap-2">
                <div className="w-px h-3 bg-white/30"></div>
                <Link href="/service/installment" className="flex items-center gap-1 hover:text-[#62bb46] transition-colors">
                  <svg className="w-3.5 h-3.5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  <span>0-0-6</span>
                </Link>
                <div className="w-px h-3 bg-white/30"></div>
              </div>

              {/* Phone - right */}
              <a href="tel:+78001001211" className="font-bold hover:text-[#62bb46] transition-colors whitespace-nowrap">
                8-800-100-12-11
              </a>
            </div>
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
            <Link href="/" className="flex items-center gap-3 shrink-0" onClick={() => setIsMobileMenuOpen(false)}>
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

            {/* Messenger Video Button */}
            <div className="flex items-center shrink-0 ml-auto lg:ml-0">
              <button
                onClick={() => setIsMessengerModalOpen(true)}
                className="w-9 h-9 rounded-lg overflow-hidden hover:scale-110 transition-transform focus:outline-none focus:ring-2 focus:ring-[#62bb46] focus:ring-offset-2"
                title="Написать нам"
                aria-label="Открыть мессенджеры"
              >
                <video
                  className="w-full h-full object-cover"
                  autoPlay
                  loop
                  muted
                  playsInline
                >
                  <source src="/images/Messenger.mp4" type="video/mp4" />
                </video>
              </button>
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
                {item.hasSubmenu ? (
                  <span
                    className="flex items-center gap-1.5 px-2 xl:px-3 py-3 font-bold hover:bg-[#55a83d] transition-colors text-[12px] xl:text-[13px] whitespace-nowrap cursor-pointer"
                    style={{ color: 'white' }}
                  >
                    {item.label}
                    <svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                  </span>
                ) : (
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
                  </Link>
                )}
                {/* Dropdown for ПОКУПАТЕЛЮ */}
                {item.hasSubmenu && (
                  <div className="absolute left-0 top-full bg-white shadow-xl rounded-b-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[200px]">
                    <ul className="py-2">
                      {serviceSubmenu.map((subItem) => (
                        <li key={subItem.href}>
                          {'external' in subItem && subItem.external ? (
                            <a
                              href={subItem.href}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="block px-4 py-2 text-[13px] text-gray-600 hover:text-[#62bb46] hover:bg-gray-50 transition-colors"
                            >
                              {subItem.label}
                            </a>
                          ) : (
                            <Link
                              href={subItem.href}
                              className="block px-4 py-2 text-[13px] text-gray-600 hover:text-[#62bb46] hover:bg-gray-50 transition-colors"
                            >
                              {subItem.label}
                            </Link>
                          )}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </li>
            ))}
          </ul>
        </div>
      </nav>

      {/* Mobile menu */}
      {isMobileMenuOpen && (
        <>
          {/* Overlay */}
          <div
            className="lg:hidden fixed inset-0 top-[88px] bg-black/50 z-40"
            onClick={() => setIsMobileMenuOpen(false)}
          />
          {/* Menu panel - 70% width on left */}
          <div className="lg:hidden fixed left-0 top-[88px] bottom-0 w-[70%] bg-white overflow-y-auto z-50 shadow-xl">
          {/* Mobile search */}
          <div className="px-4 py-3">
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
                  {item.hasSubmenu ? (
                    <div>
                      <button
                        onClick={() => setMobileServiceOpen(!mobileServiceOpen)}
                        className="flex items-center justify-between w-full px-4 py-3 font-bold"
                        style={{ color: 'white' }}
                      >
                        <span>{item.label}</span>
                        <svg
                          className={`w-4 h-4 transition-transform ${mobileServiceOpen ? 'rotate-180' : ''}`}
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                      </button>
                      {mobileServiceOpen && (
                        <div className="bg-white">
                          <ul>
                            {serviceSubmenu.map((subItem) => (
                              <li key={subItem.href} className="border-b border-gray-100 last:border-b-0">
                                {'external' in subItem && subItem.external ? (
                                  <a
                                    href={subItem.href}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="block px-4 py-2.5 text-sm text-gray-600 hover:text-[#62bb46] hover:bg-gray-50 transition-colors"
                                    onClick={() => {
                                      setMobileServiceOpen(false);
                                      setIsMobileMenuOpen(false);
                                    }}
                                  >
                                    {subItem.label}
                                  </a>
                                ) : (
                                  <Link
                                    href={subItem.href}
                                    className="block px-4 py-2.5 text-sm text-gray-600 hover:text-[#62bb46] hover:bg-gray-50 transition-colors"
                                    onClick={() => {
                                      setMobileServiceOpen(false);
                                      setIsMobileMenuOpen(false);
                                    }}
                                  >
                                    {subItem.label}
                                  </Link>
                                )}
                              </li>
                            ))}
                          </ul>
                        </div>
                      )}
                    </div>
                  ) : (
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
                  )}
                </li>
              ))}
              {/* Mobile-only menu items */}
              {mobileOnlyItems.map((item) => (
                <li key={item.href}>
                  <a
                    href={item.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center gap-2 px-4 py-3 font-bold"
                    style={{ color: 'white' }}
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    {item.label}
                  </a>
                </li>
              ))}
            </ul>
          </nav>
          </div>
        </>
      )}

      {/* City Selector Modal (Desktop) */}
      <CitySelector
        isOpen={isCitySelectorOpen}
        onClose={() => setIsCitySelectorOpen(false)}
      />

      {/* Mobile City Selector */}
      <MobileCitySelector
        isOpen={isMobileCitySelectorOpen}
        onClose={() => setIsMobileCitySelectorOpen(false)}
      />

      {/* Messenger Modal */}
      <MessengerModal
        isOpen={isMessengerModalOpen}
        onClose={() => setIsMessengerModalOpen(false)}
      />
    </header>
  );
}
