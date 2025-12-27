'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface DashboardData {
  reviews: {
    active: number;
    pending: number;
    total: number;
  };
  promotions: {
    active: number;
    daysToNearest: number | null;
    daysToFarthest: number | null;
  };
  banners: {
    active: number;
    inactive: number;
  };
  regions: {
    total: number;
    active: number;
  };
  cities: {
    total: number;
    active: number;
  };
  salons: {
    total: number;
    active: number;
  };
  users: {
    total: number;
    inactive: Array<{
      id: number;
      username: string;
      last_login: string | null;
      created_at: string;
    }>;
  };
}

function formatDate(dateString: string | null): string {
  if (!dateString) return 'Никогда';
  return new Date(dateString).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function pluralize(n: number, forms: [string, string, string]): string {
  const n100 = Math.abs(n) % 100;
  const n10 = n100 % 10;
  if (n100 > 10 && n100 < 20) return forms[2];
  if (n10 > 1 && n10 < 5) return forms[1];
  if (n10 === 1) return forms[0];
  return forms[2];
}

export default function AdminDashboard() {
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/e1admin/dashboard')
      .then((res) => res.json())
      .then((response) => {
        if (response.success) {
          setData(response.data);
        }
      })
      .catch((error) => {
        console.error('Error fetching dashboard data:', error);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-gray-900">Дашборд</h1>
          <p className="text-gray-600 mt-1">Обзор администрирования сайта</p>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : data ? (
          <div className="space-y-8">
            {/* Reviews Section */}
            <section>
              <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Отзывы
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/e1admin/reviews?status=pending" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.reviews.pending}</div>
                      <div className="text-sm text-gray-600">Ожидают модерации</div>
                    </div>
                  </div>
                </Link>

                <Link href="/e1admin/reviews?status=active" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.reviews.active}</div>
                      <div className="text-sm text-gray-600">Опубликовано</div>
                    </div>
                  </div>
                </Link>

                <Link href="/e1admin/reviews" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.reviews.total}</div>
                      <div className="text-sm text-gray-600">Всего отзывов</div>
                    </div>
                  </div>
                </Link>
              </div>
            </section>

            {/* Promotions Section */}
            <section>
              <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg className="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                </svg>
                Акции
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/e1admin/promotions?status=active" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.promotions.active}</div>
                      <div className="text-sm text-gray-600">Активных акций</div>
                    </div>
                  </div>
                </Link>

                <div className="bg-white rounded-xl border border-gray-200 p-5">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">
                        {data.promotions.daysToNearest !== null ? data.promotions.daysToNearest : '—'}
                      </div>
                      <div className="text-sm text-gray-600">
                        {data.promotions.daysToNearest !== null
                          ? `${pluralize(data.promotions.daysToNearest, ['день', 'дня', 'дней'])} до ближайшей`
                          : 'Нет активных акций'}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 p-5">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">
                        {data.promotions.daysToFarthest !== null ? data.promotions.daysToFarthest : '—'}
                      </div>
                      <div className="text-sm text-gray-600">
                        {data.promotions.daysToFarthest !== null
                          ? `${pluralize(data.promotions.daysToFarthest, ['день', 'дня', 'дней'])} до самой долгой`
                          : 'Нет активных акций'}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            {/* Banners Section */}
            <section>
              <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg className="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Баннеры
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link href="/e1admin/banners" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.banners.active}</div>
                      <div className="text-sm text-gray-600">Активных баннеров</div>
                    </div>
                  </div>
                </Link>

                <Link href="/e1admin/banners" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.banners.inactive}</div>
                      <div className="text-sm text-gray-600">Неактивных баннеров</div>
                    </div>
                  </div>
                </Link>
              </div>
            </section>

            {/* Geography Section */}
            <section>
              <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg className="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                География
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/e1admin/regions" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">
                        {data.regions.active} <span className="text-base font-normal text-gray-500">/ {data.regions.total}</span>
                      </div>
                      <div className="text-sm text-gray-600">Регионов (активных / всего)</div>
                    </div>
                  </div>
                </Link>

                <Link href="/e1admin/cities" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">
                        {data.cities.active} <span className="text-base font-normal text-gray-500">/ {data.cities.total}</span>
                      </div>
                      <div className="text-sm text-gray-600">Городов (активных / всего)</div>
                    </div>
                  </div>
                </Link>

                <Link href="/e1admin/salons" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">
                        {data.salons.active} <span className="text-base font-normal text-gray-500">/ {data.salons.total}</span>
                      </div>
                      <div className="text-sm text-gray-600">Салонов (активных / всего)</div>
                    </div>
                  </div>
                </Link>
              </div>
            </section>

            {/* Users Section */}
            <section>
              <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Администраторы
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link href="/e1admin/users" className="bg-white rounded-xl border border-gray-200 p-5 hover:border-[#7cb342] transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                      <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="text-2xl font-bold text-gray-900">{data.users.total}</div>
                      <div className="text-sm text-gray-600">Всего администраторов</div>
                    </div>
                  </div>
                </Link>

                {data.users.inactive.length > 0 && (
                  <div className="bg-white rounded-xl border border-orange-200 p-5">
                    <div className="flex items-start gap-4">
                      <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg className="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="text-sm font-medium text-orange-800 mb-2">
                          Не заходили более 3 месяцев:
                        </div>
                        <div className="space-y-1">
                          {data.users.inactive.map((user) => (
                            <div key={user.id} className="text-sm text-gray-600 flex justify-between">
                              <span className="font-medium truncate">{user.username}</span>
                              <span className="text-gray-400 ml-2 flex-shrink-0">
                                {formatDate(user.last_login)}
                              </span>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </section>

            {/* Quick actions */}
            <section>
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Быстрые действия</h2>
              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <Link
                    href="/e1admin/reviews?status=pending"
                    className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#7cb342] hover:bg-green-50 transition-colors"
                  >
                    <div className="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                      <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <div className="font-medium text-gray-900">Модерировать отзывы</div>
                      <div className="text-sm text-gray-500">Проверить новые</div>
                    </div>
                  </Link>

                  <Link
                    href="/e1admin/promotions"
                    className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#7cb342] hover:bg-green-50 transition-colors"
                  >
                    <div className="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                      <svg className="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                    </div>
                    <div>
                      <div className="font-medium text-gray-900">Создать акцию</div>
                      <div className="text-sm text-gray-500">Добавить новую</div>
                    </div>
                  </Link>

                  <Link
                    href="/e1admin/banners"
                    className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#7cb342] hover:bg-green-50 transition-colors"
                  >
                    <div className="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                      <svg className="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <div className="font-medium text-gray-900">Управлять баннерами</div>
                      <div className="text-sm text-gray-500">Настроить слайдер</div>
                    </div>
                  </Link>

                  <Link
                    href="/e1admin/users"
                    className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#7cb342] hover:bg-green-50 transition-colors"
                  >
                    <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                      <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                      </svg>
                    </div>
                    <div>
                      <div className="font-medium text-gray-900">Добавить админа</div>
                      <div className="text-sm text-gray-500">Создать пользователя</div>
                    </div>
                  </Link>
                </div>
              </div>
            </section>
          </div>
        ) : (
          <div className="text-center py-12 text-gray-500">
            Ошибка загрузки данных
          </div>
        )}
      </div>
    </AdminPageWrapper>
  );
}
