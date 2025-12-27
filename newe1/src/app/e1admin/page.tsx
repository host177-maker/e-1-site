'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface ReviewCounts {
  active: number;
  pending: number;
  total: number;
}

interface UserCount {
  total: number;
}

export default function AdminDashboard() {
  const [reviewCounts, setReviewCounts] = useState<ReviewCounts | null>(null);
  const [userCount, setUserCount] = useState<UserCount | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      fetch('/api/e1admin/reviews?limit=1').then((res) => res.json()),
      fetch('/api/e1admin/users').then((res) => res.json()),
    ])
      .then(([reviewsData, usersData]) => {
        if (reviewsData.success) {
          setReviewCounts(reviewsData.counts);
        }
        if (usersData.success) {
          setUserCount({ total: usersData.data.length });
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
        ) : (
          <>
            {/* Stats cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-gray-900">
                      {reviewCounts?.pending || 0}
                    </div>
                    <div className="text-sm text-gray-600">Ожидают модерации</div>
                  </div>
                </div>
                <Link
                  href="/e1admin/reviews?status=pending"
                  className="mt-4 inline-flex items-center text-sm text-[#7cb342] hover:text-[#689f38] font-medium"
                >
                  Просмотреть
                  <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              </div>

              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-gray-900">
                      {reviewCounts?.active || 0}
                    </div>
                    <div className="text-sm text-gray-600">Опубликовано отзывов</div>
                  </div>
                </div>
                <Link
                  href="/e1admin/reviews?status=active"
                  className="mt-4 inline-flex items-center text-sm text-[#7cb342] hover:text-[#689f38] font-medium"
                >
                  Просмотреть
                  <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              </div>

              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-gray-900">
                      {reviewCounts?.total || 0}
                    </div>
                    <div className="text-sm text-gray-600">Всего отзывов</div>
                  </div>
                </div>
                <Link
                  href="/e1admin/reviews"
                  className="mt-4 inline-flex items-center text-sm text-[#7cb342] hover:text-[#689f38] font-medium"
                >
                  Управление
                  <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              </div>

              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-gray-900">
                      {userCount?.total || 0}
                    </div>
                    <div className="text-sm text-gray-600">Администраторов</div>
                  </div>
                </div>
                <Link
                  href="/e1admin/users"
                  className="mt-4 inline-flex items-center text-sm text-[#7cb342] hover:text-[#689f38] font-medium"
                >
                  Управление
                  <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              </div>
            </div>

            {/* Quick actions */}
            <div className="bg-white rounded-xl border border-gray-200 p-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-4">Быстрые действия</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    <div className="text-sm text-gray-500">Проверить новые отзывы</div>
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
                    <div className="font-medium text-gray-900">Добавить администратора</div>
                    <div className="text-sm text-gray-500">Создать нового пользователя</div>
                  </div>
                </Link>

                <Link
                  href="/reviews"
                  target="_blank"
                  className="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-[#7cb342] hover:bg-green-50 transition-colors"
                >
                  <div className="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg className="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </div>
                  <div>
                    <div className="font-medium text-gray-900">Страница отзывов</div>
                    <div className="text-sm text-gray-500">Посмотреть на сайте</div>
                  </div>
                </Link>
              </div>
            </div>
          </>
        )}
      </div>
    </AdminPageWrapper>
  );
}
