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
      <div className="p-4">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
          <div>
            <h1 className="text-lg font-bold text-gray-900">Дизайнеры</h1>
            <p className="text-sm text-gray-500">Всего: {counts.total}</p>
          </div>
          <button
            onClick={() => openModal()}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#7cb342] text-white text-sm rounded-lg hover:bg-[#689f38] transition-colors"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Добавить
          </button>
        </div>

        {/* Filters */}
        <div className="flex flex-col md:flex-row gap-3 mb-4">
          <div className="flex flex-wrap gap-1.5">
            <button
              onClick={() => setStatusFilter('all')}
              className={`px-2.5 py-1 rounded text-sm font-medium transition-colors ${
                statusFilter === 'all'
                  ? 'bg-gray-900 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              Все ({counts.total})
            </button>
            <button
              onClick={() => setStatusFilter('active')}
              className={`px-2.5 py-1 rounded text-sm font-medium transition-colors ${
                statusFilter === 'active'
                  ? 'bg-green-600 text-white'
                  : 'bg-green-100 text-green-700 hover:bg-green-200'
              }`}
            >
              Активные ({counts.active})
            </button>
            <button
              onClick={() => setStatusFilter('inactive')}
              className={`px-2.5 py-1 rounded text-sm font-medium transition-colors ${
                statusFilter === 'inactive'
                  ? 'bg-gray-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              Неактивные ({counts.inactive})
            </button>
            <button
              onClick={() => setStatusFilter('pending')}
              className={`px-2.5 py-1 rounded text-sm font-medium transition-colors ${
                statusFilter === 'pending'
                  ? 'bg-yellow-600 text-white'
                  : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
              }`}
            >
              Ожидают ({counts.pending})
            </button>
          </div>
          <div className="flex-1">
            <input
              type="text"
              placeholder="Поиск по имени, email, телефону, промокоду..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm"
            />
          </div>
        </div>

        {/* Table */}
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
          {loading ? (
            <div className="p-8 text-center">
              <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin mx-auto"></div>
            </div>
          ) : designers.length === 0 ? (
            <div className="p-8 text-center text-gray-500">Нет дизайнеров</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">ФИО</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Контакты</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Тип</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Промокод</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Статус</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Дата</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {designers.map((designer) => (
                    <tr key={designer.id} className="hover:bg-gray-50">
                      <td className="px-3 py-2">
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
                      <td className="px-3 py-2">
                        <div className="text-gray-900">{designer.email}</div>
                        <div className="text-gray-500 text-xs">{designer.phone}</div>
                      </td>
                      <td className="px-3 py-2 text-gray-600">
                        {businessTypeLabels[designer.business_type] || designer.business_type}
                      </td>
                      <td className="px-3 py-2">
                        {designer.promo_code ? (
                          <span className="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {designer.promo_code}
                          </span>
                        ) : (
                          <span className="text-xs text-gray-400">—</span>
                        )}
                      </td>
                      <td className="px-3 py-2">
                        <button
                          onClick={() => toggleActive(designer)}
                          className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition-colors ${
                            designer.is_active
                              ? 'bg-green-100 text-green-800 hover:bg-green-200'
                              : designer.step_completed === 1
                              ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
                              : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
                          }`}
                        >
                          <span
                            className={`w-1.5 h-1.5 rounded-full ${
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
                      <td className="px-3 py-2 text-gray-500 text-xs">{formatDate(designer.created_at)}</td>
                      <td className="px-3 py-2">
                        <div className="flex gap-1">
                          <button
                            onClick={() => openModal(designer)}
                            className="p-1 text-gray-400 hover:text-[#7cb342]"
                            title="Редактировать"
                          >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                          </button>
                          <button
                            onClick={() => deleteDesigner(designer)}
                            className="p-1 text-gray-400 hover:text-red-500"
                            title="Удалить"
                          >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <>
            <div
              className="fixed inset-0 bg-black/50 z-40"
              onClick={closeModal}
            />
            <div className="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:max-w-md md:w-full bg-white rounded-xl shadow-xl z-50 overflow-hidden">
              <div className="flex items-center justify-between p-4 border-b">
                <h3 className="font-bold text-gray-900">
                  {editingDesigner ? 'Редактировать' : 'Добавить дизайнера'}
                </h3>
                <button
                  onClick={closeModal}
                  className="p-1 hover:bg-gray-100 rounded"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <form onSubmit={handleSubmit} className="p-4 max-h-[70vh] overflow-y-auto space-y-3">
                {formError && (
                  <div className="p-2 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                    {formError}
                  </div>
                )}

                <div className="grid grid-cols-2 gap-3">
                  <div className="col-span-2">
                    <label className="block text-xs font-medium text-gray-700 mb-1">ФИО *</label>
                    <input
                      type="text"
                      value={formData.full_name}
                      onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm"
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Телефон *</label>
                    <input
                      type="tel"
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm"
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Email *</label>
                    <input
                      type="email"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm"
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Форма деятельности</label>
                    <select
                      value={formData.business_type}
                      onChange={(e) => setFormData({ ...formData, business_type: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm"
                    >
                      <option value="individual">Физ. лицо</option>
                      <option value="self_employed">Самозанятый</option>
                      <option value="individual_entrepreneur">ИП</option>
                      <option value="llc">ООО</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">Промокод</label>
                    <input
                      type="text"
                      value={formData.promo_code}
                      onChange={(e) => setFormData({ ...formData, promo_code: e.target.value.toUpperCase() })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm uppercase"
                      placeholder="DESIGNER2024"
                    />
                  </div>

                  <div className="col-span-2">
                    <label className="block text-xs font-medium text-gray-700 mb-1">Ссылка на портфолио</label>
                    <input
                      type="url"
                      value={formData.portfolio_link}
                      onChange={(e) => setFormData({ ...formData, portfolio_link: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#7cb342] text-sm"
                    />
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  <label className="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      checked={formData.is_active}
                      onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                      className="sr-only peer"
                    />
                    <div className="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#7cb342]"></div>
                  </label>
                  <span className="text-sm text-gray-700">Активен</span>
                </div>
              </form>

              <div className="p-4 border-t flex justify-end gap-2">
                <button
                  type="button"
                  onClick={closeModal}
                  className="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors text-sm"
                >
                  Отмена
                </button>
                <button
                  onClick={handleSubmit}
                  disabled={saving}
                  className="px-4 py-2 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50 text-sm"
                >
                  {saving ? 'Сохранение...' : 'Сохранить'}
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </AdminPageWrapper>
  );
}
