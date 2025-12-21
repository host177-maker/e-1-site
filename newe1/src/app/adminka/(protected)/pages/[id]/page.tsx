'use client';

import { useEffect, useState, use } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';

interface Page {
  id: string;
  slug: string;
  title: string;
  content: string;
  metaDescription?: string;
  metaKeywords?: string;
  isPublished: boolean;
  createdAt: string;
  updatedAt: string;
  createdBy: string;
}

export default function EditPagePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const router = useRouter();
  const [page, setPage] = useState<Page | null>(null);
  const [title, setTitle] = useState('');
  const [slug, setSlug] = useState('');
  const [content, setContent] = useState('');
  const [metaDescription, setMetaDescription] = useState('');
  const [metaKeywords, setMetaKeywords] = useState('');
  const [isPublished, setIsPublished] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const fetchPage = async () => {
      try {
        const res = await fetch(`/api/admin/pages/${id}`);
        if (!res.ok) {
          router.push('/adminka/pages');
          return;
        }
        const data = await res.json();
        setPage(data.page);
        setTitle(data.page.title);
        setSlug(data.page.slug);
        setContent(data.page.content || '');
        setMetaDescription(data.page.metaDescription || '');
        setMetaKeywords(data.page.metaKeywords || '');
        setIsPublished(data.page.isPublished);
      } catch {
        router.push('/adminka/pages');
      } finally {
        setLoading(false);
      }
    };

    fetchPage();
  }, [id, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSaving(true);

    try {
      const res = await fetch(`/api/admin/pages/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title,
          slug,
          content,
          metaDescription,
          metaKeywords,
          isPublished,
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.error || 'Ошибка сохранения');
        return;
      }

      setSuccess('Страница успешно сохранена');
      setPage(data.page);
    } catch {
      setError('Ошибка соединения с сервером');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  if (!page) {
    return <div className="text-gray-600">Страница не найдена</div>;
  }

  return (
    <div>
      <div className="mb-6">
        <Link href="/adminka/pages" className="text-blue-600 hover:text-blue-800">
          ← Назад к списку
        </Link>
      </div>

      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex justify-between items-start mb-6">
          <div>
            <h1 className="text-xl font-bold text-gray-800">Редактирование страницы</h1>
            <p className="text-gray-600 text-sm mt-1">
              Создана: {new Date(page.createdAt).toLocaleDateString('ru-RU')} •
              Автор: {page.createdBy}
            </p>
          </div>
          <a
            href={`/${slug}`}
            target="_blank"
            className="text-blue-600 hover:text-blue-800 text-sm"
          >
            Открыть страницу →
          </a>
        </div>

        <form onSubmit={handleSubmit}>
          {error && (
            <div className="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
              {error}
            </div>
          )}

          {success && (
            <div className="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
              {success}
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
                Заголовок *
              </label>
              <input
                type="text"
                id="title"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
                disabled={saving}
              />
            </div>

            <div>
              <label htmlFor="slug" className="block text-sm font-medium text-gray-700 mb-2">
                URL (slug) *
              </label>
              <div className="flex items-center">
                <span className="text-gray-500 mr-1">/</span>
                <input
                  type="text"
                  id="slug"
                  value={slug}
                  onChange={(e) => setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9-/]/g, '').replace(/\/+/g, '/'))}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  required
                  disabled={saving}
                  placeholder="service/instructions"
                />
              </div>
              <p className="text-xs text-gray-500 mt-1">Можно указывать вложенность через / (например: service/delivery)</p>
            </div>
          </div>

          <div className="mb-6">
            <label htmlFor="content" className="block text-sm font-medium text-gray-700 mb-2">
              Содержимое (HTML)
            </label>
            <textarea
              id="content"
              value={content}
              onChange={(e) => setContent(e.target.value)}
              rows={15}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 font-mono text-sm"
              disabled={saving}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label htmlFor="metaDescription" className="block text-sm font-medium text-gray-700 mb-2">
                Meta Description
              </label>
              <textarea
                id="metaDescription"
                value={metaDescription}
                onChange={(e) => setMetaDescription(e.target.value)}
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                disabled={saving}
              />
            </div>

            <div>
              <label htmlFor="metaKeywords" className="block text-sm font-medium text-gray-700 mb-2">
                Meta Keywords
              </label>
              <textarea
                id="metaKeywords"
                value={metaKeywords}
                onChange={(e) => setMetaKeywords(e.target.value)}
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                disabled={saving}
              />
            </div>
          </div>

          <div className="mb-6">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={isPublished}
                onChange={(e) => setIsPublished(e.target.checked)}
                className="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                disabled={saving}
              />
              <span className="ml-2 text-sm text-gray-700">Опубликовать страницу</span>
            </label>
          </div>

          <div className="flex gap-4">
            <button
              type="submit"
              disabled={saving}
              className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md transition-colors disabled:bg-gray-400"
            >
              {saving ? 'Сохранение...' : 'Сохранить'}
            </button>
            <Link
              href="/adminka/pages"
              className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md transition-colors"
            >
              Отмена
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
