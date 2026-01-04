'use client';

import { useState, useEffect, useCallback } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface CartItem {
  name: string;
  slug: string;
  price: number;
  quantity: number;
  width?: number;
  height?: number;
  depth?: number;
  bodyColor?: string;
  profileColor?: string;
  filling?: string;
  includeAssembly: boolean;
  assemblyPrice: number;
}

interface Order {
  id: number;
  customer_name: string;
  customer_phone: string;
  customer_email: string | null;
  promo_code: string | null;
  comment: string | null;
  city: string | null;
  delivery_type: string;
  delivery_address: string | null;
  lift_type: string;
  floor: number | null;
  delivery_cost: number;
  lift_cost: number;
  assembly_cost: number;
  items: CartItem[];
  total_price: number;
  status: string;
  email_sent: boolean;
  created_at: string;
  updated_at: string;
}

const statusLabels: Record<string, string> = {
  new: 'Новый',
  processing: 'В обработке',
  completed: 'Выполнен',
  cancelled: 'Отменён',
};

const statusColors: Record<string, string> = {
  new: 'bg-blue-100 text-blue-800',
  processing: 'bg-yellow-100 text-yellow-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
};

export default function OrdersPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [counts, setCounts] = useState<Record<string, number>>({});
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [updating, setUpdating] = useState(false);

  const fetchOrders = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (searchQuery) params.append('search', searchQuery);
      if (statusFilter) params.append('status', statusFilter);
      if (dateFrom) params.append('dateFrom', dateFrom);
      if (dateTo) params.append('dateTo', dateTo);

      const response = await fetch(`/api/e1admin/orders?${params}`);
      const data = await response.json();

      if (data.success) {
        setOrders(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching orders:', error);
    } finally {
      setLoading(false);
    }
  }, [searchQuery, statusFilter, dateFrom, dateTo]);

  useEffect(() => {
    fetchOrders();
  }, [fetchOrders]);

  const handleStatusChange = async (id: number, newStatus: string) => {
    setUpdating(true);
    try {
      const response = await fetch('/api/e1admin/orders', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status: newStatus }),
      });

      if (response.ok) {
        fetchOrders();
        if (selectedOrder && selectedOrder.id === id) {
          setSelectedOrder({ ...selectedOrder, status: newStatus });
        }
      }
    } catch (error) {
      console.error('Error updating status:', error);
    } finally {
      setUpdating(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Удалить этот заказ?')) return;

    setDeleting(true);
    try {
      const response = await fetch('/api/e1admin/orders', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });

      if (response.ok) {
        fetchOrders();
        setSelectedOrder(null);
      }
    } catch (error) {
      console.error('Error deleting order:', error);
    } finally {
      setDeleting(false);
    }
  };

  const handleBulkDelete = async () => {
    if (selectedIds.size === 0) return;
    if (!confirm(`Удалить ${selectedIds.size} заказов?`)) return;

    setDeleting(true);
    try {
      const response = await fetch('/api/e1admin/orders', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: Array.from(selectedIds) }),
      });

      if (response.ok) {
        setSelectedIds(new Set());
        fetchOrders();
      }
    } catch (error) {
      console.error('Error bulk deleting orders:', error);
    } finally {
      setDeleting(false);
    }
  };

  const toggleSelectAll = () => {
    if (selectedIds.size === orders.length) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(orders.map(o => o.id)));
    }
  };

  const toggleSelect = (id: number) => {
    const newSet = new Set(selectedIds);
    if (newSet.has(id)) {
      newSet.delete(id);
    } else {
      newSet.add(id);
    }
    setSelectedIds(newSet);
  };

  const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const formatPrice = (price: number) => {
    return price.toLocaleString('ru-RU') + ' ₽';
  };

  const getLiftTypeLabel = (liftType: string) => {
    switch (liftType) {
      case 'none': return 'Не требуется';
      case 'stairs': return 'Без лифта';
      case 'elevator': return 'С грузовым лифтом';
      default: return liftType;
    }
  };

  return (
    <AdminPageWrapper>
      <div className="p-4">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
          <div>
            <h1 className="text-lg font-bold text-gray-900">Заказы</h1>
            <p className="text-sm text-gray-500">Всего: {counts.total || 0}</p>
          </div>

          <div className="flex flex-wrap gap-2">
            {selectedIds.size > 0 && (
              <button
                onClick={handleBulkDelete}
                disabled={deleting}
                className="px-3 py-1.5 bg-red-500 text-white text-sm rounded hover:bg-red-600 disabled:opacity-50"
              >
                Удалить ({selectedIds.size})
              </button>
            )}
          </div>
        </div>

        {/* Filters */}
        <div className="flex flex-col md:flex-row gap-3 mb-4">
          <div className="flex-1">
            <input
              type="text"
              placeholder="Поиск по имени, телефону, email, номеру заказа..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
            />
          </div>
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
          >
            <option value="">Все статусы ({counts.total || 0})</option>
            <option value="new">Новые ({counts.new || 0})</option>
            <option value="processing">В обработке ({counts.processing || 0})</option>
            <option value="completed">Выполненные ({counts.completed || 0})</option>
            <option value="cancelled">Отменённые ({counts.cancelled || 0})</option>
          </select>
          <div className="flex items-center gap-2">
            <input
              type="date"
              value={dateFrom}
              onChange={(e) => setDateFrom(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
              title="Дата от"
            />
            <span className="text-gray-400">—</span>
            <input
              type="date"
              value={dateTo}
              onChange={(e) => setDateTo(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
              title="Дата до"
            />
            {(dateFrom || dateTo) && (
              <button
                onClick={() => { setDateFrom(''); setDateTo(''); }}
                className="p-2 text-gray-400 hover:text-gray-600"
                title="Сбросить даты"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            )}
          </div>
        </div>

        {/* Table */}
        <div className="bg-white rounded-lg shadow-sm overflow-hidden">
          {loading ? (
            <div className="p-8 text-center">
              <div className="w-8 h-8 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin mx-auto"></div>
            </div>
          ) : orders.length === 0 ? (
            <div className="p-8 text-center text-gray-500">
              Заказы не найдены
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-3 py-2 text-left">
                      <input
                        type="checkbox"
                        checked={selectedIds.size === orders.length && orders.length > 0}
                        onChange={toggleSelectAll}
                        className="rounded border-gray-300"
                      />
                    </th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">№</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Статус</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Клиент</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Телефон</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Город</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Промокод</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Товаров</th>
                    <th className="px-3 py-2 text-right font-medium text-gray-700">Сумма</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Дата</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {orders.map((order) => (
                    <tr
                      key={order.id}
                      className="hover:bg-gray-50 cursor-pointer"
                      onClick={() => setSelectedOrder(order)}
                    >
                      <td className="px-3 py-2" onClick={(e) => e.stopPropagation()}>
                        <input
                          type="checkbox"
                          checked={selectedIds.has(order.id)}
                          onChange={() => toggleSelect(order.id)}
                          className="rounded border-gray-300"
                        />
                      </td>
                      <td className="px-3 py-2 font-medium text-gray-900">#{order.id}</td>
                      <td className="px-3 py-2" onClick={(e) => e.stopPropagation()}>
                        <select
                          value={order.status}
                          onChange={(e) => handleStatusChange(order.id, e.target.value)}
                          disabled={updating}
                          className={`px-2 py-0.5 rounded-full text-xs font-medium border-0 cursor-pointer ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}`}
                        >
                          {Object.entries(statusLabels).map(([value, label]) => (
                            <option key={value} value={value}>{label}</option>
                          ))}
                        </select>
                      </td>
                      <td className="px-3 py-2 text-gray-900">{order.customer_name}</td>
                      <td className="px-3 py-2 text-gray-600">{order.customer_phone}</td>
                      <td className="px-3 py-2 text-gray-600">{order.city || '—'}</td>
                      <td className="px-3 py-2">
                        {order.promo_code ? (
                          <span className="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {order.promo_code}
                          </span>
                        ) : (
                          <span className="text-gray-400">—</span>
                        )}
                      </td>
                      <td className="px-3 py-2 text-gray-600">{order.items.length}</td>
                      <td className="px-3 py-2 text-right font-medium text-gray-900">
                        {formatPrice(order.total_price)}
                      </td>
                      <td className="px-3 py-2 text-gray-500 text-xs">{formatDate(order.created_at)}</td>
                      <td className="px-3 py-2" onClick={(e) => e.stopPropagation()}>
                        <button
                          onClick={() => handleDelete(order.id)}
                          disabled={deleting}
                          className="p-1 text-gray-400 hover:text-red-500 disabled:opacity-50"
                          title="Удалить"
                        >
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Detail Modal */}
        {selectedOrder && (
          <>
            <div
              className="fixed inset-0 bg-black/50 z-40"
              onClick={() => setSelectedOrder(null)}
            />
            <div className="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:max-w-2xl md:w-full bg-white rounded-xl shadow-xl z-50 overflow-hidden flex flex-col max-h-[90vh]">
              <div className="flex items-center justify-between p-4 border-b shrink-0">
                <div className="flex items-center gap-3">
                  <h3 className="font-bold text-gray-900">Заказ #{selectedOrder.id}</h3>
                  <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[selectedOrder.status] || 'bg-gray-100 text-gray-800'}`}>
                    {statusLabels[selectedOrder.status] || selectedOrder.status}
                  </span>
                </div>
                <button
                  onClick={() => setSelectedOrder(null)}
                  className="p-1 hover:bg-gray-100 rounded"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div className="p-4 overflow-y-auto flex-1 space-y-4">
                {/* Customer info */}
                <div className="bg-gray-50 rounded-lg p-4">
                  <h4 className="font-medium text-gray-900 mb-3">Покупатель</h4>
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div>
                      <div className="text-gray-500 text-xs">ФИО</div>
                      <div className="font-medium">{selectedOrder.customer_name}</div>
                    </div>
                    <div>
                      <div className="text-gray-500 text-xs">Телефон</div>
                      <div><a href={`tel:${selectedOrder.customer_phone}`} className="text-[#62bb46]">{selectedOrder.customer_phone}</a></div>
                    </div>
                    {selectedOrder.customer_email && (
                      <div>
                        <div className="text-gray-500 text-xs">Email</div>
                        <div><a href={`mailto:${selectedOrder.customer_email}`} className="text-[#62bb46]">{selectedOrder.customer_email}</a></div>
                      </div>
                    )}
                    {selectedOrder.city && (
                      <div>
                        <div className="text-gray-500 text-xs">Город</div>
                        <div>{selectedOrder.city}</div>
                      </div>
                    )}
                    {selectedOrder.promo_code && (
                      <div>
                        <div className="text-gray-500 text-xs">Промокод</div>
                        <div className="text-[#62bb46] font-medium">{selectedOrder.promo_code}</div>
                      </div>
                    )}
                  </div>
                  {selectedOrder.comment && (
                    <div className="mt-3">
                      <div className="text-gray-500 text-xs mb-1">Комментарий</div>
                      <div className="bg-white rounded p-2 text-sm">{selectedOrder.comment}</div>
                    </div>
                  )}
                </div>

                {/* Delivery info */}
                <div className="bg-gray-50 rounded-lg p-4">
                  <h4 className="font-medium text-gray-900 mb-3">Доставка</h4>
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div>
                      <div className="text-gray-500 text-xs">Тип</div>
                      <div>{selectedOrder.delivery_type === 'pickup' ? 'Самовывоз' : 'Доставка'}</div>
                    </div>
                    {selectedOrder.delivery_address && (
                      <div className="col-span-2">
                        <div className="text-gray-500 text-xs">Адрес</div>
                        <div>{selectedOrder.delivery_address}</div>
                      </div>
                    )}
                    <div>
                      <div className="text-gray-500 text-xs">Подъём</div>
                      <div>
                        {getLiftTypeLabel(selectedOrder.lift_type)}
                        {selectedOrder.lift_type === 'stairs' && selectedOrder.floor && ` (${selectedOrder.floor} этаж)`}
                      </div>
                    </div>
                    <div>
                      <div className="text-gray-500 text-xs">Стоимость доставки</div>
                      <div className="font-medium">{formatPrice(selectedOrder.delivery_cost)}</div>
                    </div>
                    {selectedOrder.lift_cost > 0 && (
                      <div>
                        <div className="text-gray-500 text-xs">Подъём на этаж</div>
                        <div className="font-medium">{formatPrice(selectedOrder.lift_cost)}</div>
                      </div>
                    )}
                    {selectedOrder.assembly_cost > 0 && (
                      <div>
                        <div className="text-gray-500 text-xs">Выезд сборщика</div>
                        <div className="font-medium">{formatPrice(selectedOrder.assembly_cost)}</div>
                      </div>
                    )}
                  </div>
                </div>

                {/* Items */}
                <div>
                  <h4 className="font-medium text-gray-900 mb-3">Товары ({selectedOrder.items.length})</h4>
                  <div className="space-y-3">
                    {selectedOrder.items.map((item, index) => (
                      <div key={index} className="bg-gray-50 rounded-lg p-3">
                        <div className="flex justify-between items-start mb-2">
                          <div className="font-medium text-gray-900">{item.name}</div>
                          <div className="font-medium text-gray-900">{formatPrice(item.price * item.quantity)}</div>
                        </div>
                        <div className="text-xs text-gray-500 space-y-1">
                          {item.width && item.height && item.depth && (
                            <div>Размер: {item.width}×{item.height}×{item.depth} мм</div>
                          )}
                          {item.bodyColor && <div>Цвет корпуса: {item.bodyColor}</div>}
                          {item.profileColor && <div>Цвет профиля: {item.profileColor}</div>}
                          {item.filling && <div>Наполнение: {item.filling}</div>}
                          <div className="flex justify-between pt-1">
                            <span>Кол-во: {item.quantity} × {formatPrice(item.price)}</span>
                            {item.includeAssembly && (
                              <span className="text-[#62bb46]">+ Сборка: {formatPrice(item.assemblyPrice)}</span>
                            )}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Total */}
                <div className="bg-[#62bb46]/10 rounded-lg p-4">
                  <div className="flex justify-between items-center text-lg font-bold text-gray-900">
                    <span>Итого</span>
                    <span>{formatPrice(selectedOrder.total_price)}</span>
                  </div>
                </div>

                {/* Meta info */}
                <div className="text-xs text-gray-400 flex justify-between">
                  <span>Создан: {formatDate(selectedOrder.created_at)}</span>
                  <span>Email: {selectedOrder.email_sent ? 'Отправлен' : 'Не отправлен'}</span>
                </div>
              </div>

              <div className="p-4 border-t flex justify-between gap-2 shrink-0">
                <select
                  value={selectedOrder.status}
                  onChange={(e) => handleStatusChange(selectedOrder.id, e.target.value)}
                  disabled={updating}
                  className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46]"
                >
                  {Object.entries(statusLabels).map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
                <div className="flex gap-2">
                  <button
                    onClick={() => setSelectedOrder(null)}
                    className="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                  >
                    Закрыть
                  </button>
                  <button
                    onClick={() => handleDelete(selectedOrder.id)}
                    disabled={deleting}
                    className="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50"
                  >
                    Удалить
                  </button>
                </div>
              </div>
            </div>
          </>
        )}
      </div>
    </AdminPageWrapper>
  );
}
