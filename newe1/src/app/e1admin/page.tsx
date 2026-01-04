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
  orders: {
    today: { count: number; sum: number };
    yesterday: { count: number; sum: number };
    week: { count: number; sum: number };
    month: { count: number; sum: number };
  };
  warehouses: {
    total: number;
    active: number;
  };
  citiesWithoutWarehouses: Array<{ id: number; name: string }>;
  citiesWithoutPriceGroup: Array<{ id: number; name: string }>;
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

function formatCurrency(amount: number): string {
  if (amount >= 1000000) {
    return (amount / 1000000).toFixed(1).replace('.0', '') + ' млн ₽';
  }
  if (amount >= 1000) {
    return Math.round(amount / 1000) + ' тыс ₽';
  }
  return amount.toLocaleString('ru-RU') + ' ₽';
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
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-gray-900">Дашборд</h1>
          <p className="text-gray-600 mt-1">Обзор администрирования сайта</p>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : data ? (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Orders Statistics */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden lg:col-span-2">
              <Link
                href="/e1admin/orders"
                className="flex items-center justify-between p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors"
              >
                <div className="flex items-center gap-3">
                  <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  <span className="font-semibold text-gray-900">Заказы</span>
                </div>
                <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                </svg>
              </Link>
              <div className="grid grid-cols-2 md:grid-cols-4 divide-x divide-gray-100">
                <div className="p-4 text-center">
                  <div className="text-xs text-gray-500 mb-1">Сегодня</div>
                  <div className="text-xl font-bold text-gray-900">{data.orders.today.count}</div>
                  <div className="text-sm text-green-600 font-medium">{formatCurrency(data.orders.today.sum)}</div>
                </div>
                <div className="p-4 text-center">
                  <div className="text-xs text-gray-500 mb-1">Вчера</div>
                  <div className="text-xl font-bold text-gray-900">{data.orders.yesterday.count}</div>
                  <div className="text-sm text-gray-600">{formatCurrency(data.orders.yesterday.sum)}</div>
                </div>
                <div className="p-4 text-center">
                  <div className="text-xs text-gray-500 mb-1">7 дней</div>
                  <div className="text-xl font-bold text-gray-900">{data.orders.week.count}</div>
                  <div className="text-sm text-gray-600">{formatCurrency(data.orders.week.sum)}</div>
                </div>
                <div className="p-4 text-center">
                  <div className="text-xs text-gray-500 mb-1">30 дней</div>
                  <div className="text-xl font-bold text-gray-900">{data.orders.month.count}</div>
                  <div className="text-sm text-gray-600">{formatCurrency(data.orders.month.sum)}</div>
                </div>
              </div>
            </div>

            {/* Reviews & Promotions */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div className="border-b border-gray-100">
                <Link
                  href="/e1admin/reviews"
                  className="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span className="font-semibold text-gray-900">Отзывы</span>
                  </div>
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
                <div className="px-4 pb-4 grid grid-cols-3 gap-4 text-sm">
                  <Link href="/e1admin/reviews?status=pending" className="hover:text-[#7cb342]">
                    <div className="text-xl font-bold text-gray-900">{data.reviews.pending}</div>
                    <div className="text-gray-500">На модерации</div>
                  </Link>
                  <Link href="/e1admin/reviews?status=active" className="hover:text-[#7cb342]">
                    <div className="text-xl font-bold text-gray-900">{data.reviews.active}</div>
                    <div className="text-gray-500">Опубликовано</div>
                  </Link>
                  <div>
                    <div className="text-xl font-bold text-gray-900">{data.reviews.total}</div>
                    <div className="text-gray-500">Всего</div>
                  </div>
                </div>
              </div>

              <div>
                <Link
                  href="/e1admin/promotions"
                  className="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <svg className="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    <span className="font-semibold text-gray-900">Акции</span>
                  </div>
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
                <div className="px-4 pb-4 grid grid-cols-3 gap-4 text-sm">
                  <div>
                    <div className="text-xl font-bold text-gray-900">{data.promotions.active}</div>
                    <div className="text-gray-500">Активных</div>
                  </div>
                  <div>
                    <div className="text-xl font-bold text-gray-900">
                      {data.promotions.daysToNearest !== null ? data.promotions.daysToNearest : '—'}
                    </div>
                    <div className="text-gray-500">
                      {data.promotions.daysToNearest !== null
                        ? `${pluralize(data.promotions.daysToNearest, ['день', 'дня', 'дней'])} до ближ.`
                        : 'До ближайшей'}
                    </div>
                  </div>
                  <div>
                    <div className="text-xl font-bold text-gray-900">
                      {data.promotions.daysToFarthest !== null ? data.promotions.daysToFarthest : '—'}
                    </div>
                    <div className="text-gray-500">
                      {data.promotions.daysToFarthest !== null
                        ? `${pluralize(data.promotions.daysToFarthest, ['день', 'дня', 'дней'])} до дальн.`
                        : 'До самой долгой'}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Banners & Users */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div className="border-b border-gray-100">
                <Link
                  href="/e1admin/banners"
                  className="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <svg className="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span className="font-semibold text-gray-900">Баннеры</span>
                  </div>
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
                <div className="px-4 pb-4 grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <div className="text-xl font-bold text-green-600">{data.banners.active}</div>
                    <div className="text-gray-500">Активных</div>
                  </div>
                  <div>
                    <div className="text-xl font-bold text-gray-400">{data.banners.inactive}</div>
                    <div className="text-gray-500">Неактивных</div>
                  </div>
                </div>
              </div>

              <div>
                <Link
                  href="/e1admin/users"
                  className="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span className="font-semibold text-gray-900">Администраторы</span>
                  </div>
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
                <div className="px-4 pb-4">
                  <div className="text-sm">
                    <div className="text-xl font-bold text-gray-900">{data.users.total}</div>
                    <div className="text-gray-500">Всего администраторов</div>
                  </div>
                  {data.users.inactive.length > 0 && (
                    <div className="mt-3 pt-3 border-t border-gray-100">
                      <div className="text-xs font-medium text-orange-600 mb-2">
                        Не заходили более 3 месяцев:
                      </div>
                      <div className="space-y-1">
                        {data.users.inactive.map((user) => (
                          <div key={user.id} className="text-xs text-gray-600 flex justify-between">
                            <span className="font-medium">{user.username}</span>
                            <span className="text-gray-400">{formatDate(user.last_login)}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Geography */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden lg:col-span-2">
              <div className="p-4 border-b border-gray-100">
                <div className="flex items-center gap-3">
                  <svg className="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span className="font-semibold text-gray-900">География</span>
                </div>
              </div>
              <div className="grid grid-cols-2 md:grid-cols-4 divide-x divide-gray-100">
                <Link href="/e1admin/regions" className="p-4 hover:bg-gray-50 transition-colors text-center">
                  <div className="text-2xl font-bold text-gray-900">
                    {data.regions.active}<span className="text-base font-normal text-gray-400">/{data.regions.total}</span>
                  </div>
                  <div className="text-sm text-gray-500">Регионов</div>
                </Link>
                <Link href="/e1admin/cities" className="p-4 hover:bg-gray-50 transition-colors text-center">
                  <div className="text-2xl font-bold text-gray-900">
                    {data.cities.active}<span className="text-base font-normal text-gray-400">/{data.cities.total}</span>
                  </div>
                  <div className="text-sm text-gray-500">Городов</div>
                </Link>
                <Link href="/e1admin/warehouses" className="p-4 hover:bg-gray-50 transition-colors text-center">
                  <div className="text-2xl font-bold text-gray-900">
                    {data.warehouses.active}<span className="text-base font-normal text-gray-400">/{data.warehouses.total}</span>
                  </div>
                  <div className="text-sm text-gray-500">Складов</div>
                </Link>
                <Link href="/e1admin/salons" className="p-4 hover:bg-gray-50 transition-colors text-center">
                  <div className="text-2xl font-bold text-gray-900">
                    {data.salons.active}<span className="text-base font-normal text-gray-400">/{data.salons.total}</span>
                  </div>
                  <div className="text-sm text-gray-500">Салонов</div>
                </Link>
              </div>
              {/* Cities without warehouses and service groups */}
              {(data.citiesWithoutWarehouses.length > 0 || data.citiesWithoutPriceGroup.length > 0) && (
                <div className="border-t border-gray-100 p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                  {data.citiesWithoutWarehouses.length > 0 && (
                    <div>
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-2xl font-bold text-orange-500">{data.citiesWithoutWarehouses.length}</span>
                        <span className="text-sm text-gray-500">Без складов</span>
                      </div>
                      <div className="text-xs text-gray-500">
                        {data.citiesWithoutWarehouses.map((city, idx) => (
                          <span key={city.id}>
                            <Link href={`/e1admin/cities?search=${encodeURIComponent(city.name)}`} className="hover:text-[#7cb342]">
                              {city.name}
                            </Link>
                            {idx < data.citiesWithoutWarehouses.length - 1 && ', '}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}
                  {data.citiesWithoutPriceGroup.length > 0 && (
                    <div>
                      <div className="flex items-center gap-2 mb-2">
                        <span className="text-2xl font-bold text-orange-500">{data.citiesWithoutPriceGroup.length}</span>
                        <span className="text-sm text-gray-500">Без группы услуг</span>
                      </div>
                      <div className="text-xs text-gray-500">
                        {data.citiesWithoutPriceGroup.map((city, idx) => (
                          <span key={city.id}>
                            <Link href={`/e1admin/cities?search=${encodeURIComponent(city.name)}`} className="hover:text-[#7cb342]">
                              {city.name}
                            </Link>
                            {idx < data.citiesWithoutPriceGroup.length - 1 && ', '}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              )}
            </div>
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
