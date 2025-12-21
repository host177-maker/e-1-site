'use client';

import { useEffect, useState } from 'react';

interface Messenger {
  id: string;
  name: string;
  url: string;
  icon: string;
  order: number;
  isActive: boolean;
  showInHeader: boolean;
  showInFooter: boolean;
}

interface SocialNetwork {
  id: string;
  name: string;
  url: string;
  icon: string;
  order: number;
  isActive: boolean;
}

const iconOptions = [
  { value: 'whatsapp', label: 'WhatsApp' },
  { value: 'telegram', label: 'Telegram' },
  { value: 'max', label: 'Max' },
  { value: 'viber', label: 'Viber' },
  { value: 'vk', label: 'ВКонтакте' },
  { value: 'ok', label: 'Одноклассники' },
  { value: 'rutube', label: 'Rutube' },
  { value: 'youtube', label: 'YouTube' },
  { value: 'default', label: 'Другое' },
];

export default function MessengersPage() {
  const [messengers, setMessengers] = useState<Messenger[]>([]);
  const [socials, setSocials] = useState<SocialNetwork[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [activeTab, setActiveTab] = useState<'messengers' | 'socials'>('messengers');
  const [showNewForm, setShowNewForm] = useState(false);
  const [newItem, setNewItem] = useState({ name: '', url: '', icon: 'default' });

  const fetchData = async () => {
    try {
      const [messRes, socRes] = await Promise.all([
        fetch('/api/admin/messengers'),
        fetch('/api/admin/socials'),
      ]);
      const messData = await messRes.json();
      const socData = await socRes.json();
      setMessengers(messData.messengers || []);
      setSocials(socData.socials || []);
    } catch (err) {
      console.error('Error fetching data:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleAddMessenger = async () => {
    if (!newItem.name || !newItem.url) {
      setError('Заполните название и URL');
      return;
    }

    setSaving(true);
    setError('');

    try {
      const res = await fetch('/api/admin/messengers', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newItem),
      });

      if (res.ok) {
        setSuccess('Мессенджер добавлен');
        setNewItem({ name: '', url: '', icon: 'default' });
        setShowNewForm(false);
        fetchData();
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

  const handleSaveMessengers = async () => {
    setSaving(true);
    setError('');

    try {
      const res = await fetch('/api/admin/messengers', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messengers }),
      });

      if (res.ok) {
        setSuccess('Мессенджеры сохранены');
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

  const handleSaveSocials = async () => {
    setSaving(true);
    setError('');

    try {
      const res = await fetch('/api/admin/socials', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ socials }),
      });

      if (res.ok) {
        setSuccess('Социальные сети сохранены');
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

  const handleDeleteMessenger = async (id: string) => {
    if (!confirm('Удалить мессенджер?')) return;

    try {
      const res = await fetch('/api/admin/messengers', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });

      if (res.ok) {
        fetchData();
      }
    } catch (err) {
      console.error('Error deleting:', err);
    }
  };

  const updateMessenger = (id: string, updates: Partial<Messenger>) => {
    setMessengers(messengers.map((m) => (m.id === id ? { ...m, ...updates } : m)));
  };

  const updateSocial = (id: string, updates: Partial<SocialNetwork>) => {
    setSocials(socials.map((s) => (s.id === id ? { ...s, ...updates } : s)));
  };

  const addSocial = () => {
    const newSocial: SocialNetwork = {
      id: crypto.randomUUID(),
      name: 'Новая соцсеть',
      url: 'https://',
      icon: 'default',
      order: socials.length + 1,
      isActive: true,
    };
    setSocials([...socials, newSocial]);
  };

  const removeSocial = (id: string) => {
    setSocials(socials.filter((s) => s.id !== id));
  };

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-800 mb-6">Мессенджеры и соцсети</h1>

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

      {/* Tabs */}
      <div className="flex space-x-4 mb-6">
        <button
          onClick={() => setActiveTab('messengers')}
          className={`px-4 py-2 rounded-md transition-colors ${
            activeTab === 'messengers'
              ? 'bg-green-600 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Мессенджеры
        </button>
        <button
          onClick={() => setActiveTab('socials')}
          className={`px-4 py-2 rounded-md transition-colors ${
            activeTab === 'socials'
              ? 'bg-green-600 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          }`}
        >
          Социальные сети
        </button>
      </div>

      {activeTab === 'messengers' && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-800">Мессенджеры</h2>
            <div className="flex gap-2">
              <button
                onClick={() => setShowNewForm(!showNewForm)}
                className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
              >
                + Добавить
              </button>
              <button
                onClick={handleSaveMessengers}
                disabled={saving}
                className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm disabled:bg-gray-400"
              >
                Сохранить
              </button>
            </div>
          </div>

          {showNewForm && (
            <div className="mb-4 p-4 bg-gray-50 rounded-lg">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <input
                  type="text"
                  placeholder="Название"
                  value={newItem.name}
                  onChange={(e) => setNewItem({ ...newItem, name: e.target.value })}
                  className="px-3 py-2 border border-gray-300 rounded-md"
                />
                <input
                  type="text"
                  placeholder="URL (https://...)"
                  value={newItem.url}
                  onChange={(e) => setNewItem({ ...newItem, url: e.target.value })}
                  className="px-3 py-2 border border-gray-300 rounded-md"
                />
                <select
                  value={newItem.icon}
                  onChange={(e) => setNewItem({ ...newItem, icon: e.target.value })}
                  className="px-3 py-2 border border-gray-300 rounded-md"
                >
                  {iconOptions.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
              </div>
              <button
                onClick={handleAddMessenger}
                disabled={saving}
                className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm"
              >
                Добавить
              </button>
            </div>
          )}

          <div className="space-y-3">
            {messengers.sort((a, b) => a.order - b.order).map((messenger) => (
              <div
                key={messenger.id}
                className={`flex items-center gap-4 p-3 border rounded-lg ${
                  !messenger.isActive ? 'bg-gray-100 opacity-60' : 'bg-white'
                }`}
              >
                <input
                  type="number"
                  value={messenger.order}
                  onChange={(e) => updateMessenger(messenger.id, { order: parseInt(e.target.value) })}
                  className="w-16 px-2 py-1 border border-gray-300 rounded text-sm"
                />
                <select
                  value={messenger.icon}
                  onChange={(e) => updateMessenger(messenger.id, { icon: e.target.value })}
                  className="w-32 px-2 py-1 border border-gray-300 rounded text-sm"
                >
                  {iconOptions.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
                <input
                  type="text"
                  value={messenger.name}
                  onChange={(e) => updateMessenger(messenger.id, { name: e.target.value })}
                  className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                />
                <input
                  type="text"
                  value={messenger.url}
                  onChange={(e) => updateMessenger(messenger.id, { url: e.target.value })}
                  className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                />
                <label className="flex items-center text-xs">
                  <input
                    type="checkbox"
                    checked={messenger.showInHeader}
                    onChange={(e) => updateMessenger(messenger.id, { showInHeader: e.target.checked })}
                    className="mr-1"
                  />
                  Шапка
                </label>
                <label className="flex items-center text-xs">
                  <input
                    type="checkbox"
                    checked={messenger.showInFooter}
                    onChange={(e) => updateMessenger(messenger.id, { showInFooter: e.target.checked })}
                    className="mr-1"
                  />
                  Подвал
                </label>
                <label className="flex items-center text-xs">
                  <input
                    type="checkbox"
                    checked={messenger.isActive}
                    onChange={(e) => updateMessenger(messenger.id, { isActive: e.target.checked })}
                    className="mr-1"
                  />
                  Активен
                </label>
                <button
                  onClick={() => handleDeleteMessenger(messenger.id)}
                  className="text-red-600 hover:text-red-800"
                >
                  ×
                </button>
              </div>
            ))}
          </div>
        </div>
      )}

      {activeTab === 'socials' && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-800">Социальные сети</h2>
            <div className="flex gap-2">
              <button
                onClick={addSocial}
                className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
              >
                + Добавить
              </button>
              <button
                onClick={handleSaveSocials}
                disabled={saving}
                className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm disabled:bg-gray-400"
              >
                Сохранить
              </button>
            </div>
          </div>

          <div className="space-y-3">
            {socials.sort((a, b) => a.order - b.order).map((social) => (
              <div
                key={social.id}
                className={`flex items-center gap-4 p-3 border rounded-lg ${
                  !social.isActive ? 'bg-gray-100 opacity-60' : 'bg-white'
                }`}
              >
                <input
                  type="number"
                  value={social.order}
                  onChange={(e) => updateSocial(social.id, { order: parseInt(e.target.value) })}
                  className="w-16 px-2 py-1 border border-gray-300 rounded text-sm"
                />
                <select
                  value={social.icon}
                  onChange={(e) => updateSocial(social.id, { icon: e.target.value })}
                  className="w-32 px-2 py-1 border border-gray-300 rounded text-sm"
                >
                  {iconOptions.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
                <input
                  type="text"
                  value={social.name}
                  onChange={(e) => updateSocial(social.id, { name: e.target.value })}
                  className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                />
                <input
                  type="text"
                  value={social.url}
                  onChange={(e) => updateSocial(social.id, { url: e.target.value })}
                  className="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                />
                <label className="flex items-center text-xs">
                  <input
                    type="checkbox"
                    checked={social.isActive}
                    onChange={(e) => updateSocial(social.id, { isActive: e.target.checked })}
                    className="mr-1"
                  />
                  Активен
                </label>
                <button
                  onClick={() => removeSocial(social.id)}
                  className="text-red-600 hover:text-red-800"
                >
                  ×
                </button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
