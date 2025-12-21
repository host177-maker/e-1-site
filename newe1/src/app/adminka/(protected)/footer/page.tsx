'use client';

import { useEffect, useState } from 'react';

interface FooterColumn {
  id: string;
  title: string;
  links: { label: string; url: string }[];
}

interface FooterConfig {
  columns: FooterColumn[];
  copyright: string;
  companyDescription: string;
  legalText: string;
}

export default function FooterPage() {
  const [footer, setFooter] = useState<FooterConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [editColumn, setEditColumn] = useState<string | null>(null);

  const fetchFooter = async () => {
    try {
      const res = await fetch('/api/admin/footer');
      const data = await res.json();
      setFooter(data.footer);
    } catch (err) {
      console.error('Error fetching footer:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFooter();
  }, []);

  const handleSave = async () => {
    if (!footer) return;

    setSaving(true);
    setError('');
    setSuccess('');

    try {
      const res = await fetch('/api/admin/footer', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(footer),
      });

      if (res.ok) {
        setSuccess('Настройки подвала сохранены');
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

  const updateColumn = (columnId: string, updates: Partial<FooterColumn>) => {
    if (!footer) return;
    setFooter({
      ...footer,
      columns: footer.columns.map((col) =>
        col.id === columnId ? { ...col, ...updates } : col
      ),
    });
  };

  const addLink = (columnId: string) => {
    if (!footer) return;
    setFooter({
      ...footer,
      columns: footer.columns.map((col) =>
        col.id === columnId
          ? { ...col, links: [...col.links, { label: 'Новая ссылка', url: '/' }] }
          : col
      ),
    });
  };

  const updateLink = (columnId: string, linkIndex: number, updates: { label?: string; url?: string }) => {
    if (!footer) return;
    setFooter({
      ...footer,
      columns: footer.columns.map((col) =>
        col.id === columnId
          ? {
              ...col,
              links: col.links.map((link, idx) =>
                idx === linkIndex ? { ...link, ...updates } : link
              ),
            }
          : col
      ),
    });
  };

  const removeLink = (columnId: string, linkIndex: number) => {
    if (!footer) return;
    setFooter({
      ...footer,
      columns: footer.columns.map((col) =>
        col.id === columnId
          ? { ...col, links: col.links.filter((_, idx) => idx !== linkIndex) }
          : col
      ),
    });
  };

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  if (!footer) {
    return <div className="text-gray-600">Ошибка загрузки</div>;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Настройки подвала</h1>
        <button
          onClick={handleSave}
          disabled={saving}
          className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md transition-colors disabled:bg-gray-400"
        >
          {saving ? 'Сохранение...' : 'Сохранить всё'}
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

      {/* General settings */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Общие настройки</h2>
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Копирайт
            </label>
            <input
              type="text"
              value={footer.copyright}
              onChange={(e) => setFooter({ ...footer, copyright: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Описание компании
            </label>
            <input
              type="text"
              value={footer.companyDescription}
              onChange={(e) => setFooter({ ...footer, companyDescription: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Юридический текст
            </label>
            <textarea
              value={footer.legalText}
              onChange={(e) => setFooter({ ...footer, legalText: e.target.value })}
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
        </div>
      </div>

      {/* Columns */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {footer.columns.map((column) => (
          <div key={column.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex justify-between items-center mb-4">
              {editColumn === column.id ? (
                <input
                  type="text"
                  value={column.title}
                  onChange={(e) => updateColumn(column.id, { title: e.target.value })}
                  className="px-2 py-1 border border-gray-300 rounded-md font-semibold"
                  autoFocus
                  onBlur={() => setEditColumn(null)}
                  onKeyDown={(e) => e.key === 'Enter' && setEditColumn(null)}
                />
              ) : (
                <h3
                  className="text-lg font-semibold text-gray-800 cursor-pointer hover:text-green-600"
                  onClick={() => setEditColumn(column.id)}
                  title="Нажмите для редактирования"
                >
                  {column.title}
                </h3>
              )}
              <button
                onClick={() => addLink(column.id)}
                className="text-green-600 hover:text-green-800 text-sm"
              >
                + Добавить ссылку
              </button>
            </div>

            <div className="space-y-2">
              {column.links.map((link, idx) => (
                <div key={idx} className="flex items-center gap-2">
                  <input
                    type="text"
                    value={link.label}
                    onChange={(e) => updateLink(column.id, idx, { label: e.target.value })}
                    className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                    placeholder="Название"
                  />
                  <input
                    type="text"
                    value={link.url}
                    onChange={(e) => updateLink(column.id, idx, { url: e.target.value })}
                    className="w-32 px-2 py-1 border border-gray-300 rounded text-sm"
                    placeholder="URL"
                  />
                  <button
                    onClick={() => removeLink(column.id, idx)}
                    className="text-red-600 hover:text-red-800"
                    title="Удалить"
                  >
                    ×
                  </button>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
