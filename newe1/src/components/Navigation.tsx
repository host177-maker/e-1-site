'use client';

import Link from 'next/link';
import { useState } from 'react';

interface NavItem {
  label: string;
  href: string;
  children?: NavItem[];
}

const navItems: NavItem[] = [
  {
    label: 'Каталог',
    href: '/catalog',
    children: [
      { label: 'Шкафы-купе', href: '/catalog/shkafi-kupe' },
      { label: 'Гардеробные', href: '/catalog/garderobnye' },
      { label: 'Встроенные шкафы', href: '/catalog/vstroennye-shkafi' },
      { label: 'Прихожие', href: '/catalog/prihozhie' },
    ],
  },
  { label: 'О компании', href: '/about' },
  { label: 'Услуги', href: '/services' },
  { label: 'Как купить', href: '/how-to-buy' },
  { label: 'Производители', href: '/brands' },
  { label: 'Магазины', href: '/stores' },
  { label: 'Контакты', href: '/contacts' },
];

interface NavigationProps {
  isMobile?: boolean;
}

export default function Navigation({ isMobile = false }: NavigationProps) {
  const [openDropdown, setOpenDropdown] = useState<string | null>(null);

  if (isMobile) {
    return (
      <nav className="flex flex-col gap-2">
        {navItems.map((item) => (
          <div key={item.href}>
            {item.children ? (
              <div>
                <button
                  onClick={() => setOpenDropdown(openDropdown === item.href ? null : item.href)}
                  className="flex items-center justify-between w-full py-2 text-text-dark hover:text-primary"
                >
                  <span>{item.label}</span>
                  <svg
                    className={`w-4 h-4 transition-transform ${openDropdown === item.href ? 'rotate-180' : ''}`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                {openDropdown === item.href && (
                  <div className="pl-4 border-l-2 border-primary ml-2">
                    {item.children.map((child) => (
                      <Link
                        key={child.href}
                        href={child.href}
                        className="block py-2 text-text-medium hover:text-primary"
                      >
                        {child.label}
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            ) : (
              <Link
                href={item.href}
                className="block py-2 text-text-dark hover:text-primary"
              >
                {item.label}
              </Link>
            )}
          </div>
        ))}
      </nav>
    );
  }

  return (
    <nav className="flex items-center gap-6">
      {navItems.map((item) => (
        <div key={item.href} className="relative group">
          {item.children ? (
            <>
              <button className="flex items-center gap-1 py-2 text-text-dark hover:text-primary font-medium">
                <span>{item.label}</span>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div className="absolute top-full left-0 mt-0 w-56 bg-white shadow-lg rounded-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                {item.children.map((child) => (
                  <Link
                    key={child.href}
                    href={child.href}
                    className="block px-4 py-2 text-text-dark hover:text-primary hover:bg-bg-light"
                  >
                    {child.label}
                  </Link>
                ))}
              </div>
            </>
          ) : (
            <Link
              href={item.href}
              className="py-2 text-text-dark hover:text-primary font-medium"
            >
              {item.label}
            </Link>
          )}
        </div>
      ))}
    </nav>
  );
}
