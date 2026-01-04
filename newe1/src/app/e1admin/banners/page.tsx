'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Banner {
  id: number;
  name: string;
  desktop_image: string;
  mobile_image: string;
  link: string | null;
  sort_order: number;
  is_active: boolean;
  created_at: string;
}

interface Counts {
  active: number;
  inactive: number;
  total: number;
}

export default function BannersPage() {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [counts, setCounts] = useState<Counts>({ active: 0, inactive: 0, total: 0 });
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingBanner, setEditingBanner] = useState<Banner | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formName, setFormName] = useState('');
  const [formDesktopImage, setFormDesktopImage] = useState('');
  const [formMobileImage, setFormMobileImage] = useState('');
  const [formLink, setFormLink] = useState('');
  const [formIsActive, setFormIsActive] = useState(true);

  // Upload states
  const [uploadingDesktop, setUploadingDesktop] = useState(false);
  const [uploadingMobile, setUploadingMobile] = useState(false);
  const desktopInputRef = useRef<HTMLInputElement>(null);
  const mobileInputRef = useRef<HTMLInputElement>(null);

  // Drag and drop
  const [draggedId, setDraggedId] = useState<number | null>(null);

  const fetchBanners = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/banners');
      const data = await response.json();
      if (data.success) {
        setBanners(data.data);
        setCounts(data.counts);
      }
    } catch (error) {
      console.error('Error fetching banners:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchBanners();
  }, [fetchBanners]);

  const openCreateModal = () => {
    setEditingBanner(null);
    setFormName('');
    setFormDesktopImage('');
    setFormMobileImage('');
    setFormLink('');
    setFormIsActive(true);
    setModalOpen(true);
  };

  const openEditModal = (banner: Banner) => {
    setEditingBanner(banner);
    setFormName(banner.name);
    setFormDesktopImage(banner.desktop_image);
    setFormMobileImage(banner.mobile_image);
    setFormLink(banner.link || '');
    setFormIsActive(banner.is_active);
    setModalOpen(true);
  };

  const uploadImage = async (file: File, type: 'desktop' | 'mobile') => {
    const setUploading = type === 'desktop' ? setUploadingDesktop : setUploadingMobile;
    const setImage = type === 'desktop' ? setFormDesktopImage : setFormMobileImage;

    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('image', file);
      formData.append('type', type);

      const response = await fetch('/api/e1admin/banners/upload', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        setImage(data.path);
      } else {
        alert(data.message || 'Ошибка загрузки');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setUploading(false);
    }
  };

  const handleDesktopUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      uploadImage(file, 'desktop');
    }
    if (desktopInputRef.current) desktopInputRef.current.value = '';
  };

  const handleMobileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      uploadImage(file, 'mobile');
    }
    if (mobileInputRef.current) mobileInputRef.current.value = '';
  };

  const saveBanner = async () => {
    if (!formName.trim() || !formDesktopImage || !formMobileImage) {
      alert('Заполните название и загрузите оба изображения');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        name: formName,
        desktop_image: formDesktopImage,
        mobile_image: formMobileImage,
        link: formLink || null,
        is_active: formIsActive,
      };

      let response;
      if (editingBanner) {
        response = await fetch(`/api/e1admin/banners/${editingBanner.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/banners', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchBanners();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (banner: Banner) => {
    setActionLoading(banner.id);
    try {
      const response = await fetch(`/api/e1admin/banners/${banner.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !banner.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchBanners();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteBanner = async (banner: Banner) => {
    if (!confirm(`Удалить баннер "${banner.name}"? Это действие нельзя отменить.`)) return;

    setActionLoading(banner.id);
    try {
      const response = await fetch(`/api/e1admin/banners/${banner.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchBanners();
      } else {
        alert(data.error || 'Ошибка удаления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  // Drag and drop handlers
  const handleDragStart = (e: React.DragEvent, id: number) => {
    setDraggedId(id);
    e.dataTransfer.effectAllowed = 'move';
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleDrop = async (e: React.DragEvent, targetId: number) => {
    e.preventDefault();
    if (draggedId === null || draggedId === targetId) return;

    const draggedIndex = banners.findIndex(b => b.id === draggedId);
    const targetIndex = banners.findIndex(b => b.id === targetId);

    if (draggedIndex === -1 || targetIndex === -1) return;

    // Reorder locally
    const newBanners = [...banners];
    const [removed] = newBanners.splice(draggedIndex, 1);
    newBanners.splice(targetIndex, 0, removed);

    // Update sort_order
    const order = newBanners.map((b, index) => ({
      id: b.id,
      sort_order: index,
    }));

    setBanners(newBanners);
    setDraggedId(null);

    // Save to server
    try {
      await fetch('/api/e1admin/banners', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order }),
      });
    } catch {
      // Revert on error
      fetchBanners();
    }
  };

  const handleDragEnd = () => {
    setDraggedId(null);
  };

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Баннеры</h1>
            <p className="text-gray-600 mt-1">Управление баннерами на главной странице</p>
          </div>
          <button
            onClick={openCreateModal}
            className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Добавить баннер
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

        {/* Info */}
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <p className="text-sm text-blue-800">
            <strong>Подсказка:</strong> Перетаскивайте баннеры для изменения порядка отображения
          </p>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : banners.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Нет баннеров</h3>
            <p className="text-gray-500 mb-4">Добавьте первый баннер для главной страницы</p>
            <button
              onClick={openCreateModal}
              className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Добавить баннер
            </button>
          </div>
        ) : (
          <div className="space-y-3">
            {banners.map((banner, index) => (
              <div
                key={banner.id}
                draggable
                onDragStart={(e) => handleDragStart(e, banner.id)}
                onDragOver={handleDragOver}
                onDrop={(e) => handleDrop(e, banner.id)}
                onDragEnd={handleDragEnd}
                className={`bg-white rounded-xl border p-4 cursor-move transition-all ${
                  draggedId === banner.id ? 'opacity-50 scale-[0.98]' : ''
                } ${banner.is_active ? 'border-gray-200' : 'border-gray-300 bg-gray-50/50'}`}
              >
                <div className="flex items-center gap-4">
                  {/* Drag handle & order */}
                  <div className="flex items-center gap-2 text-gray-400">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                    </svg>
                    <span className="text-sm font-medium w-6 text-center">{index + 1}</span>
                  </div>

                  {/* Preview images */}
                  <div className="flex gap-2">
                    <div className="w-24 h-14 bg-gray-100 rounded overflow-hidden" title="Desktop">
                      <img
                        src={banner.desktop_image}
                        alt="Desktop"
                        className="w-full h-full object-cover"
                      />
                    </div>
                    <div className="w-10 h-14 bg-gray-100 rounded overflow-hidden" title="Mobile">
                      <img
                        src={banner.mobile_image}
                        alt="Mobile"
                        className="w-full h-full object-cover"
                      />
                    </div>
                  </div>

                  {/* Info */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <h3 className="font-medium text-gray-900 truncate">{banner.name}</h3>
                      {!banner.is_active && (
                        <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                          Скрыт
                        </span>
                      )}
                    </div>
                    {banner.link && (
                      <p className="text-sm text-gray-500 truncate">{banner.link}</p>
                    )}
                  </div>

                  {/* Actions */}
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => toggleActive(banner)}
                      disabled={actionLoading === banner.id}
                      className={`p-2 rounded-lg transition-colors disabled:opacity-50 ${
                        banner.is_active
                          ? 'text-green-600 hover:bg-green-50'
                          : 'text-gray-400 hover:bg-gray-100'
                      }`}
                      title={banner.is_active ? 'Скрыть' : 'Показать'}
                    >
                      {banner.is_active ? (
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

                    <button
                      onClick={() => openEditModal(banner)}
                      className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                      title="Редактировать"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>

                    <button
                      onClick={() => deleteBanner(banner)}
                      disabled={actionLoading === banner.id}
                      className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
                      title="Удалить"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
          <div className="bg-white rounded-xl max-w-2xl w-full my-8">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingBanner ? 'Редактирование баннера' : 'Новый баннер'}
              </h2>
            </div>

            <div className="p-6 space-y-6 max-h-[60vh] overflow-y-auto">
              {/* Name */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Название (для админки) <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="Например: Новогодняя акция"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* Desktop Image */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Изображение для десктопа <span className="text-red-500">*</span>
                </label>
                <p className="text-xs text-gray-500 mb-2">Рекомендуемый размер: 1920×500 px (на всю ширину экрана)</p>
                <input
                  ref={desktopInputRef}
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif"
                  onChange={handleDesktopUpload}
                  className="hidden"
                />
                {formDesktopImage ? (
                  <div className="relative">
                    <img
                      src={formDesktopImage}
                      alt="Desktop preview"
                      className="w-full h-40 object-cover rounded-lg border border-gray-200"
                    />
                    <button
                      type="button"
                      onClick={() => desktopInputRef.current?.click()}
                      disabled={uploadingDesktop}
                      className="absolute bottom-2 right-2 px-3 py-1 bg-white/90 hover:bg-white text-sm rounded shadow transition-colors"
                    >
                      {uploadingDesktop ? 'Загрузка...' : 'Заменить'}
                    </button>
                  </div>
                ) : (
                  <button
                    type="button"
                    onClick={() => desktopInputRef.current?.click()}
                    disabled={uploadingDesktop}
                    className="w-full h-40 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-500 hover:border-[#7cb342] hover:text-[#7cb342] transition-colors disabled:opacity-50"
                  >
                    {uploadingDesktop ? (
                      <div className="w-6 h-6 border-2 border-current border-t-transparent rounded-full animate-spin" />
                    ) : (
                      <>
                        <svg className="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Загрузить изображение</span>
                      </>
                    )}
                  </button>
                )}
              </div>

              {/* Mobile Image */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Изображение для мобильных <span className="text-red-500">*</span>
                </label>
                <p className="text-xs text-gray-500 mb-2">Рекомендуемый размер: 600×600 px (1:1)</p>
                <input
                  ref={mobileInputRef}
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif"
                  onChange={handleMobileUpload}
                  className="hidden"
                />
                {formMobileImage ? (
                  <div className="relative w-48">
                    <img
                      src={formMobileImage}
                      alt="Mobile preview"
                      className="w-full h-60 object-cover rounded-lg border border-gray-200"
                    />
                    <button
                      type="button"
                      onClick={() => mobileInputRef.current?.click()}
                      disabled={uploadingMobile}
                      className="absolute bottom-2 right-2 px-3 py-1 bg-white/90 hover:bg-white text-sm rounded shadow transition-colors"
                    >
                      {uploadingMobile ? 'Загрузка...' : 'Заменить'}
                    </button>
                  </div>
                ) : (
                  <button
                    type="button"
                    onClick={() => mobileInputRef.current?.click()}
                    disabled={uploadingMobile}
                    className="w-48 h-60 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-500 hover:border-[#7cb342] hover:text-[#7cb342] transition-colors disabled:opacity-50"
                  >
                    {uploadingMobile ? (
                      <div className="w-6 h-6 border-2 border-current border-t-transparent rounded-full animate-spin" />
                    ) : (
                      <>
                        <svg className="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span className="text-sm">Загрузить</span>
                      </>
                    )}
                  </button>
                )}
              </div>

              {/* Link */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Ссылка при клике (необязательно)
                </label>
                <input
                  type="text"
                  value={formLink}
                  onChange={(e) => setFormLink(e.target.value)}
                  placeholder="https://..."
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
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
                  {formIsActive ? 'Баннер виден на сайте' : 'Баннер скрыт'}
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
                onClick={saveBanner}
                disabled={saving || !formDesktopImage || !formMobileImage}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingBanner ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
