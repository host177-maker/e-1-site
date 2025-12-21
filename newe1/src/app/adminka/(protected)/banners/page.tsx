'use client';

import { useEffect, useState } from 'react';

interface Banner {
  id: string;
  image: string;
  alt: string;
  link: string;
  order: number;
  isActive: boolean;
}

export default function BannersPage() {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [editBanner, setEditBanner] = useState<Banner | null>(null);
  const [showNewForm, setShowNewForm] = useState(false);
  const [newBanner, setNewBanner] = useState({ image: '', alt: '', link: '/' });

  const fetchBanners = async () => {
    try {
      const res = await fetch('/api/admin/banners');
      const data = await res.json();
      setBanners(data.banners || []);
    } catch (err) {
      console.error('Error fetching banners:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBanners();
  }, []);

  const handleAdd = async () => {
    if (!newBanner.image || !newBanner.alt) {
      setError('Заполните изображение и описание');
      return;
    }

    setSaving(true);
    setError('');

    try {
      const res = await fetch('/api/admin/banners', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newBanner),
      });

      if (res.ok) {
        setSuccess('Баннер добавлен');
        setNewBanner({ image: '', alt: '', link: '/' });
        setShowNewForm(false);
        fetchBanners();
      } else {
        const data = await res.json();
        setError(data.error || 'Ошибка добавления');
      }
    } catch {
      setError('Ошибка соединения');
    } finally {
      setSaving(false);
    }
  };

  const handleSave = async (banner: Banner) => {
    setSaving(true);
    setError('');

    try {
      const res = await fetch('/api/admin/banners', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(banner),
      });

      if (res.ok) {
        setSuccess('Сохранено');
        setEditBanner(null);
        fetchBanners();
      } else {
        const data = await res.json();
        setError(data.error || 'Ошибка сохранения');
      }
    } catch {
      setError('Ошибка соединения');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm('Удалить баннер?')) return;

    try {
      const res = await fetch('/api/admin/banners', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });

      if (res.ok) {
        fetchBanners();
      }
    } catch (err) {
      console.error('Error deleting banner:', err);
    }
  };

  const handleToggleActive = async (banner: Banner) => {
    await handleSave({ ...banner, isActive: !banner.isActive });
  };

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Баннеры главной страницы</h1>
        <button
          onClick={() => setShowNewForm(!showNewForm)}
          className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors"
        >
          + Добавить баннер
        </button>
      </div>

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

      {/* New banner form */}
      {showNewForm && (
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">Новый баннер</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <input
              type="text"
              placeholder="URL изображения (/images/banner.jpg)"
              value={newBanner.image}
              onChange={(e) => setNewBanner({ ...newBanner, image: e.target.value })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
            <input
              type="text"
              placeholder="Описание (alt)"
              value={newBanner.alt}
              onChange={(e) => setNewBanner({ ...newBanner, alt: e.target.value })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
            <input
              type="text"
              placeholder="Ссылка (/sales)"
              value={newBanner.link}
              onChange={(e) => setNewBanner({ ...newBanner, link: e.target.value })}
              className="px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <div className="flex gap-2">
            <button
              onClick={handleAdd}
              disabled={saving}
              className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md disabled:bg-gray-400"
            >
              Добавить
            </button>
            <button
              onClick={() => setShowNewForm(false)}
              className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md"
            >
              Отмена
            </button>
          </div>
        </div>
      )}

      {/* Banners list */}
      <div className="space-y-4">
        {banners.sort((a, b) => a.order - b.order).map((banner) => (
          <div
            key={banner.id}
            className={`bg-white rounded-lg shadow-md p-4 ${!banner.isActive ? 'opacity-50' : ''}`}
          >
            {editBanner?.id === banner.id ? (
              <div className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <input
                    type="text"
                    value={editBanner.image}
                    onChange={(e) => setEditBanner({ ...editBanner, image: e.target.value })}
                    className="px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="URL изображения"
                  />
                  <input
                    type="text"
                    value={editBanner.alt}
                    onChange={(e) => setEditBanner({ ...editBanner, alt: e.target.value })}
                    className="px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="Описание"
                  />
                  <input
                    type="text"
                    value={editBanner.link}
                    onChange={(e) => setEditBanner({ ...editBanner, link: e.target.value })}
                    className="px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="Ссылка"
                  />
                  <input
                    type="number"
                    value={editBanner.order}
                    onChange={(e) => setEditBanner({ ...editBanner, order: parseInt(e.target.value) })}
                    className="px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="Порядок"
                  />
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={() => handleSave(editBanner)}
                    disabled={saving}
                    className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
                  >
                    Сохранить
                  </button>
                  <button
                    onClick={() => setEditBanner(null)}
                    className="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm"
                  >
                    Отмена
                  </button>
                </div>
              </div>
            ) : (
              <div className="flex items-center gap-4">
                <span className="text-gray-400 text-sm w-8">{banner.order}</span>
                <div className="w-24 h-16 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={banner.image}
                    alt={banner.alt}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                      (e.target as HTMLImageElement).src = '/images/placeholder.jpg';
                    }}
                  />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="font-medium text-gray-800 truncate">{banner.alt}</div>
                  <div className="text-sm text-gray-500">{banner.link}</div>
                </div>
                {!banner.isActive && (
                  <span className="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                    Скрыт
                  </span>
                )}
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => handleToggleActive(banner)}
                    className="text-gray-500 hover:text-gray-700"
                    title={banner.isActive ? 'Скрыть' : 'Показать'}
                  >
                    {banner.isActive ? '👁️' : '👁️‍🗨️'}
                  </button>
                  <button
                    onClick={() => setEditBanner(banner)}
                    className="text-blue-600 hover:text-blue-800 text-sm"
                  >
                    Редактировать
                  </button>
                  <button
                    onClick={() => handleDelete(banner.id)}
                    className="text-red-600 hover:text-red-800 text-sm"
                  >
                    Удалить
                  </button>
                </div>
              </div>
            )}
          </div>
        ))}

        {banners.length === 0 && (
          <div className="text-center text-gray-600 py-8">
            Баннеров пока нет
          </div>
        )}
      </div>
    </div>
  );
}
