'use client';

import { useState, useEffect } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface EmailSetting {
  key: string;
  email: string;
  description: string;
  updated_at: string;
}

const keyLabels: Record<string, string> = {
  director_email: 'Почта для директора',
  sales_email: 'Отдел продаж',
  designers_email: 'Дизайнерам и франшиза',
  wholesale_email: 'Оптовые продажи',
  marketplace_email: 'Маркетплейсы (ЧИМ)',
  procurement_email: 'Отдел закупок',
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
      <AdminPageWrapper>
        <div className="p-4">
          <div className="animate-pulse">
            <div className="h-6 bg-gray-200 rounded w-1/4 mb-4"></div>
            <div className="space-y-2">
              {[1, 2, 3, 4, 5, 6].map(i => (
                <div key={i} className="h-12 bg-gray-200 rounded"></div>
              ))}
            </div>
          </div>
        </div>
      </AdminPageWrapper>
    );
  }

  return (
    <AdminPageWrapper>
      <div className="p-4">
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-lg font-bold text-gray-900">Почтовые адреса</h1>
          <button
            onClick={handleSave}
            disabled={saving || !hasChanges()}
            className="px-3 py-1.5 bg-[#62bb46] text-white text-sm rounded hover:bg-[#55a83d] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {saving ? 'Сохранение...' : 'Сохранить'}
          </button>
        </div>

        {message && (
          <div
            className={`mb-4 p-2 rounded text-sm ${
              message.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            }`}
          >
            {message.text}
          </div>
        )}

        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
          <div className="divide-y divide-gray-100">
            {emails.map((item) => (
              <div key={item.key} className="p-3 hover:bg-gray-50">
                <div className="flex items-center gap-3">
                  <label className="w-40 text-sm font-medium text-gray-700 shrink-0">
                    {keyLabels[item.key] || item.key}
                  </label>
                  <input
                    type="email"
                    value={editedEmails[item.key] || ''}
                    onChange={(e) =>
                      setEditedEmails({ ...editedEmails, [item.key]: e.target.value })
                    }
                    placeholder="email@example.com"
                    className="flex-1 px-2.5 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
                  />
                </div>
              </div>
            ))}
          </div>
        </div>

        <p className="mt-3 text-xs text-gray-500">
          Эти адреса используются для получения заявок с сайта. Изменения вступают в силу сразу после сохранения.
        </p>
      </div>
    </AdminPageWrapper>
  );
}
