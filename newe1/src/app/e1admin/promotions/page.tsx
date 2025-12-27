'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Promotion {
  id: number;
  title: string;
  slug: string;
  content: string;
  images: string[];
  start_date: string;
  end_date: string;
  published_at: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

interface Counts {
  active: number;
  expired: number;
  inactive: number;
  total: number;
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}

function formatDateForInput(dateString: string): string {
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
}

function getStatusInfo(promotion: Promotion): { label: string; color: string } {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const endDate = new Date(promotion.end_date);
  endDate.setHours(0, 0, 0, 0);

  if (!promotion.is_active) {
    return { label: 'Неактивна', color: 'bg-gray-100 text-gray-700' };
  }
  if (endDate < today) {
    return { label: 'Истекла', color: 'bg-red-100 text-red-700' };
  }
  return { label: 'Активна', color: 'bg-green-100 text-green-700' };
}

export default function PromotionsPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const statusFilter = searchParams.get('status') || 'all';

  const [promotions, setPromotions] = useState<Promotion[]>([]);
  const [counts, setCounts] = useState<Counts>({ active: 0, expired: 0, inactive: 0, total: 0 });
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingPromotion, setEditingPromotion] = useState<Promotion | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formTitle, setFormTitle] = useState('');
  const [formContent, setFormContent] = useState('');
  const [formImages, setFormImages] = useState<string[]>([]);
  const [formNewImage, setFormNewImage] = useState('');
  const [formStartDate, setFormStartDate] = useState('');
  const [formEndDate, setFormEndDate] = useState('');
  const [formPublishedAt, setFormPublishedAt] = useState('');
  const [formIsActive, setFormIsActive] = useState(true);
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const fetchPromotions = useCallback(async () => {
    try {
      const url = `/api/e1admin/promotions?status=${statusFilter}`;
      const response = await fetch(url);
      const data = await response.json();
      if (data.success) {
        setPromotions(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching promotions:', error);
    } finally {
      setLoading(false);
    }
  }, [statusFilter]);

  useEffect(() => {
    setLoading(true);
    fetchPromotions();
  }, [fetchPromotions]);

  const setStatus = (status: string) => {
    if (status === 'all') {
      router.push('/e1admin/promotions');
    } else {
      router.push(`/e1admin/promotions?status=${status}`);
    }
  };

  const openCreateModal = () => {
    setEditingPromotion(null);
    setFormTitle('');
    setFormContent('');
    setFormImages([]);
    setFormNewImage('');
    const today = new Date().toISOString().split('T')[0];
    setFormStartDate(today);
    setFormEndDate('');
    setFormPublishedAt('');
    setFormIsActive(true);
    setModalOpen(true);
  };

  const openEditModal = (promotion: Promotion) => {
    setEditingPromotion(promotion);
    setFormTitle(promotion.title);
    setFormContent(promotion.content);
    setFormImages(promotion.images || []);
    setFormNewImage('');
    setFormStartDate(formatDateForInput(promotion.start_date));
    setFormEndDate(formatDateForInput(promotion.end_date));
    setFormPublishedAt(promotion.published_at ? formatDateForInput(promotion.published_at) : '');
    setFormIsActive(promotion.is_active);
    setModalOpen(true);
  };

  const addImage = () => {
    if (formNewImage.trim()) {
      setFormImages([...formImages, formNewImage.trim()]);
      setFormNewImage('');
    }
  };

  const removeImage = (index: number) => {
    setFormImages(formImages.filter((_, i) => i !== index));
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    setUploading(true);
    try {
      const formData = new FormData();
      Array.from(files).forEach((file) => {
        formData.append('images', file);
      });

      const response = await fetch('/api/e1admin/promotions/upload', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        setFormImages([...formImages, ...data.paths]);
      } else {
        alert(data.message || 'Ошибка загрузки');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setUploading(false);
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    }
  };

  const savePromotion = async () => {
    if (!formTitle.trim() || !formContent.trim() || !formStartDate || !formEndDate) {
      alert('Заполните все обязательные поля');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        title: formTitle,
        content: formContent,
        images: formImages,
        start_date: formStartDate,
        end_date: formEndDate,
        published_at: formPublishedAt || null,
        is_active: formIsActive,
      };

      let response;
      if (editingPromotion) {
        response = await fetch(`/api/e1admin/promotions/${editingPromotion.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/promotions', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchPromotions();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (promotion: Promotion) => {
    setActionLoading(promotion.id);
    try {
      const response = await fetch(`/api/e1admin/promotions/${promotion.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !promotion.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchPromotions();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deletePromotion = async (promotion: Promotion) => {
    if (!confirm(`Удалить акцию "${promotion.title}"? Это действие нельзя отменить.`)) return;

    setActionLoading(promotion.id);
    try {
      const response = await fetch(`/api/e1admin/promotions/${promotion.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchPromotions();
      } else {
        alert(data.error || 'Ошибка удаления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const getCardClass = (promotion: Promotion) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const endDate = new Date(promotion.end_date);
    endDate.setHours(0, 0, 0, 0);

    if (!promotion.is_active) {
      return 'border-gray-300 bg-gray-50/30';
    }
    if (endDate < today) {
      return 'border-red-300 bg-red-50/30';
    }
    return 'border-gray-200';
  };

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Акции</h1>
            <p className="text-gray-600 mt-1">Управление акциями и спецпредложениями</p>
          </div>
          <button
            onClick={openCreateModal}
            className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Создать акцию
          </button>
        </div>

        {/* Filters */}
        <div className="flex flex-wrap gap-2 mb-6">
          <button
            onClick={() => setStatus('all')}
            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
              statusFilter === 'all'
                ? 'bg-[#7cb342] text-white'
                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
            }`}
          >
            Все ({counts.total})
          </button>
          <button
            onClick={() => setStatus('active')}
            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
              statusFilter === 'active'
                ? 'bg-green-500 text-white'
                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
            }`}
          >
            Активные ({counts.active})
          </button>
          <button
            onClick={() => setStatus('expired')}
            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
              statusFilter === 'expired'
                ? 'bg-red-500 text-white'
                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
            }`}
          >
            Истекшие ({counts.expired})
          </button>
          <button
            onClick={() => setStatus('inactive')}
            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
              statusFilter === 'inactive'
                ? 'bg-gray-500 text-white'
                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
            }`}
          >
            Неактивные ({counts.inactive})
          </button>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : promotions.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Нет акций</h3>
            <p className="text-gray-500 mb-4">
              {statusFilter === 'active'
                ? 'Нет активных акций'
                : statusFilter === 'expired'
                  ? 'Нет истекших акций'
                  : statusFilter === 'inactive'
                    ? 'Нет неактивных акций'
                    : 'Создайте первую акцию'}
            </p>
            {statusFilter === 'all' && (
              <button
                onClick={openCreateModal}
                className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
                Создать акцию
              </button>
            )}
          </div>
        ) : (
          <div className="space-y-4">
            {promotions.map((promotion) => {
              const statusInfo = getStatusInfo(promotion);
              return (
                <div
                  key={promotion.id}
                  className={`bg-white rounded-xl border p-6 ${getCardClass(promotion)}`}
                >
                  <div className="flex flex-col lg:flex-row lg:items-start gap-4">
                    {/* Main content */}
                    <div className="flex-1 min-w-0">
                      {/* Header */}
                      <div className="flex flex-wrap items-center gap-3 mb-3">
                        <h3 className="font-semibold text-lg text-gray-900">{promotion.title}</h3>
                        <span className={`px-2 py-0.5 text-xs font-medium rounded-full ${statusInfo.color}`}>
                          {statusInfo.label}
                        </span>
                      </div>

                      {/* Dates */}
                      <div className="flex flex-wrap gap-4 text-sm text-gray-500 mb-3">
                        <span className="flex items-center gap-1">
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                          {formatDate(promotion.start_date)} — {formatDate(promotion.end_date)}
                        </span>
                        {promotion.published_at && (
                          <span className="flex items-center gap-1">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Опубликовано: {formatDate(promotion.published_at)}
                          </span>
                        )}
                      </div>

                      {/* Content preview */}
                      <p className="text-gray-700 mb-3 line-clamp-3">{promotion.content}</p>

                      {/* Images */}
                      {promotion.images && promotion.images.length > 0 && (
                        <div className="flex gap-2 flex-wrap">
                          {promotion.images.map((image, index) => (
                            <a
                              key={index}
                              href={image}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden hover:opacity-75 transition-opacity"
                            >
                              <img
                                src={image}
                                alt={`Фото ${index + 1}`}
                                className="w-full h-full object-cover"
                              />
                            </a>
                          ))}
                        </div>
                      )}
                    </div>

                    {/* Actions */}
                    <div className="flex lg:flex-col gap-2 flex-wrap">
                      <button
                        onClick={() => toggleActive(promotion)}
                        disabled={actionLoading === promotion.id}
                        className={`flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 ${
                          promotion.is_active
                            ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            : 'bg-green-500 hover:bg-green-600 text-white'
                        }`}
                      >
                        {actionLoading === promotion.id ? (
                          <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                          </svg>
                        ) : promotion.is_active ? (
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                          </svg>
                        ) : (
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                          </svg>
                        )}
                        {promotion.is_active ? 'Деактивировать' : 'Активировать'}
                      </button>

                      <button
                        onClick={() => openEditModal(promotion)}
                        className="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Редактировать
                      </button>

                      <button
                        onClick={() => deletePromotion(promotion)}
                        disabled={actionLoading === promotion.id}
                        className="flex items-center gap-2 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Удалить
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
          <div className="bg-white rounded-xl max-w-2xl w-full my-8">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingPromotion ? 'Редактирование акции' : 'Создание акции'}
              </h2>
            </div>

            <div className="p-6 space-y-6 max-h-[60vh] overflow-y-auto">
              {/* Title */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Заголовок <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formTitle}
                  onChange={(e) => setFormTitle(e.target.value)}
                  placeholder="Название акции"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* Content */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Описание <span className="text-red-500">*</span>
                </label>
                <textarea
                  value={formContent}
                  onChange={(e) => setFormContent(e.target.value)}
                  rows={5}
                  placeholder="Описание акции..."
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none resize-none"
                />
              </div>

              {/* Images */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Изображения
                </label>

                {/* File upload */}
                <div className="mb-3">
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    multiple
                    onChange={handleFileUpload}
                    className="hidden"
                  />
                  <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    disabled={uploading}
                    className="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg hover:border-[#7cb342] hover:bg-gray-50 text-gray-600 transition-colors disabled:opacity-50"
                  >
                    {uploading ? (
                      <>
                        <svg className="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>Загрузка...</span>
                      </>
                    ) : (
                      <>
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Загрузить изображения</span>
                      </>
                    )}
                  </button>
                  <p className="mt-1 text-xs text-gray-500">JPG, PNG, WebP или GIF, до 10MB</p>
                </div>

                {/* URL input */}
                <div className="flex gap-2 mb-3">
                  <input
                    type="text"
                    value={formNewImage}
                    onChange={(e) => setFormNewImage(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), addImage())}
                    placeholder="или вставьте URL изображения"
                    className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none text-sm"
                  />
                  <button
                    type="button"
                    onClick={addImage}
                    className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm"
                  >
                    Добавить
                  </button>
                </div>

                {/* Image previews */}
                {formImages.length > 0 && (
                  <div className="flex gap-2 flex-wrap">
                    {formImages.map((image, index) => (
                      <div key={index} className="relative group">
                        <div className="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden">
                          <img
                            src={image}
                            alt={`Изображение ${index + 1}`}
                            className="w-full h-full object-cover"
                            onError={(e) => {
                              (e.target as HTMLImageElement).src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%239CA3AF"><path d="M4 5h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V7a2 2 0 012-2zm10 4a2 2 0 100 4 2 2 0 000-4zM4 17l4-4 2 2 4-4 6 6H4z"/></svg>';
                            }}
                          />
                        </div>
                        <button
                          type="button"
                          onClick={() => removeImage(index)}
                          className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                        >
                          ×
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Dates */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Дата начала <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="date"
                    value={formStartDate}
                    onChange={(e) => setFormStartDate(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Дата окончания <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="date"
                    value={formEndDate}
                    onChange={(e) => setFormEndDate(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              {/* Published at */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Дата публикации (отображается на сайте)
                </label>
                <input
                  type="date"
                  value={formPublishedAt}
                  onChange={(e) => setFormPublishedAt(e.target.value)}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
                <p className="mt-1 text-sm text-gray-500">
                  Если не указано, акция не будет иметь даты публикации
                </p>
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
                  {formIsActive ? 'Акция активна' : 'Акция неактивна'}
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
                onClick={savePromotion}
                disabled={saving}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingPromotion ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
