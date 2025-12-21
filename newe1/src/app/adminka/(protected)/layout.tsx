'use client';

import { useEffect, useState } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import Link from 'next/link';

interface User {
  id: string;
  username: string;
  role: string;
}

export default function ProtectedLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const pathname = usePathname();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [sidebarOpen, setSidebarOpen] = useState(true);

  useEffect(() => {
    const checkAuth = async () => {
      try {
        const res = await fetch('/api/auth/me');
        if (!res.ok) {
          router.push('/adminka');
          return;
        }
        const data = await res.json();
        setUser(data.user);
      } catch {
        router.push('/adminka');
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, [router]);

  const handleLogout = async () => {
    await fetch('/api/auth/logout', { method: 'POST' });
    router.push('/adminka');
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-gray-600">Загрузка...</div>
      </div>
    );
  }

  const menuItems = [
    { href: '/adminka/dashboard', label: 'Главная', icon: '🏠' },
    { href: '/adminka/pages', label: 'Страницы', icon: '📄' },
    { href: '/adminka/menu', label: 'Меню', icon: '📋' },
    { href: '/adminka/users', label: 'Пользователи', icon: '👥' },
    { href: '/adminka/settings', label: 'Настройки', icon: '⚙️' },
  ];

  return (
    <div className="min-h-screen flex">
      {/* Sidebar */}
      <aside
        className={`${
          sidebarOpen ? 'w-64' : 'w-16'
        } bg-gray-800 text-white transition-all duration-300 flex flex-col`}
      >
        <div className="p-4 border-b border-gray-700 flex items-center justify-between">
          {sidebarOpen && <span className="font-bold text-lg">E-1.RU</span>}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="text-gray-400 hover:text-white"
          >
            {sidebarOpen ? '◀' : '▶'}
          </button>
        </div>

        <nav className="flex-1 py-4">
          {menuItems.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={`flex items-center px-4 py-3 hover:bg-gray-700 transition-colors ${
                pathname === item.href ? 'bg-gray-700 border-l-4 border-green-500' : ''
              }`}
            >
              <span className="text-xl">{item.icon}</span>
              {sidebarOpen && <span className="ml-3">{item.label}</span>}
            </Link>
          ))}
        </nav>

        <div className="p-4 border-t border-gray-700">
          {sidebarOpen && (
            <div className="text-sm text-gray-400 mb-2">
              {user?.username}
            </div>
          )}
          <button
            onClick={handleLogout}
            className="flex items-center text-red-400 hover:text-red-300"
          >
            <span>🚪</span>
            {sidebarOpen && <span className="ml-2">Выход</span>}
          </button>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex-1 overflow-auto">
        <div className="p-6">
          {children}
        </div>
      </main>
    </div>
  );
}
