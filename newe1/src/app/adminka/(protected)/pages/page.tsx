'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

interface Page {
  id: string;
  slug: string;
  title: string;
  isPublished: boolean;
  createdAt: string;
  updatedAt: string;
  createdBy: string;
}

export default function PagesListPage() {
  const [pages, setPages] = useState<Page[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchPages = async () => {
    try {
      const res = await fetch('/api/admin/pages');
      const data = await res.json();
      setPages(data.pages || []);
    } catch (error) {
      console.error('Error fetching pages:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPages();
  }, []);

  const handleDelete = async (id: string) => {
    if (!confirm('Вы уверены, что хотите удалить эту страницу?')) return;

    try {
      const res = await fetch(`/api/admin/pages/${id}`, { method: 'DELETE' });
      if (res.ok) {
        fetchPages();
      } else {
        const data = await res.json();
        alert(data.error || 'Ошибка удаления');
      }
    } catch (error) {
      console.error('Error deleting page:', error);
      alert('Ошибка удаления страницы');
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Страницы</h1>
        <Link
          href="/adminka/pages/new"
          className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
        >
          + Создать страницу
        </Link>
      </div>

      {loading ? (
        <div className="text-gray-600">Загрузка...</div>
      ) : pages.length === 0 ? (
        <div className="bg-white rounded-lg shadow-md p-8 text-center">
          <p className="text-gray-600 mb-4">Страниц пока нет</p>
          <Link
            href="/adminka/pages/new"
            className="text-green-600 hover:text-green-800"
          >
            Создать первую страницу
          </Link>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          <table className="w-full">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Заголовок</th>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">URL</th>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Статус</th>
                <th className="text-left px-6 py-3 text-sm font-medium text-gray-600">Обновлено</th>
                <th className="text-right px-6 py-3 text-sm font-medium text-gray-600">Действия</th>
              </tr>
            </thead>
            <tbody>
              {pages.map((page) => (
                <tr key={page.id} className="border-b hover:bg-gray-50">
                  <td className="px-6 py-4 font-medium text-gray-800">{page.title}</td>
                  <td className="px-6 py-4 text-gray-600 text-sm">/{page.slug}</td>
                  <td className="px-6 py-4">
                    {page.isPublished ? (
                      <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                        Опубликовано
                      </span>
                    ) : (
                      <span className="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                        Черновик
                      </span>
                    )}
                  </td>
                  <td className="px-6 py-4 text-gray-600 text-sm">{formatDate(page.updatedAt)}</td>
                  <td className="px-6 py-4 text-right">
                    <Link
                      href={`/adminka/pages/${page.id}`}
                      className="text-blue-600 hover:text-blue-800 mr-4"
                    >
                      Редактировать
                    </Link>
                    <button
                      onClick={() => handleDelete(page.id)}
                      className="text-red-600 hover:text-red-800"
                    >
                      Удалить
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
