'use client';

import { useEffect, useState, useCallback } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Region {
  id: number;
  name: string;
}

interface Warehouse {
  id: number;
  name: string;
  display_name: string | null;
}

interface ServicePrice {
  id: number;
  region_group: string;
}

interface City {
  id: number;
  name: string;
  name_prepositional: string | null;
  region_id: number;
  region_name: string;
  external_code: string | null;
  sort_order: number;
  is_active: boolean;
  created_at: string;
  salon_count: number;
  warehouse_id: number | null;
  warehouse_name: string | null;
  price_group: string | null;
}

interface Counts {
  active: number;
  inactive: number;
  total: number;
}

export default function CitiesPage() {
  const [cities, setCities] = useState<City[]>([]);
  const [regions, setRegions] = useState<Region[]>([]);
  const [warehouses, setWarehouses] = useState<Warehouse[]>([]);
  const [servicePrices, setServicePrices] = useState<ServicePrice[]>([]);
  const [counts, setCounts] = useState<Counts>({ active: 0, inactive: 0, total: 0 });
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Filters
  const [filterRegion, setFilterRegion] = useState<string>('');
  const [searchQuery, setSearchQuery] = useState('');

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingCity, setEditingCity] = useState<City | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formName, setFormName] = useState('');
  const [formNamePrepositional, setFormNamePrepositional] = useState('');
  const [formRegionId, setFormRegionId] = useState<number | null>(null);
  const [formExternalCode, setFormExternalCode] = useState('');
  const [formSortOrder, setFormSortOrder] = useState(500);
  const [formIsActive, setFormIsActive] = useState(true);
  const [formWarehouseId, setFormWarehouseId] = useState<number | null>(null);
  const [formPriceGroup, setFormPriceGroup] = useState('');

  // Auto-fill loading state
  const [autoFillLoading, setAutoFillLoading] = useState(false);

  const fetchRegions = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/regions');
      const data = await response.json();
      if (data.success) {
        setRegions(data.data);
      }
    } catch (error) {
      console.error('Error fetching regions:', error);
    }
  }, []);

  const fetchWarehouses = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/warehouses');
      const data = await response.json();
      if (data.success) {
        setWarehouses(data.data);
      }
    } catch (error) {
      console.error('Error fetching warehouses:', error);
    }
  }, []);

  const fetchServicePrices = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/service-prices');
      const data = await response.json();
      if (data.success) {
        setServicePrices(data.data);
      }
    } catch (error) {
      console.error('Error fetching service prices:', error);
    }
  }, []);

  const fetchCities = useCallback(async () => {
    try {
      let url = '/api/e1admin/cities';
      if (filterRegion) {
        url += `?region_id=${filterRegion}`;
      }
      const response = await fetch(url);
      const data = await response.json();
      if (data.success) {
        setCities(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching cities:', error);
    } finally {
      setLoading(false);
    }
  }, [filterRegion]);

  useEffect(() => {
    fetchRegions();
    fetchWarehouses();
    fetchServicePrices();
  }, [fetchRegions, fetchWarehouses, fetchServicePrices]);

  useEffect(() => {
    setLoading(true);
    fetchCities();
  }, [fetchCities]);

  const openCreateModal = () => {
    setEditingCity(null);
    setFormName('');
    setFormNamePrepositional('');
    setFormRegionId(regions.length > 0 ? regions[0].id : null);
    setFormExternalCode('');
    setFormSortOrder(500);
    setFormIsActive(true);
    setFormWarehouseId(null);
    setFormPriceGroup('');
    setModalOpen(true);
  };

  const openEditModal = (city: City) => {
    setEditingCity(city);
    setFormName(city.name);
    setFormNamePrepositional(city.name_prepositional || '');
    setFormRegionId(city.region_id);
    setFormExternalCode(city.external_code || '');
    setFormSortOrder(city.sort_order);
    setFormIsActive(city.is_active);
    setFormWarehouseId(city.warehouse_id);
    setFormPriceGroup(city.price_group || '');
    setModalOpen(true);
  };

  const saveCity = async () => {
    if (!formName.trim()) {
      alert('Введите название города');
      return;
    }

    if (!formRegionId) {
      alert('Выберите регион');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        name: formName,
        name_prepositional: formNamePrepositional || null,
        region_id: formRegionId,
        external_code: formExternalCode || null,
        sort_order: formSortOrder,
        is_active: formIsActive,
        warehouse_id: formWarehouseId || null,
        price_group: formPriceGroup || null,
      };

      let response;
      if (editingCity) {
        response = await fetch(`/api/e1admin/cities/${editingCity.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/cities', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchCities();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (city: City) => {
    setActionLoading(city.id);
    try {
      const response = await fetch(`/api/e1admin/cities/${city.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !city.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchCities();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteCity = async (city: City) => {
    if (!confirm(`Удалить город "${city.name}"? Это действие нельзя отменить.`)) return;

    setActionLoading(city.id);
    try {
      const response = await fetch(`/api/e1admin/cities/${city.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchCities();
      } else {
        alert(data.error || 'Ошибка удаления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  // Auto-fill prepositional names for all cities without them
  const autoFillPrepositional = async () => {
    if (!confirm('Автоматически заполнить предложный падеж для всех городов без этого поля?')) return;

    setAutoFillLoading(true);
    try {
      const response = await fetch('/api/e1admin/cities/autofill-prepositional', {
        method: 'POST',
      });
      const data = await response.json();
      if (data.success) {
        alert(`Обновлено ${data.updated} городов`);
        fetchCities();
      } else {
        alert(data.error || 'Ошибка автозаполнения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setAutoFillLoading(false);
    }
  };

  // Filter cities by search query
  const filteredCities = cities.filter(city => {
    if (!searchQuery) return true;
    const query = searchQuery.toLowerCase();
    return (
      city.name.toLowerCase().includes(query) ||
      city.region_name?.toLowerCase().includes(query)
    );
  });

  // Group cities by region
  const citiesByRegion: Record<string, City[]> = {};
  for (const city of filteredCities) {
    const regionName = city.region_name || 'Без региона';
    if (!citiesByRegion[regionName]) {
      citiesByRegion[regionName] = [];
    }
    citiesByRegion[regionName].push(city);
  }

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Города</h1>
            <p className="text-gray-600 mt-1">Управление городами</p>
          </div>
          <div className="flex gap-2">
            <button
              onClick={autoFillPrepositional}
              disabled={autoFillLoading}
              className="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors disabled:opacity-50"
              title="Автозаполнение предложного падежа"
            >
              {autoFillLoading ? (
                <div className="w-5 h-5 border-2 border-gray-400 border-t-transparent rounded-full animate-spin" />
              ) : (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              )}
              Автозаполнить падежи
            </button>
            <button
              onClick={openCreateModal}
              className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Добавить город
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

        {/* Filters */}
        <div className="flex flex-col sm:flex-row gap-4 mb-6">
          <div className="flex-1">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Поиск по названию..."
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
            />
          </div>
          <div className="sm:w-64">
            <select
              value={filterRegion}
              onChange={(e) => setFilterRegion(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
            >
              <option value="">Все регионы</option>
              {regions.map((region) => (
                <option key={region.id} value={region.id}>
                  {region.name}
                </option>
              ))}
            </select>
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : filteredCities.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              {searchQuery ? 'Города не найдены' : 'Нет городов'}
            </h3>
            <p className="text-gray-500 mb-4">
              {searchQuery ? 'Попробуйте изменить параметры поиска' : 'Добавьте первый город'}
            </p>
            {!searchQuery && (
              <button
                onClick={openCreateModal}
                className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                Добавить город
              </button>
            )}
          </div>
        ) : (
          <div className="space-y-6">
            {Object.entries(citiesByRegion).map(([regionName, regionCities]) => (
              <div key={regionName} className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div className="bg-gray-50 px-4 py-3 border-b border-gray-200">
                  <h3 className="font-medium text-gray-900">{regionName}</h3>
                  <p className="text-sm text-gray-500">{regionCities.length} городов</p>
                </div>
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                      <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Внешний код</th>
                      <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Салонов</th>
                      <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                      <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {regionCities.map((city) => (
                      <tr
                        key={city.id}
                        className={`hover:bg-gray-50 ${!city.is_active ? 'bg-gray-50/50' : ''}`}
                      >
                        <td className="px-4 py-4 whitespace-nowrap">
                          <div className="flex items-center gap-2">
                            <span className="font-medium text-gray-900">{city.name}</span>
                            {!city.is_active && (
                              <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                                Скрыт
                              </span>
                            )}
                          </div>
                        </td>
                        <td className="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                          {city.external_code || '-'}
                        </td>
                        <td className="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                          {city.salon_count}
                        </td>
                        <td className="px-4 py-4 whitespace-nowrap text-center">
                          <button
                            onClick={() => toggleActive(city)}
                            disabled={actionLoading === city.id}
                            className={`p-1.5 rounded-lg transition-colors disabled:opacity-50 ${
                              city.is_active
                                ? 'text-green-600 hover:bg-green-50'
                                : 'text-gray-400 hover:bg-gray-100'
                            }`}
                            title={city.is_active ? 'Скрыть' : 'Показать'}
                          >
                            {city.is_active ? (
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
                              onClick={() => openEditModal(city)}
                              className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                              title="Редактировать"
                            >
                              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </button>
                            <button
                              onClick={() => deleteCity(city)}
                              disabled={actionLoading === city.id}
                              className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
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
            ))}
          </div>
        )}
      </div>

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl max-w-md w-full">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingCity ? 'Редактирование города' : 'Новый город'}
              </h2>
            </div>

            <div className="p-6 space-y-6">
              {/* Name */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Название <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="Например: Краснодар"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* Name Prepositional */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  В предложном падеже
                </label>
                <input
                  type="text"
                  value={formNamePrepositional}
                  onChange={(e) => setFormNamePrepositional(e.target.value)}
                  placeholder="Например: Краснодаре (для &quot;в Краснодаре&quot;)"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
                <p className="text-xs text-gray-500 mt-1">Используется в тексте &quot;Шкафы в [город]&quot;</p>
              </div>

              {/* Region */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Регион <span className="text-red-500">*</span>
                </label>
                <select
                  value={formRegionId || ''}
                  onChange={(e) => setFormRegionId(e.target.value ? parseInt(e.target.value) : null)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                >
                  <option value="">Выберите регион</option>
                  {regions.map((region) => (
                    <option key={region.id} value={region.id}>
                      {region.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* External Code */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Внешний код
                </label>
                <input
                  type="text"
                  value={formExternalCode}
                  onChange={(e) => setFormExternalCode(e.target.value)}
                  placeholder="Код из внешней системы"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* Warehouse */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Склад для самовывоза
                </label>
                <select
                  value={formWarehouseId || ''}
                  onChange={(e) => setFormWarehouseId(e.target.value ? parseInt(e.target.value) : null)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                >
                  <option value="">Не выбран</option>
                  {warehouses.map((warehouse) => (
                    <option key={warehouse.id} value={warehouse.id}>
                      {warehouse.display_name || warehouse.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* Price Group */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Группа прайса услуг
                </label>
                <select
                  value={formPriceGroup}
                  onChange={(e) => setFormPriceGroup(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                >
                  <option value="">Не выбрана</option>
                  {servicePrices.map((price) => (
                    <option key={price.id} value={price.region_group}>
                      {price.region_group}
                    </option>
                  ))}
                </select>
              </div>

              {/* Sort Order */}
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
                <p className="text-xs text-gray-500 mt-1">Меньшее значение = выше в списке</p>
              </div>

              {/* Active toggle */}
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
                  {formIsActive ? 'Город активен' : 'Город скрыт'}
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
                onClick={saveCity}
                disabled={saving || !formName.trim() || !formRegionId}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingCity ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
