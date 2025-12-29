'use client';

import { useState, useEffect, useCallback } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface B2BLead {
  id: number;
  lead_type: string;
  full_name: string;
  phone: string;
  email: string | null;
  city: string | null;
  company_name: string | null;
  website: string | null;
  proposal: string | null;
  file_name: string | null;
  file_path: string | null;
  created_at: string;
}

const leadTypeLabels: Record<string, string> = {
  franchise: 'Франшиза',
  wholesale: 'Опт',
  marketplace: 'Маркетплейс',
  suppliers_materials: 'Поставки материалов',
  suppliers_logistics: 'Логистика',
};

const leadTypeColors: Record<string, string> = {
  franchise: 'bg-purple-100 text-purple-800',
  wholesale: 'bg-blue-100 text-blue-800',
  marketplace: 'bg-green-100 text-green-800',
  suppliers_materials: 'bg-orange-100 text-orange-800',
  suppliers_logistics: 'bg-yellow-100 text-yellow-800',
};

export default function B2BLeadsPage() {
  const [leads, setLeads] = useState<B2BLead[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [typeFilter, setTypeFilter] = useState<string>('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [counts, setCounts] = useState<Record<string, number>>({});
  const [selectedLead, setSelectedLead] = useState<B2BLead | null>(null);
  const [deleting, setDeleting] = useState(false);

  const fetchLeads = useCallback(async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (searchQuery) params.append('search', searchQuery);
      if (typeFilter) params.append('type', typeFilter);
      if (dateFrom) params.append('dateFrom', dateFrom);
      if (dateTo) params.append('dateTo', dateTo);

      const response = await fetch(`/api/e1admin/b2b-leads?${params}`);
      const data = await response.json();

      if (data.success) {
        setLeads(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching leads:', error);
    } finally {
      setLoading(false);
    }
  }, [searchQuery, typeFilter, dateFrom, dateTo]);

  useEffect(() => {
    fetchLeads();
  }, [fetchLeads]);

  const handleDelete = async (id: number) => {
    if (!confirm('Удалить эту заявку?')) return;

    setDeleting(true);
    try {
      const response = await fetch('/api/e1admin/b2b-leads', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });

      if (response.ok) {
        fetchLeads();
        setSelectedLead(null);
      }
    } catch (error) {
      console.error('Error deleting lead:', error);
    } finally {
      setDeleting(false);
    }
  };

  const handleBulkDelete = async () => {
    if (selectedIds.size === 0) return;
    if (!confirm(`Удалить ${selectedIds.size} заявок?`)) return;

    setDeleting(true);
    try {
      const response = await fetch('/api/e1admin/b2b-leads', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ids: Array.from(selectedIds) }),
      });

      if (response.ok) {
        setSelectedIds(new Set());
        fetchLeads();
      }
    } catch (error) {
      console.error('Error bulk deleting leads:', error);
    } finally {
      setDeleting(false);
    }
  };

  const toggleSelectAll = () => {
    if (selectedIds.size === leads.length) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(leads.map(l => l.id)));
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

  return (
    <AdminPageWrapper>
      <div className="p-4">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
          <div>
            <h1 className="text-lg font-bold text-gray-900">Заявки B2B</h1>
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
              placeholder="Поиск по имени, телефону, email, компании..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
            />
          </div>
          <select
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
            className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#62bb46] text-sm"
          >
            <option value="">Все типы ({counts.total || 0})</option>
            <option value="franchise">Франшиза ({counts.franchise || 0})</option>
            <option value="wholesale">Опт ({counts.wholesale || 0})</option>
            <option value="marketplace">Маркетплейс ({counts.marketplace || 0})</option>
            <option value="suppliers_materials">Поставки материалов ({counts.suppliers_materials || 0})</option>
            <option value="suppliers_logistics">Логистика ({counts.suppliers_logistics || 0})</option>
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
          ) : leads.length === 0 ? (
            <div className="p-8 text-center text-gray-500">
              Заявки не найдены
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-3 py-2 text-left">
                      <input
                        type="checkbox"
                        checked={selectedIds.size === leads.length && leads.length > 0}
                        onChange={toggleSelectAll}
                        className="rounded border-gray-300"
                      />
                    </th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Тип</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">ФИО</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Телефон</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Email</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Компания</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Дата</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700">Файл</th>
                    <th className="px-3 py-2 text-left font-medium text-gray-700"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {leads.map((lead) => (
                    <tr
                      key={lead.id}
                      className="hover:bg-gray-50 cursor-pointer"
                      onClick={() => setSelectedLead(lead)}
                    >
                      <td className="px-3 py-2" onClick={(e) => e.stopPropagation()}>
                        <input
                          type="checkbox"
                          checked={selectedIds.has(lead.id)}
                          onChange={() => toggleSelect(lead.id)}
                          className="rounded border-gray-300"
                        />
                      </td>
                      <td className="px-3 py-2">
                        <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${leadTypeColors[lead.lead_type] || 'bg-gray-100 text-gray-800'}`}>
                          {leadTypeLabels[lead.lead_type] || lead.lead_type}
                        </span>
                      </td>
                      <td className="px-3 py-2 font-medium text-gray-900">{lead.full_name}</td>
                      <td className="px-3 py-2 text-gray-600">{lead.phone || '—'}</td>
                      <td className="px-3 py-2 text-gray-600">{lead.email || '—'}</td>
                      <td className="px-3 py-2 text-gray-600">{lead.company_name || '—'}</td>
                      <td className="px-3 py-2 text-gray-500 text-xs">{formatDate(lead.created_at)}</td>
                      <td className="px-3 py-2" onClick={(e) => e.stopPropagation()}>
                        {lead.file_path ? (
                          <a
                            href={lead.file_path}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-[#62bb46] hover:underline text-xs"
                          >
                            {lead.file_name || 'Файл'}
                          </a>
                        ) : (
                          <span className="text-gray-400">—</span>
                        )}
                      </td>
                      <td className="px-3 py-2" onClick={(e) => e.stopPropagation()}>
                        <button
                          onClick={() => handleDelete(lead.id)}
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
        {selectedLead && (
          <>
            <div
              className="fixed inset-0 bg-black/50 z-40"
              onClick={() => setSelectedLead(null)}
            />
            <div className="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:max-w-lg md:w-full bg-white rounded-xl shadow-xl z-50 overflow-hidden">
              <div className="flex items-center justify-between p-4 border-b">
                <h3 className="font-bold text-gray-900">Детали заявки</h3>
                <button
                  onClick={() => setSelectedLead(null)}
                  className="p-1 hover:bg-gray-100 rounded"
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div className="p-4 max-h-[70vh] overflow-y-auto space-y-3">
                <div>
                  <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${leadTypeColors[selectedLead.lead_type] || 'bg-gray-100 text-gray-800'}`}>
                    {leadTypeLabels[selectedLead.lead_type] || selectedLead.lead_type}
                  </span>
                </div>

                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <div className="text-gray-500 text-xs">ФИО</div>
                    <div className="font-medium">{selectedLead.full_name}</div>
                  </div>
                  {selectedLead.phone && (
                    <div>
                      <div className="text-gray-500 text-xs">Телефон</div>
                      <div><a href={`tel:${selectedLead.phone}`} className="text-[#62bb46]">{selectedLead.phone}</a></div>
                    </div>
                  )}
                  {selectedLead.email && (
                    <div>
                      <div className="text-gray-500 text-xs">Email</div>
                      <div><a href={`mailto:${selectedLead.email}`} className="text-[#62bb46]">{selectedLead.email}</a></div>
                    </div>
                  )}
                  {selectedLead.city && (
                    <div>
                      <div className="text-gray-500 text-xs">Город</div>
                      <div>{selectedLead.city}</div>
                    </div>
                  )}
                  {selectedLead.company_name && (
                    <div>
                      <div className="text-gray-500 text-xs">Компания</div>
                      <div>{selectedLead.company_name}</div>
                    </div>
                  )}
                  {selectedLead.website && (
                    <div>
                      <div className="text-gray-500 text-xs">Сайт</div>
                      <div><a href={selectedLead.website} target="_blank" rel="noopener noreferrer" className="text-[#62bb46]">{selectedLead.website}</a></div>
                    </div>
                  )}
                  <div>
                    <div className="text-gray-500 text-xs">Дата</div>
                    <div>{formatDate(selectedLead.created_at)}</div>
                  </div>
                </div>

                {selectedLead.proposal && (
                  <div>
                    <div className="text-gray-500 text-xs mb-1">Предложение</div>
                    <div className="bg-gray-50 rounded p-3 text-sm whitespace-pre-wrap">{selectedLead.proposal}</div>
                  </div>
                )}

                {selectedLead.file_path && (
                  <div>
                    <div className="text-gray-500 text-xs mb-1">Файл</div>
                    <a
                      href={selectedLead.file_path}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 rounded hover:bg-gray-200 transition-colors"
                    >
                      <svg className="w-4 h-4 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <span className="text-sm">{selectedLead.file_name}</span>
                    </a>
                  </div>
                )}
              </div>
              <div className="p-4 border-t flex justify-end gap-2">
                <button
                  onClick={() => setSelectedLead(null)}
                  className="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  Закрыть
                </button>
                <button
                  onClick={() => {
                    handleDelete(selectedLead.id);
                  }}
                  disabled={deleting}
                  className="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors disabled:opacity-50"
                >
                  Удалить
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </AdminPageWrapper>
  );
}
