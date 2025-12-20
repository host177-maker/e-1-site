'use client';

import Link from 'next/link';
import { useState } from 'react';
import Navigation from './Navigation';

// E1 Logo SVG component (green color as original)
function Logo() {
  return (
    <svg viewBox="0 0 177 56" className="h-10 w-auto">
      <g fill="#62BB46">
        <polygon points="76.82,9.79 76.82,51.74 65.78,51.74 65.78,15.47 69.73,15.47 58.69,9.94 58.69,4.26 65.78,4.26" />
        <polygon points="47.48,15.47 11.05,15.47 0,10.03 0,4.26 36.43,4.26 47.48,9.79" />
        <polygon points="11.06,15.47 11.06,22.4 36.43,22.4 47.48,27.92 47.48,33.6 11.22,33.6 0,28.08 0,15.47" />
        <polygon points="47.48,46.06 47.48,51.74 11.05,51.74 0,46.22 0,33.6 11.22,33.6 11.22,40.53 36.43,40.53" />
      </g>
      <g fill="#333333">
        <path d="M90.22,4.96h4.25v13.17h3.25V4.96h4.23v13.17h3.27V4.96h4.23v17.2H90.22V4.96z" />
        <path d="M111.86,4.96h4.25v6.73h1.06l2.83-6.73h4.91l-4.05,8.6l4.2,8.6h-4.91l-2.95-6.71h-1.08v6.71h-4.25V4.96z" />
        <path d="M130.3,4.96h4.28l5.46,17.2h-4.42l-0.71-2.58H130l-0.71,2.58h-4.45L130.3,4.96z M134.01,16.09l-0.81-2.95c-0.3-1.13-0.57-2.33-0.76-3.71c-0.2,1.38-0.44,2.58-0.74,3.71l-0.83,2.95H134.01z" />
        <path d="M146.57,21c-4.06-0.32-6.98-3.12-6.98-7.45c0-4.15,2.92-7.13,6.98-7.44V4h4.05v2.11c4.03,0.37,6.9,3.32,6.9,7.44c0,4.13-2.88,7.08-6.9,7.45v2.11h-4.05V21z M146.67,9.8c-1.97,0.22-2.75,1.67-2.75,3.76s0.86,3.54,2.75,3.76V9.8z M153.2,13.56c0-2.09-0.79-3.51-2.65-3.76v7.52C152.42,17.07,153.2,15.64,153.2,13.56z" />
        <path d="M159.06,4.96h4.25v5.75h1.92c3.29,0,5.92,1.84,5.92,5.73s-2.63,5.73-5.92,5.73h-6.17V4.96z M164.89,18.3c1.23,0,2.02-0.64,2.02-1.87c0-1.2-0.81-1.87-2.02-1.87h-1.57v3.74H164.89z M177,22.16h-4.25V4.96H177V22.16z" />
        <path d="M90.22,34.46h4.25v6.73h1.06l2.83-6.73h4.91l-4.05,8.6l4.2,8.6h-4.91l-2.95-6.71h-1.08v6.71h-4.25V34.46z" />
        <path d="M117.48,34.46l-4.64,12.97c-1.35,3.71-3.27,4.57-5.23,4.57c-0.96,0-1.52-0.1-2.31-0.34V47.9c0.71,0.17,1.33,0.2,1.84,0.1c0.69-0.07,1.11-0.49,1.52-1.18l-5.45-12.36h4.5l2.97,7.72l2.29-7.72H117.48z" />
        <path d="M118.65,34.46h12.6v17.2h-4.23V38.49h-4.13v13.17h-4.25V34.46z" />
        <path d="M133.69,34.46h10.15v3.78h-5.9v2.92h5.33v3.69h-5.33v2.97h6.05v3.83h-10.3V34.46z" />
      </g>
    </svg>
  );
}

export default function Header() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);

  return (
    <header className="bg-white shadow-sm sticky top-0 z-50">
      {/* Top bar */}
      <div className="bg-bg-gray border-b border-border-light">
        <div className="container-custom">
          <div className="flex items-center justify-between py-2 text-sm">
            {/* Region */}
            <div className="flex items-center gap-2 text-text-medium">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>Москва</span>
            </div>

            {/* Phone */}
            <div className="hidden md:flex items-center gap-4">
              <a href="tel:+78000000000" className="flex items-center gap-2 text-text-dark hover:text-primary">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span className="font-medium">+7 800 000-00-00</span>
              </a>
              <button className="text-primary hover:text-primary-hover text-sm font-medium">
                Заказать звонок
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Main header */}
      <div className="container-custom">
        <div className="flex items-center justify-between py-4">
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

          {/* Logo */}
          <Link href="/" className="flex items-center">
            <Logo />
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden lg:block flex-1 mx-8">
            <Navigation />
          </div>

          {/* Actions */}
          <div className="flex items-center gap-3">
            {/* Search */}
            <button
              className="p-2 hover:text-primary"
              onClick={() => setIsSearchOpen(!isSearchOpen)}
              aria-label="Поиск"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>

            {/* Wishlist */}
            <Link href="/wishlist" className="p-2 hover:text-primary relative hidden sm:block">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
              <span className="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                0
              </span>
            </Link>

            {/* Compare */}
            <Link href="/compare" className="p-2 hover:text-primary relative hidden sm:block">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span className="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                0
              </span>
            </Link>

            {/* Cart */}
            <Link href="/cart" className="p-2 hover:text-primary relative">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span className="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                0
              </span>
            </Link>
          </div>
        </div>

        {/* Search bar */}
        {isSearchOpen && (
          <div className="pb-4">
            <div className="relative">
              <input
                type="text"
                placeholder="Поиск по сайту..."
                className="w-full px-4 py-3 border border-border rounded-lg focus:outline-none focus:border-primary"
              />
              <button className="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-primary">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Mobile menu */}
      {isMobileMenuOpen && (
        <div className="lg:hidden border-t border-border-light">
          <div className="container-custom py-4">
            <Navigation isMobile />
            <div className="mt-4 pt-4 border-t border-border-light">
              <a href="tel:+78000000000" className="flex items-center gap-2 text-text-dark py-2">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span className="font-medium">+7 800 000-00-00</span>
              </a>
              <button className="text-primary hover:text-primary-hover text-sm mt-2 font-medium">
                Заказать звонок
              </button>
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
