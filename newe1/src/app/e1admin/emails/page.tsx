'use client';

import { useState, useEffect } from 'react';

interface EmailSetting {
  key: string;
  email: string;
  description: string;
  updated_at: string;
}

const keyLabels: Record<string, string> = {
  director_email: 'Почта сообщений для директора',
  sales_email: 'Отдел продаж интернет-магазина',
  designers_email: 'Отдел работы с дизайнерами',
  wholesale_email: 'Отдел оптовых продаж',
  marketplace_email: 'Отдел по ЧИМ',
};

export default function EmailsPage() {
  const [emails, setEmails] = useState<EmailSetting[]>([]);
  const [editedEmails, setEditedEmails] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  useEffect(() => {
    fetchEmails();
  }, []);

  const fetchEmails = async () => {
    try {
      const response = await fetch('/api/e1admin/emails');
      const data = await response.json();

      if (data.success) {
        setEmails(data.data);
        const edited: Record<string, string> = {};
        data.data.forEach((item: EmailSetting) => {
          edited[item.key] = item.email;
        });
        setEditedEmails(edited);
      }
    } catch (error) {
      console.error('Error fetching emails:', error);
      setMessage({ type: 'error', text: 'Ошибка загрузки настроек' });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    setSaving(true);
    setMessage(null);

    try {
      const emailsToSave = Object.entries(editedEmails).map(([key, email]) => ({
        key,
        email,
      }));

      const response = await fetch('/api/e1admin/emails', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ emails: emailsToSave }),
      });

      const data = await response.json();

      if (data.success) {
        setMessage({ type: 'success', text: 'Настройки сохранены' });
        fetchEmails();
      } else {
        setMessage({ type: 'error', text: data.error || 'Ошибка сохранения' });
      }
    } catch (error) {
      console.error('Error saving emails:', error);
      setMessage({ type: 'error', text: 'Ошибка сохранения настроек' });
    } finally {
      setSaving(false);
    }
  };

  const hasChanges = () => {
    return emails.some(item => item.email !== editedEmails[item.key]);
  };

  if (loading) {
    return (
      <div className="p-6">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-1/4 mb-6"></div>
          <div className="space-y-4">
            {[1, 2, 3, 4, 5].map(i => (
              <div key={i} className="h-20 bg-gray-200 rounded"></div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Почтовые адреса</h1>
          <p className="text-gray-600 mt-1">Настройка email адресов для сайта и сервисов</p>
        </div>
        <button
          onClick={handleSave}
          disabled={saving || !hasChanges()}
          className="px-4 py-2 bg-[#62bb46] text-white rounded-lg hover:bg-[#55a83d] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {saving ? 'Сохранение...' : 'Сохранить'}
        </button>
      </div>

      {message && (
        <div
          className={`mb-6 p-4 rounded-lg ${
            message.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
          }`}
        >
          {message.text}
        </div>
      )}

      <div className="bg-white rounded-xl shadow-sm overflow-hidden">
        <div className="divide-y divide-gray-200">
          {emails.map((item) => (
            <div key={item.key} className="p-4 hover:bg-gray-50">
              <div className="flex flex-col md:flex-row md:items-center gap-4">
                <div className="flex-1">
                  <label className="block text-sm font-medium text-gray-900 mb-1">
                    {keyLabels[item.key] || item.key}
                  </label>
                  <p className="text-xs text-gray-500 mb-2">{item.description}</p>
                  <input
                    type="email"
                    value={editedEmails[item.key] || ''}
                    onChange={(e) =>
                      setEditedEmails({ ...editedEmails, [item.key]: e.target.value })
                    }
                    placeholder="email@example.com"
                    className="w-full md:w-96 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm"
                  />
                </div>
                <div className="text-xs text-gray-400">
                  {item.updated_at && (
                    <>
                      Обновлено:{' '}
                      {new Date(item.updated_at).toLocaleString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                      })}
                    </>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="mt-6 p-4 bg-blue-50 rounded-lg">
        <h3 className="font-medium text-blue-900 mb-2">Как это работает</h3>
        <ul className="text-sm text-blue-800 space-y-1">
          <li>• <strong>Почта для директора</strong> — получает сообщения из формы обратной связи в футере сайта</li>
          <li>• <strong>Отдел продаж</strong> — получает лиды с сайта (замеры, рассрочка) при сбое CRM</li>
          <li>• <strong>Отдел дизайнеров</strong> — получает заявки от дизайнеров и на франшизу</li>
          <li>• <strong>Отдел оптовых продаж</strong> — получает заявки на оптовое сотрудничество</li>
          <li>• <strong>Отдел по ЧИМ</strong> — получает заявки от селлеров маркетплейсов</li>
        </ul>
      </div>
    </div>
  );
}
