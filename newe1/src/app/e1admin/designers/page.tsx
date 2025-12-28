'use client';

import { useState, useEffect, useCallback } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Designer {
  id: number;
  business_type: string;
  full_name: string;
  phone: string;
  email: string;
  portfolio_link: string | null;
  promo_code: string | null;
  is_active: boolean;
  step_completed: number;
  created_at: string;
  updated_at: string;
}

const businessTypeLabels: Record<string, string> = {
  individual: 'Физическое лицо',
  self_employed: 'Самозанятый',
  individual_entrepreneur: 'ИП',
  llc: 'ООО',
};

export default function DesignersPage() {
  const [designers, setDesigners] = useState<Designer[]>([]);
  const [loading, setLoading] = useState(true);
  const [counts, setCounts] = useState({ active: 0, inactive: 0, pending: 0, total: 0 });
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [modalOpen, setModalOpen] = useState(false);
  const [editingDesigner, setEditingDesigner] = useState<Designer | null>(null);
  const [formData, setFormData] = useState({
    business_type: 'individual',
    full_name: '',
    phone: '',
    email: '',
    portfolio_link: '',
    promo_code: '',
    is_active: false,
  });
  const [formError, setFormError] = useState('');
  const [saving, setSaving] = useState(false);

  const fetchDesigners = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (statusFilter !== 'all') params.append('status', statusFilter);
      if (searchQuery) params.append('search', searchQuery);

      const response = await fetch(`/api/e1admin/designers?${params}`);
      const data = await response.json();

      if (data.success) {
        setDesigners(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching designers:', error);
    } finally {
      setLoading(false);
    }
  }, [statusFilter, searchQuery]);

  useEffect(() => {
    fetchDesigners();
  }, [fetchDesigners]);

  const openModal = (designer?: Designer) => {
    if (designer) {
      setEditingDesigner(designer);
      setFormData({
        business_type: designer.business_type,
        full_name: designer.full_name,
        phone: designer.phone,
        email: designer.email,
        portfolio_link: designer.portfolio_link || '',
        promo_code: designer.promo_code || '',
        is_active: designer.is_active,
      });
    } else {
      setEditingDesigner(null);
      setFormData({
        business_type: 'individual',
        full_name: '',
        phone: '',
        email: '',
        portfolio_link: '',
        promo_code: '',
        is_active: false,
      });
    }
    setFormError('');
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setEditingDesigner(null);
    setFormError('');
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setFormError('');

    try {
      const url = editingDesigner
        ? `/api/e1admin/designers/${editingDesigner.id}`
        : '/api/e1admin/designers';
      const method = editingDesigner ? 'PATCH' : 'POST';

      const response = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.success) {
        closeModal();
        fetchDesigners();
      } else {
        setFormError(data.error);
      }
    } catch {
      setFormError('Ошибка сервера');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (designer: Designer) => {
    try {
      const response = await fetch(`/api/e1admin/designers/${designer.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !designer.is_active }),
      });

      const data = await response.json();

      if (data.success) {
        fetchDesigners();
      }
    } catch (error) {
      console.error('Error toggling active:', error);
    }
  };

  const deleteDesigner = async (designer: Designer) => {
    if (!confirm(`Удалить дизайнера "${designer.full_name}"?`)) return;

    try {
      const response = await fetch(`/api/e1admin/designers/${designer.id}`, {
        method: 'DELETE',
      });

      const data = await response.json();

      if (data.success) {
        fetchDesigners();
      }
    } catch (error) {
      console.error('Error deleting designer:', error);
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <AdminPageWrapper>
      <div className="p-6">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Дизайнеры</h1>
            <p className="text-gray-500 mt-1">Управление партнёрами-дизайнерами</p>
          </div>
          <button
            onClick={() => openModal()}
            className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Добавить дизайнера
          </button>
        </div>

        {/* Filters */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
          <div className="flex flex-col sm:flex-row gap-4">
            {/* Status Tabs */}
            <div className="flex flex-wrap gap-2">
              <button
                onClick={() => setStatusFilter('all')}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  statusFilter === 'all'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                Все ({counts.total})
              </button>
              <button
                onClick={() => setStatusFilter('active')}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  statusFilter === 'active'
                    ? 'bg-green-600 text-white'
                    : 'bg-green-100 text-green-700 hover:bg-green-200'
                }`}
              >
                Активные ({counts.active})
              </button>
              <button
                onClick={() => setStatusFilter('inactive')}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  statusFilter === 'inactive'
                    ? 'bg-gray-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                Неактивные ({counts.inactive})
              </button>
              <button
                onClick={() => setStatusFilter('pending')}
                className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                  statusFilter === 'pending'
                    ? 'bg-yellow-600 text-white'
                    : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                }`}
              >
                Ожидают ({counts.pending})
              </button>
            </div>

            {/* Search */}
            <div className="flex-1">
              <input
                type="text"
                placeholder="Поиск по имени, email, телефону, промокоду..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342]"
              />
            </div>
          </div>
        </div>

        {/* Table */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Загрузка...</div>
          ) : designers.length === 0 ? (
            <div className="p-8 text-center text-gray-500">Нет дизайнеров</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ФИО</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Контакты</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тип</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Промокод</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {designers.map((designer) => (
                    <tr key={designer.id} className="hover:bg-gray-50">
                      <td className="px-4 py-4">
                        <div className="font-medium text-gray-900">{designer.full_name}</div>
                        {designer.portfolio_link && (
                          <a
                            href={designer.portfolio_link}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-xs text-[#7cb342] hover:underline"
                          >
                            Портфолио
                          </a>
                        )}
                      </td>
                      <td className="px-4 py-4">
                        <div className="text-sm text-gray-900">{designer.email}</div>
                        <div className="text-sm text-gray-500">{designer.phone}</div>
                      </td>
                      <td className="px-4 py-4">
                        <span className="text-sm text-gray-700">
                          {businessTypeLabels[designer.business_type] || designer.business_type}
                        </span>
                      </td>
                      <td className="px-4 py-4">
                        {designer.promo_code ? (
                          <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {designer.promo_code}
                          </span>
                        ) : (
                          <span className="text-xs text-gray-400">Не указан</span>
                        )}
                      </td>
                      <td className="px-4 py-4">
                        <button
                          onClick={() => toggleActive(designer)}
                          className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-colors ${
                            designer.is_active
                              ? 'bg-green-100 text-green-800 hover:bg-green-200'
                              : designer.step_completed === 1
                              ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
                              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                          }`}
                        >
                          <span
                            className={`w-2 h-2 rounded-full ${
                              designer.is_active
                                ? 'bg-green-500'
                                : designer.step_completed === 1
                                ? 'bg-yellow-500'
                                : 'bg-gray-400'
                            }`}
                          />
                          {designer.is_active
                            ? 'Активен'
                            : designer.step_completed === 1
                            ? 'Ожидает'
                            : 'Неактивен'}
                        </button>
                      </td>
                      <td className="px-4 py-4">
                        <div className="text-sm text-gray-500">{formatDate(designer.created_at)}</div>
                      </td>
                      <td className="px-4 py-4">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => openModal(designer)}
                            className="p-2 text-gray-500 hover:text-[#7cb342] hover:bg-gray-100 rounded-lg transition-colors"
                            title="Редактировать"
                          >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                          </button>
                          <button
                            onClick={() => deleteDesigner(designer)}
                            className="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Удалить"
                          >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Modal */}
        {modalOpen && (
          <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
              <div className="p-6 border-b border-gray-200">
                <h2 className="text-xl font-bold text-gray-900">
                  {editingDesigner ? 'Редактировать дизайнера' : 'Добавить дизайнера'}
                </h2>
              </div>

              <form onSubmit={handleSubmit} className="p-6 space-y-4">
                {formError && (
                  <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {formError}
                  </div>
                )}

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Форма деятельности
                  </label>
                  <select
                    value={formData.business_type}
                    onChange={(e) => setFormData({ ...formData, business_type: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342]"
                  >
                    <option value="individual">Физическое лицо</option>
                    <option value="self_employed">Самозанятый</option>
                    <option value="individual_entrepreneur">ИП</option>
                    <option value="llc">ООО</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">ФИО *</label>
                  <input
                    type="text"
                    value={formData.full_name}
                    onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342]"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
                  <input
                    type="tel"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342]"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                  <input
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342]"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Ссылка на портфолио</label>
                  <input
                    type="url"
                    value={formData.portfolio_link}
                    onChange={(e) => setFormData({ ...formData, portfolio_link: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342]"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Промокод</label>
                  <input
                    type="text"
                    value={formData.promo_code}
                    onChange={(e) => setFormData({ ...formData, promo_code: e.target.value.toUpperCase() })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] uppercase"
                    placeholder="DESIGNER2024"
                  />
                </div>

                <div className="flex items-center gap-3">
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      checked={formData.is_active}
                      onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                      className="sr-only peer"
                    />
                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#7cb342]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#7cb342]"></div>
                  </label>
                  <span className="text-sm font-medium text-gray-700">Активен</span>
                </div>

                <div className="flex gap-3 pt-4">
                  <button
                    type="button"
                    onClick={closeModal}
                    className="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                  >
                    Отмена
                  </button>
                  <button
                    type="submit"
                    disabled={saving}
                    className="flex-1 px-4 py-2 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
                  >
                    {saving ? 'Сохранение...' : 'Сохранить'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </AdminPageWrapper>
  );
}
