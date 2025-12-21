'use client';

import { useEffect, useState } from 'react';

interface ContactsConfig {
  phones: { number: string; label: string }[];
  workingHours: string;
  orderCallText: string;
  directorLinkText: string;
  directorLinkUrl: string;
}

export default function ContactsPage() {
  const [contacts, setContacts] = useState<ContactsConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    const fetchContacts = async () => {
      try {
        const res = await fetch('/api/admin/contacts');
        const data = await res.json();
        setContacts(data.contacts);
      } catch (err) {
        console.error('Error fetching contacts:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchContacts();
  }, []);

  const handleSave = async () => {
    if (!contacts) return;

    setSaving(true);
    setError('');
    setSuccess('');

    try {
      const res = await fetch('/api/admin/contacts', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(contacts),
      });

      if (res.ok) {
        setSuccess('Контакты сохранены');
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

  const addPhone = () => {
    if (!contacts) return;
    setContacts({
      ...contacts,
      phones: [...contacts.phones, { number: '', label: '' }],
    });
  };

  const updatePhone = (index: number, updates: { number?: string; label?: string }) => {
    if (!contacts) return;
    setContacts({
      ...contacts,
      phones: contacts.phones.map((phone, idx) =>
        idx === index ? { ...phone, ...updates } : phone
      ),
    });
  };

  const removePhone = (index: number) => {
    if (!contacts) return;
    setContacts({
      ...contacts,
      phones: contacts.phones.filter((_, idx) => idx !== index),
    });
  };

  if (loading) {
    return <div className="text-gray-600">Загрузка...</div>;
  }

  if (!contacts) {
    return <div className="text-gray-600">Ошибка загрузки</div>;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-800">Контактная информация</h1>
        <button
          onClick={handleSave}
          disabled={saving}
          className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md transition-colors disabled:bg-gray-400"
        >
          {saving ? 'Сохранение...' : 'Сохранить'}
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

      <div className="bg-white rounded-lg shadow-md p-6">
        {/* Phones */}
        <div className="mb-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-800">Телефоны</h2>
            <button
              onClick={addPhone}
              className="text-green-600 hover:text-green-800 text-sm"
            >
              + Добавить телефон
            </button>
          </div>
          <div className="space-y-3">
            {contacts.phones.map((phone, idx) => (
              <div key={idx} className="flex items-center gap-4">
                <input
                  type="text"
                  value={phone.number}
                  onChange={(e) => updatePhone(idx, { number: e.target.value })}
                  placeholder="8-800-100-12-11"
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                />
                <input
                  type="text"
                  value={phone.label}
                  onChange={(e) => updatePhone(idx, { label: e.target.value })}
                  placeholder="Описание (например: Бесплатный звонок)"
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-md"
                />
                <button
                  onClick={() => removePhone(idx)}
                  className="text-red-600 hover:text-red-800 px-2"
                >
                  ×
                </button>
              </div>
            ))}
          </div>
        </div>

        {/* Working hours */}
        <div className="mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Часы работы
          </label>
          <input
            type="text"
            value={contacts.workingHours}
            onChange={(e) => setContacts({ ...contacts, workingHours: e.target.value })}
            placeholder="с 07:00 до 20:00 мск"
            className="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md"
          />
        </div>

        {/* Order call */}
        <div className="mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Текст кнопки &quot;Заказать звонок&quot;
          </label>
          <input
            type="text"
            value={contacts.orderCallText}
            onChange={(e) => setContacts({ ...contacts, orderCallText: e.target.value })}
            className="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md"
          />
        </div>

        {/* Director link */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Текст ссылки на директора
            </label>
            <input
              type="text"
              value={contacts.directorLinkText}
              onChange={(e) => setContacts({ ...contacts, directorLinkText: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              URL ссылки на директора
            </label>
            <input
              type="text"
              value={contacts.directorLinkUrl}
              onChange={(e) => setContacts({ ...contacts, directorLinkUrl: e.target.value })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            />
          </div>
        </div>
      </div>
    </div>
  );
}
