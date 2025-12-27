'use client';

import { useEffect, useState, useCallback } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Region {
  id: number;
  name: string;
}

interface City {
  id: number;
  name: string;
  region_id: number;
}

interface Salon {
  id: number;
  name: string;
  city: string;
  region: string | null;
  address: string | null;
  email: string | null;
  phone: string | null;
  working_hours: string | null;
  latitude: number | null;
  longitude: number | null;
  external_code: string | null;
  slug: string | null;
  region_id: number | null;
  city_id: number | null;
  region_name: string | null;
  city_name_ref: string | null;
  is_active: boolean;
  created_at: string;
}

interface Counts {
  active: number;
  inactive: number;
  total: number;
}

export default function SalonsPage() {
  const [salons, setSalons] = useState<Salon[]>([]);
  const [regions, setRegions] = useState<Region[]>([]);
  const [cities, setCities] = useState<City[]>([]);
  const [counts, setCounts] = useState<Counts>({ active: 0, inactive: 0, total: 0 });
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Filters
  const [filterRegion, setFilterRegion] = useState<string>('');
  const [filterCity, setFilterCity] = useState<string>('');
  const [searchQuery, setSearchQuery] = useState('');

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingSalon, setEditingSalon] = useState<Salon | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formName, setFormName] = useState('');
  const [formCity, setFormCity] = useState('');
  const [formRegion, setFormRegion] = useState('');
  const [formAddress, setFormAddress] = useState('');
  const [formEmail, setFormEmail] = useState('');
  const [formPhone, setFormPhone] = useState('');
  const [formWorkingHours, setFormWorkingHours] = useState('');
  const [formLatitude, setFormLatitude] = useState('');
  const [formLongitude, setFormLongitude] = useState('');
  const [formExternalCode, setFormExternalCode] = useState('');
  const [formSlug, setFormSlug] = useState('');
  const [formRegionId, setFormRegionId] = useState<number | null>(null);
  const [formCityId, setFormCityId] = useState<number | null>(null);
  const [formIsActive, setFormIsActive] = useState(true);

  // Filtered cities for form based on selected region
  const [formFilteredCities, setFormFilteredCities] = useState<City[]>([]);

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

  const fetchCities = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/cities');
      const data = await response.json();
      if (data.success) {
        setCities(data.data);
      }
    } catch (error) {
      console.error('Error fetching cities:', error);
    }
  }, []);

  const fetchSalons = useCallback(async () => {
    try {
      const params = new URLSearchParams();
      if (filterRegion) params.set('region_id', filterRegion);
      if (filterCity) params.set('city', filterCity);

      const url = `/api/e1admin/salons${params.toString() ? `?${params.toString()}` : ''}`;
      const response = await fetch(url);
      const data = await response.json();
      if (data.success) {
        setSalons(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching salons:', error);
    } finally {
      setLoading(false);
    }
  }, [filterRegion, filterCity]);

  useEffect(() => {
    fetchRegions();
    fetchCities();
  }, [fetchRegions, fetchCities]);

  useEffect(() => {
    setLoading(true);
    fetchSalons();
  }, [fetchSalons]);

  // Update filtered cities when region changes in form
  useEffect(() => {
    if (formRegionId) {
      setFormFilteredCities(cities.filter(c => c.region_id === formRegionId));
    } else {
      setFormFilteredCities(cities);
    }
  }, [formRegionId, cities]);

  const openCreateModal = () => {
    setEditingSalon(null);
    setFormName('');
    setFormCity('');
    setFormRegion('');
    setFormAddress('');
    setFormEmail('');
    setFormPhone('');
    setFormWorkingHours('');
    setFormLatitude('');
    setFormLongitude('');
    setFormExternalCode('');
    setFormSlug('');
    setFormRegionId(null);
    setFormCityId(null);
    setFormIsActive(true);
    setModalOpen(true);
  };

  const openEditModal = (salon: Salon) => {
    setEditingSalon(salon);
    setFormName(salon.name);
    setFormCity(salon.city);
    setFormRegion(salon.region || '');
    setFormAddress(salon.address || '');
    setFormEmail(salon.email || '');
    setFormPhone(salon.phone || '');
    setFormWorkingHours(salon.working_hours || '');
    setFormLatitude(salon.latitude?.toString() || '');
    setFormLongitude(salon.longitude?.toString() || '');
    setFormExternalCode(salon.external_code || '');
    setFormSlug(salon.slug || '');
    setFormRegionId(salon.region_id);
    setFormCityId(salon.city_id);
    setFormIsActive(salon.is_active);
    setModalOpen(true);
  };

  const saveSalon = async () => {
    if (!formName.trim()) {
      alert('Введите название салона');
      return;
    }

    if (!formCity.trim()) {
      alert('Введите город');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        name: formName,
        city: formCity,
        region: formRegion || null,
        address: formAddress || null,
        email: formEmail || null,
        phone: formPhone || null,
        working_hours: formWorkingHours || null,
        latitude: formLatitude ? parseFloat(formLatitude) : null,
        longitude: formLongitude ? parseFloat(formLongitude) : null,
        external_code: formExternalCode || null,
        slug: formSlug || null,
        region_id: formRegionId,
        city_id: formCityId,
        is_active: formIsActive,
      };

      let response;
      if (editingSalon) {
        response = await fetch(`/api/e1admin/salons/${editingSalon.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/salons', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchSalons();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (salon: Salon) => {
    setActionLoading(salon.id);
    try {
      const response = await fetch(`/api/e1admin/salons/${salon.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !salon.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchSalons();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteSalon = async (salon: Salon) => {
    if (!confirm(`Удалить салон "${salon.name}"? Это действие нельзя отменить.`)) return;

    setActionLoading(salon.id);
    try {
      const response = await fetch(`/api/e1admin/salons/${salon.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchSalons();
      } else {
        alert(data.error || 'Ошибка удаления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  // Get unique cities from salons for filter
  const uniqueCities = [...new Set(salons.map(s => s.city))].sort();

  // Filter salons by search query
  const filteredSalons = salons.filter(salon => {
    if (!searchQuery) return true;
    const query = searchQuery.toLowerCase();
    return (
      salon.name.toLowerCase().includes(query) ||
      salon.city.toLowerCase().includes(query) ||
      salon.address?.toLowerCase().includes(query) ||
      salon.phone?.toLowerCase().includes(query)
    );
  });

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Салоны</h1>
            <p className="text-gray-600 mt-1">Управление салонами</p>
          </div>
          <button
            onClick={openCreateModal}
            className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Добавить салон
          </button>
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
              placeholder="Поиск по названию, адресу, телефону..."
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
            />
          </div>
          <div className="sm:w-48">
            <select
              value={filterRegion}
              onChange={(e) => {
                setFilterRegion(e.target.value);
                setFilterCity('');
              }}
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
          <div className="sm:w-48">
            <select
              value={filterCity}
              onChange={(e) => setFilterCity(e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
            >
              <option value="">Все города</option>
              {uniqueCities.map((city) => (
                <option key={city} value={city}>
                  {city}
                </option>
              ))}
            </select>
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : filteredSalons.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              {searchQuery ? 'Салоны не найдены' : 'Нет салонов'}
            </h3>
            <p className="text-gray-500 mb-4">
              {searchQuery ? 'Попробуйте изменить параметры поиска' : 'Добавьте первый салон'}
            </p>
            {!searchQuery && (
              <button
                onClick={openCreateModal}
                className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                Добавить салон
              </button>
            )}
          </div>
        ) : (
          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Город</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Адрес</th>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                    <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {filteredSalons.map((salon) => (
                    <tr
                      key={salon.id}
                      className={`hover:bg-gray-50 ${!salon.is_active ? 'bg-gray-50/50' : ''}`}
                    >
                      <td className="px-4 py-4">
                        <div className="flex items-center gap-2">
                          <span className="font-medium text-gray-900">{salon.name}</span>
                          {!salon.is_active && (
                            <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                              Скрыт
                            </span>
                          )}
                        </div>
                      </td>
                      <td className="px-4 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">{salon.city}</div>
                        {salon.region_name && (
                          <div className="text-xs text-gray-500">{salon.region_name}</div>
                        )}
                      </td>
                      <td className="px-4 py-4">
                        <div className="text-sm text-gray-500 max-w-xs truncate" title={salon.address || ''}>
                          {salon.address || '-'}
                        </div>
                      </td>
                      <td className="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {salon.phone || '-'}
                      </td>
                      <td className="px-4 py-4 whitespace-nowrap text-center">
                        <button
                          onClick={() => toggleActive(salon)}
                          disabled={actionLoading === salon.id}
                          className={`p-1.5 rounded-lg transition-colors disabled:opacity-50 ${
                            salon.is_active
                              ? 'text-green-600 hover:bg-green-50'
                              : 'text-gray-400 hover:bg-gray-100'
                          }`}
                          title={salon.is_active ? 'Скрыть' : 'Показать'}
                        >
                          {salon.is_active ? (
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
                            onClick={() => openEditModal(salon)}
                            className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            title="Редактировать"
                          >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                          </button>
                          <button
                            onClick={() => deleteSalon(salon)}
                            disabled={actionLoading === salon.id}
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
          </div>
        )}
      </div>

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
          <div className="bg-white rounded-xl max-w-2xl w-full my-8">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingSalon ? 'Редактирование салона' : 'Новый салон'}
              </h2>
            </div>

            <div className="p-6 space-y-6 max-h-[60vh] overflow-y-auto">
              {/* Name */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Название <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder='Например: Салон Е1 в ТЦ "Галактика"'
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* City & Region row */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Город <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formCity}
                    onChange={(e) => setFormCity(e.target.value)}
                    placeholder="Например: Краснодар"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Регион (текст)
                  </label>
                  <input
                    type="text"
                    value={formRegion}
                    onChange={(e) => setFormRegion(e.target.value)}
                    placeholder="Например: Краснодарский край"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              {/* Region & City selects row */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Связь с регионом (из справочника)
                  </label>
                  <select
                    value={formRegionId || ''}
                    onChange={(e) => {
                      const newRegionId = e.target.value ? parseInt(e.target.value) : null;
                      setFormRegionId(newRegionId);
                      setFormCityId(null);
                    }}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  >
                    <option value="">Не выбрано</option>
                    {regions.map((region) => (
                      <option key={region.id} value={region.id}>
                        {region.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Связь с городом (из справочника)
                  </label>
                  <select
                    value={formCityId || ''}
                    onChange={(e) => setFormCityId(e.target.value ? parseInt(e.target.value) : null)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  >
                    <option value="">Не выбрано</option>
                    {formFilteredCities.map((city) => (
                      <option key={city.id} value={city.id}>
                        {city.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Address */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Адрес
                </label>
                <input
                  type="text"
                  value={formAddress}
                  onChange={(e) => setFormAddress(e.target.value)}
                  placeholder="ул. Пушкина, д. 10"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* Email & Phone row */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Email
                  </label>
                  <input
                    type="email"
                    value={formEmail}
                    onChange={(e) => setFormEmail(e.target.value)}
                    placeholder="salon@e-1.ru"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Телефон
                  </label>
                  <input
                    type="text"
                    value={formPhone}
                    onChange={(e) => setFormPhone(e.target.value)}
                    placeholder="+7 (928) 123-45-67"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              {/* Working hours */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Режим работы
                </label>
                <input
                  type="text"
                  value={formWorkingHours}
                  onChange={(e) => setFormWorkingHours(e.target.value)}
                  placeholder="ежедневно с 10:00 до 20:00"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* Coordinates row */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Широта (Latitude)
                  </label>
                  <input
                    type="text"
                    value={formLatitude}
                    onChange={(e) => setFormLatitude(e.target.value)}
                    placeholder="45.0355"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Долгота (Longitude)
                  </label>
                  <input
                    type="text"
                    value={formLongitude}
                    onChange={(e) => setFormLongitude(e.target.value)}
                    placeholder="38.9753"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              {/* External code & Slug row */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Символьный код (slug)
                  </label>
                  <input
                    type="text"
                    value={formSlug}
                    onChange={(e) => setFormSlug(e.target.value)}
                    placeholder="salon-krasnodar-galaktika"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
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
                  {formIsActive ? 'Салон активен' : 'Салон скрыт'}
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
                onClick={saveSalon}
                disabled={saving || !formName.trim() || !formCity.trim()}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingSalon ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
