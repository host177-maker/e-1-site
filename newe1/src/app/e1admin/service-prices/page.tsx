'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface ServicePrice {
  id: number;
  region_group: string;
  delivery_base_price: number;
  delivery_per_km: number;
  floor_lift_price: number;
  elevator_lift_price: number;
  assembly_per_km: number;
  is_active: boolean;
  sort_order: number;
  created_at: string;
}

export default function ServicePricesPage() {
  const [prices, setPrices] = useState<ServicePrice[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingPrice, setEditingPrice] = useState<ServicePrice | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formRegionGroup, setFormRegionGroup] = useState('');
  const [formDeliveryBase, setFormDeliveryBase] = useState(0);
  const [formDeliveryPerKm, setFormDeliveryPerKm] = useState(0);
  const [formFloorLift, setFormFloorLift] = useState(0);
  const [formElevatorLift, setFormElevatorLift] = useState(0);
  const [formAssemblyPerKm, setFormAssemblyPerKm] = useState(0);
  const [formSortOrder, setFormSortOrder] = useState(500);
  const [formIsActive, setFormIsActive] = useState(true);

  // Import
  const [importing, setImporting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const fetchPrices = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/service-prices');
      const data = await response.json();
      if (data.success) {
        setPrices(data.data);
      }
    } catch (error) {
      console.error('Error fetching service prices:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchPrices();
  }, [fetchPrices]);

  const openCreateModal = () => {
    setEditingPrice(null);
    setFormRegionGroup('');
    setFormDeliveryBase(1590);
    setFormDeliveryPerKm(45);
    setFormFloorLift(350);
    setFormElevatorLift(550);
    setFormAssemblyPerKm(45);
    setFormSortOrder(500);
    setFormIsActive(true);
    setModalOpen(true);
  };

  const openEditModal = (price: ServicePrice) => {
    setEditingPrice(price);
    setFormRegionGroup(price.region_group);
    setFormDeliveryBase(Number(price.delivery_base_price));
    setFormDeliveryPerKm(Number(price.delivery_per_km));
    setFormFloorLift(Number(price.floor_lift_price));
    setFormElevatorLift(Number(price.elevator_lift_price));
    setFormAssemblyPerKm(Number(price.assembly_per_km));
    setFormSortOrder(price.sort_order);
    setFormIsActive(price.is_active);
    setModalOpen(true);
  };

  const savePrice = async () => {
    if (!formRegionGroup.trim()) {
      alert('Введите название группы');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        region_group: formRegionGroup,
        delivery_base_price: formDeliveryBase,
        delivery_per_km: formDeliveryPerKm,
        floor_lift_price: formFloorLift,
        elevator_lift_price: formElevatorLift,
        assembly_per_km: formAssemblyPerKm,
        sort_order: formSortOrder,
        is_active: formIsActive,
      };

      let response;
      if (editingPrice) {
        response = await fetch(`/api/e1admin/service-prices/${editingPrice.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/service-prices', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchPrices();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (price: ServicePrice) => {
    setActionLoading(price.id);
    try {
      const response = await fetch(`/api/e1admin/service-prices/${price.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !price.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchPrices();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deletePrice = async (price: ServicePrice) => {
    if (!confirm(`Удалить группу "${price.region_group}"? Это действие нельзя отменить.`)) return;

    setActionLoading(price.id);
    try {
      const response = await fetch(`/api/e1admin/service-prices/${price.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchPrices();
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

      const response = await fetch('/api/e1admin/service-prices', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        alert(data.message || `Импортировано ${data.imported} групп прайса`);
        fetchPrices();
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

  const formatPrice = (value: number | string) => {
    return Number(value).toLocaleString('ru-RU');
  };

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Прайс услуг</h1>
            <p className="text-gray-600 mt-1">Настройка стоимости доставки и подъёма по регионам</p>
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
              Добавить группу
            </button>
          </div>
        </div>

        {/* Info */}
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <div className="flex gap-3">
            <svg className="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div className="text-sm text-blue-700">
              <p className="font-medium mb-1">Расчёт стоимости доставки:</p>
              <p>Стоимость = Базовая цена + (Расстояние в км × Цена за км)</p>
              <p className="mt-1">Подъём на этаж = Цена за этаж × Количество этажей (для лестницы) или фиксированная цена (для лифта)</p>
            </div>
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : prices.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Нет групп прайса</h3>
            <p className="text-gray-500 mb-4">Добавьте первую группу или импортируйте из Excel (лист ПрайсУслуг)</p>
          </div>
        ) : (
          <div className="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Группа регионов</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Базовая цена</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">За 1 км</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Подъём (этаж)</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Подъём (лифт)</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Сборщик/км</th>
                  <th className="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                  <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {prices.map((price) => (
                  <tr
                    key={price.id}
                    className={`hover:bg-gray-50 ${!price.is_active ? 'bg-gray-50/50' : ''}`}
                  >
                    <td className="px-4 py-4">
                      <div className="flex items-center gap-2">
                        <span className="font-medium text-gray-900">{price.region_group}</span>
                        {!price.is_active && (
                          <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                            Скрыт
                          </span>
                        )}
                      </div>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm">
                      <span className="font-medium">{formatPrice(price.delivery_base_price)}</span> <span className="text-gray-500">₽</span>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm">
                      <span className="font-medium">{formatPrice(price.delivery_per_km)}</span> <span className="text-gray-500">₽</span>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm">
                      <span className="font-medium">{formatPrice(price.floor_lift_price)}</span> <span className="text-gray-500">₽</span>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm">
                      <span className="font-medium">{formatPrice(price.elevator_lift_price)}</span> <span className="text-gray-500">₽</span>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center text-sm">
                      <span className="font-medium">{formatPrice(price.assembly_per_km)}</span> <span className="text-gray-500">₽</span>
                    </td>
                    <td className="px-4 py-4 whitespace-nowrap text-center">
                      <button
                        onClick={() => toggleActive(price)}
                        disabled={actionLoading === price.id}
                        className={`p-1.5 rounded-lg transition-colors disabled:opacity-50 ${
                          price.is_active
                            ? 'text-green-600 hover:bg-green-50'
                            : 'text-gray-400 hover:bg-gray-100'
                        }`}
                        title={price.is_active ? 'Скрыть' : 'Показать'}
                      >
                        {price.is_active ? (
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
                          onClick={() => openEditModal(price)}
                          className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                          title="Редактировать"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button
                          onClick={() => deletePrice(price)}
                          disabled={actionLoading === price.id}
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
        )}
      </div>

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingPrice ? 'Редактирование группы' : 'Новая группа прайса'}
              </h2>
            </div>

            <div className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Название группы <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formRegionGroup}
                  onChange={(e) => setFormRegionGroup(e.target.value)}
                  placeholder="Например: Москва, Центральный, Южный"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Базовая цена доставки (₽)
                  </label>
                  <input
                    type="number"
                    value={formDeliveryBase}
                    onChange={(e) => setFormDeliveryBase(parseFloat(e.target.value) || 0)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Цена за 1 км (₽)
                  </label>
                  <input
                    type="number"
                    value={formDeliveryPerKm}
                    onChange={(e) => setFormDeliveryPerKm(parseFloat(e.target.value) || 0)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Подъём на этаж (без лифта) (₽)
                  </label>
                  <input
                    type="number"
                    value={formFloorLift}
                    onChange={(e) => setFormFloorLift(parseFloat(e.target.value) || 0)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                  <p className="text-xs text-gray-500 mt-1">За каждый этаж</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Подъём с лифтом (₽)
                  </label>
                  <input
                    type="number"
                    value={formElevatorLift}
                    onChange={(e) => setFormElevatorLift(parseFloat(e.target.value) || 0)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                  <p className="text-xs text-gray-500 mt-1">Фиксированная цена</p>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Сборщик за 1 км (₽)
                </label>
                <input
                  type="number"
                  value={formAssemblyPerKm}
                  onChange={(e) => setFormAssemblyPerKm(parseFloat(e.target.value) || 0)}
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
                  {formIsActive ? 'Группа активна' : 'Группа скрыта'}
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
                onClick={savePrice}
                disabled={saving || !formRegionGroup.trim()}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingPrice ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
