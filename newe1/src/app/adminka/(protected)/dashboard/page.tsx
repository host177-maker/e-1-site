'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

interface Stats {
  pagesCount: number;
  usersCount: number;
  menuItemsCount: number;
}

export default function DashboardPage() {
  const [stats, setStats] = useState<Stats>({ pagesCount: 0, usersCount: 0, menuItemsCount: 0 });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const [pagesRes, usersRes, menuRes] = await Promise.all([
          fetch('/api/admin/pages'),
          fetch('/api/admin/users'),
          fetch('/api/admin/menu'),
        ]);

        const [pagesData, usersData, menuData] = await Promise.all([
          pagesRes.json(),
          usersRes.json(),
          menuRes.json(),
        ]);

        const menuItemsCount = menuData.menu
          ? menuData.menu.topMenu.length +
            menuData.menu.mainMenu.length +
            menuData.menu.footerMenu.length
          : 0;

        setStats({
          pagesCount: pagesData.pages?.length || 0,
          usersCount: usersData.users?.length || 0,
          menuItemsCount,
        });
      } catch (error) {
        console.error('Error fetching stats:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  const cards = [
    {
      title: 'Страницы',
      value: stats.pagesCount,
      href: '/adminka/pages',
      icon: '📄',
      color: 'bg-blue-500',
    },
    {
      title: 'Пользователи',
      value: stats.usersCount,
      href: '/adminka/users',
      icon: '👥',
      color: 'bg-green-500',
    },
    {
      title: 'Пункты меню',
      value: stats.menuItemsCount,
      href: '/adminka/menu',
      icon: '📋',
      color: 'bg-purple-500',
    },
  ];

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Панель управления</h1>

      {loading ? (
        <div className="text-gray-600">Загрузка статистики...</div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          {cards.map((card) => (
            <Link
              key={card.href}
              href={card.href}
              className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-gray-600 text-sm">{card.title}</p>
                  <p className="text-3xl font-bold text-gray-800 mt-1">{card.value}</p>
                </div>
                <div
                  className={`${card.color} text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl`}
                >
                  {card.icon}
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}

      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Быстрые действия</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Link
            href="/adminka/pages/new"
            className="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
          >
            <span className="text-2xl mr-3">➕</span>
            <span className="text-blue-800">Создать страницу</span>
          </Link>
          <Link
            href="/adminka/users/new"
            className="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
          >
            <span className="text-2xl mr-3">👤</span>
            <span className="text-green-800">Добавить администратора</span>
          </Link>
          <Link
            href="/adminka/menu"
            className="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
          >
            <span className="text-2xl mr-3">📋</span>
            <span className="text-purple-800">Редактировать меню</span>
          </Link>
          <Link
            href="/"
            target="_blank"
            className="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <span className="text-2xl mr-3">🌐</span>
            <span className="text-gray-800">Открыть сайт</span>
          </Link>
        </div>
      </div>
    </div>
  );
}
