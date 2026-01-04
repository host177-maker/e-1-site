'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Warehouse {
  id: number;
  external_id: string | null;
  sort_order: number;
  name: string;
  display_name: string | null;
  is_active: boolean;
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  phone: string | null;
  working_hours: string | null;
  created_at: string;
  city_count: number;
}

interface Counts {
  active: number;
  inactive: number;
  total: number;
}

export default function WarehousesPage() {
  const [warehouses, setWarehouses] = useState<Warehouse[]>([]);
  const [counts, setCounts] = useState<Counts>({ active: 0, inactive: 0, total: 0 });
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);
  const [searchQuery, setSearchQuery] = useState('');

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingWarehouse, setEditingWarehouse] = useState<Warehouse | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formName, setFormName] = useState('');
  const [formDisplayName, setFormDisplayName] = useState('');
  const [formAddress, setFormAddress] = useState('');
  const [formLatitude, setFormLatitude] = useState('');
  const [formLongitude, setFormLongitude] = useState('');
  const [formPhone, setFormPhone] = useState('');
  const [formWorkingHours, setFormWorkingHours] = useState('');
  const [formSortOrder, setFormSortOrder] = useState(500);
  const [formIsActive, setFormIsActive] = useState(true);

  // Import
  const [importing, setImporting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const fetchWarehouses = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/warehouses');
      const data = await response.json();
      if (data.success) {
        setWarehouses(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching warehouses:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchWarehouses();
  }, [fetchWarehouses]);

  const openCreateModal = () => {
    setEditingWarehouse(null);
    setFormName('');
    setFormDisplayName('');
    setFormAddress('');
    setFormLatitude('');
    setFormLongitude('');
    setFormPhone('');
    setFormWorkingHours('');
    setFormSortOrder(500);
    setFormIsActive(true);
    setModalOpen(true);
  };

  const openEditModal = (warehouse: Warehouse) => {
    setEditingWarehouse(warehouse);
    setFormName(warehouse.name);
    setFormDisplayName(warehouse.display_name || '');
    setFormAddress(warehouse.address || '');
    setFormLatitude(warehouse.latitude?.toString() || '');
    setFormLongitude(warehouse.longitude?.toString() || '');
    setFormPhone(warehouse.phone || '');
    setFormWorkingHours(warehouse.working_hours || '');
    setFormSortOrder(warehouse.sort_order);
    setFormIsActive(warehouse.is_active);
    setModalOpen(true);
  };

  const saveWarehouse = async () => {
    if (!formName.trim()) {
      alert('Введите название склада');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        name: formName,
        display_name: formDisplayName || null,
        address: formAddress || null,
        latitude: formLatitude ? parseFloat(formLatitude) : null,
        longitude: formLongitude ? parseFloat(formLongitude) : null,
        phone: formPhone || null,
        working_hours: formWorkingHours || null,
        sort_order: formSortOrder,
        is_active: formIsActive,
      };

      let response;
      if (editingWarehouse) {
        response = await fetch(`/api/e1admin/warehouses/${editingWarehouse.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/warehouses', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchWarehouses();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (warehouse: Warehouse) => {
    setActionLoading(warehouse.id);
    try {
      const response = await fetch(`/api/e1admin/warehouses/${warehouse.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !warehouse.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchWarehouses();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteWarehouse = async (warehouse: Warehouse) => {
    if (!confirm(`Удалить склад "${warehouse.name}"? Это действие нельзя отменить.`)) return;

    setActionLoading(warehouse.id);
    try {
      const response = await fetch(`/api/e1admin/warehouses/${warehouse.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchWarehouses();
      } else {
        alert(data.error || 'Ошибка удаления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const handleImport = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setImporting(true);
    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await fetch('/api/e1admin/warehouses', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        alert(data.message || `Импортировано ${data.imported} складов`);
        fetchWarehouses();
      } else {
        alert(data.error || 'Ошибка импорта');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setImporting(false);
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    }
  };

  const filteredWarehouses = warehouses.filter(w => {
    if (!searchQuery) return true;
    const query = searchQuery.toLowerCase();
    return (
      w.name.toLowerCase().includes(query) ||
      w.display_name?.toLowerCase().includes(query) ||
      w.address?.toLowerCase().includes(query)
    );
  });

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Склады</h1>
            <p className="text-gray-600 mt-1">Управление региональными складами для самовывоза</p>
          </div>
          <div className="flex gap-2">
            <input
              type="file"
              ref={fileInputRef}
              onChange={handleImport}
              accept=".xlsx,.xls"
              className="hidden"
            />
            <button
              onClick={() => fileInputRef.current?.click()}
              disabled={importing}
              className="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors disabled:opacity-50"
            >
              {importing ? (
                <div className="w-5 h-5 border-2 border-gray-400 border-t-transparent rounded-full animate-spin" />
              ) : (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
              )}
              Импорт из Excel
            </button>
            <button
              onClick={openCreateModal}
              className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Добавить склад
            </button>
          </div>
        </div>

        {/* Stats */}
        <div className="flex gap-4 mb-6 text-sm">
          <span className="text-gray-600">
            Всего: <strong>{counts.total}</strong>
          </span>
          <span className="text-green-600">
            Активных: <strong>{counts.active}</strong>
          </span>
          <span className="text-gray-500">
            Скрытых: <strong>{counts.inactive}</strong>
          </span>
        </div>

        {/* Search */}
        <div className="mb-6">
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Поиск по названию или адресу..."
            className="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
          />
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : filteredWarehouses.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              {searchQuery ? 'Склады не найдены' : 'Нет складов'}
            </h3>
            <p className="text-gray-500 mb-4">
              {searchQuery ? 'Попробуйте изменить параметры поиска' : 'Добавьте первый склад или импортируйте из Excel'}
            </p>
          </div>
        ) : (
          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Адрес</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Городов</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                  <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredWarehouses.map((warehouse) => (
                  <tr
                    key={warehouse.id}
                    className={`hover:bg-gray-50 ${!warehouse.is_active ? 'bg-gray-50/50' : ''}`}
                  >
                    <td className="px-4 py-4">
                      <div className="flex items-center gap-2">
                        <span className="font-medium text-gray-900">{warehouse.display_name || warehouse.name}</span>
                        {!warehouse.is_active && (
                          <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                            Скрыт
                          </span>
                        )}
                      </div>
                      {warehouse.working_hours && (
                        <p className="text-xs text-gray-500 mt-1">{warehouse.working_hours}</p>
                      )}
                    </td>
                    <td className="px-4 py-4 text-sm text-gray-600 max-w-xs truncate">
                      {warehouse.address || '-'}
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                      {warehouse.phone || '-'}
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                      {warehouse.city_count}
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center">
                      <button
                        onClick={() => toggleActive(warehouse)}
                        disabled={actionLoading === warehouse.id}
                        className={`p-1.5 rounded-lg transition-colors disabled:opacity-50 ${
                          warehouse.is_active
                            ? 'text-green-600 hover:bg-green-50'
                            : 'text-gray-400 hover:bg-gray-100'
                        }`}
                        title={warehouse.is_active ? 'Скрыть' : 'Показать'}
                      >
                        {warehouse.is_active ? (
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        ) : (
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                          </svg>
                        )}
                      </button>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button
                          onClick={() => openEditModal(warehouse)}
                          className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                          title="Редактировать"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button
                          onClick={() => deleteWarehouse(warehouse)}
                          disabled={actionLoading === warehouse.id || warehouse.city_count > 0}
                          className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
                          title={warehouse.city_count > 0 ? 'Нельзя удалить - есть привязанные города' : 'Удалить'}
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

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingWarehouse ? 'Редактирование склада' : 'Новый склад'}
              </h2>
            </div>

            <div className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Название <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="Склад региональный Краснодар"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Отображаемое название
                </label>
                <input
                  type="text"
                  value={formDisplayName}
                  onChange={(e) => setFormDisplayName(e.target.value)}
                  placeholder="Краснодар"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Адрес
                </label>
                <textarea
                  value={formAddress}
                  onChange={(e) => setFormAddress(e.target.value)}
                  rows={2}
                  placeholder="г. Краснодар, ул. Примерная, 123"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none resize-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Широта (GPS)
                  </label>
                  <input
                    type="text"
                    value={formLatitude}
                    onChange={(e) => setFormLatitude(e.target.value)}
                    placeholder="45.035470"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Долгота (GPS)
                  </label>
                  <input
                    type="text"
                    value={formLongitude}
                    onChange={(e) => setFormLongitude(e.target.value)}
                    placeholder="38.975313"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Телефон
                </label>
                <input
                  type="text"
                  value={formPhone}
                  onChange={(e) => setFormPhone(e.target.value)}
                  placeholder="+7 (999) 123-45-67"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  График работы
                </label>
                <input
                  type="text"
                  value={formWorkingHours}
                  onChange={(e) => setFormWorkingHours(e.target.value)}
                  placeholder="Пн-Пт 9:00-18:00"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Порядок сортировки
                </label>
                <input
                  type="number"
                  value={formSortOrder}
                  onChange={(e) => setFormSortOrder(parseInt(e.target.value) || 0)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              <div className="flex items-center gap-3">
                <button
                  type="button"
                  onClick={() => setFormIsActive(!formIsActive)}
                  className={`relative w-12 h-6 rounded-full transition-colors ${
                    formIsActive ? 'bg-[#7cb342]' : 'bg-gray-300'
                  }`}
                >
                  <span
                    className={`absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${
                      formIsActive ? 'left-6' : 'left-0.5'
                    }`}
                  />
                </button>
                <span className="text-sm font-medium text-gray-700">
                  {formIsActive ? 'Склад активен' : 'Склад скрыт'}
                </span>
              </div>
            </div>

            <div className="p-6 border-t border-gray-200 flex gap-3">
              <button
                onClick={() => setModalOpen(false)}
                className="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Отмена
              </button>
              <button
                onClick={saveWarehouse}
                disabled={saving || !formName.trim()}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingWarehouse ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
