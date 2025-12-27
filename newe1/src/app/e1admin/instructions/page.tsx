'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface Instruction {
  id: number;
  name: string;
  pdf_path: string;
  video_url: string | null;
  sort_order: number;
  is_active: boolean;
  created_at: string;
}

export default function InstructionsPage() {
  const [instructions, setInstructions] = useState<Instruction[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingInstruction, setEditingInstruction] = useState<Instruction | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formName, setFormName] = useState('');
  const [formPdfPath, setFormPdfPath] = useState('');
  const [formVideoUrl, setFormVideoUrl] = useState('');
  const [formIsActive, setFormIsActive] = useState(true);

  // Upload states
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Drag and drop
  const [draggedId, setDraggedId] = useState<number | null>(null);

  const fetchInstructions = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/instructions');
      const data = await response.json();
      if (data.success) {
        setInstructions(data.data);
      }
    } catch (error) {
      console.error('Error fetching instructions:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchInstructions();
  }, [fetchInstructions]);

  const openCreateModal = () => {
    setEditingInstruction(null);
    setFormName('');
    setFormPdfPath('');
    setFormVideoUrl('');
    setFormIsActive(true);
    setModalOpen(true);
  };

  const openEditModal = (instruction: Instruction) => {
    setEditingInstruction(instruction);
    setFormName(instruction.name);
    setFormPdfPath(instruction.pdf_path);
    setFormVideoUrl(instruction.video_url || '');
    setFormIsActive(instruction.is_active);
    setModalOpen(true);
  };

  const uploadPdf = async (file: File) => {
    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await fetch('/api/e1admin/instructions/upload', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        setFormPdfPath(data.path);
      } else {
        alert(data.message || 'Ошибка загрузки');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setUploading(false);
    }
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      uploadPdf(file);
    }
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const saveInstruction = async () => {
    if (!formName.trim() || !formPdfPath) {
      alert('Заполните название и загрузите PDF-файл');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        name: formName,
        pdf_path: formPdfPath,
        video_url: formVideoUrl || null,
        is_active: formIsActive,
      };

      let response;
      if (editingInstruction) {
        response = await fetch(`/api/e1admin/instructions/${editingInstruction.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/instructions', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchInstructions();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (instruction: Instruction) => {
    setActionLoading(instruction.id);
    try {
      const response = await fetch(`/api/e1admin/instructions/${instruction.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !instruction.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchInstructions();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteInstruction = async (instruction: Instruction) => {
    if (!confirm(`Удалить инструкцию "${instruction.name}"? Это действие нельзя отменить.`)) return;

    setActionLoading(instruction.id);
    try {
      const response = await fetch(`/api/e1admin/instructions/${instruction.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchInstructions();
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

    const draggedIndex = instructions.findIndex(i => i.id === draggedId);
    const targetIndex = instructions.findIndex(i => i.id === targetId);

    if (draggedIndex === -1 || targetIndex === -1) return;

    // Reorder locally
    const newInstructions = [...instructions];
    const [removed] = newInstructions.splice(draggedIndex, 1);
    newInstructions.splice(targetIndex, 0, removed);

    // Update sort_order
    const order = newInstructions.map((i, index) => ({
      id: i.id,
      sort_order: index,
    }));

    setInstructions(newInstructions);
    setDraggedId(null);

    // Save to server
    try {
      await fetch('/api/e1admin/instructions', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order }),
      });
    } catch {
      // Revert on error
      fetchInstructions();
    }
  };

  const handleDragEnd = () => {
    setDraggedId(null);
  };

  const activeCount = instructions.filter(i => i.is_active).length;
  const inactiveCount = instructions.filter(i => !i.is_active).length;

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Инструкции по сборке</h1>
            <p className="text-gray-600 mt-1">Управление PDF-инструкциями и видео</p>
          </div>
          <button
            onClick={openCreateModal}
            className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Добавить инструкцию
          </button>
        </div>

        {/* Stats */}
        <div className="flex gap-4 mb-6 text-sm">
          <span className="text-gray-600">
            Всего: <strong>{instructions.length}</strong>
          </span>
          <span className="text-green-600">
            Активных: <strong>{activeCount}</strong>
          </span>
          <span className="text-gray-500">
            Скрытых: <strong>{inactiveCount}</strong>
          </span>
        </div>

        {/* Info */}
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <p className="text-sm text-blue-800">
            <strong>Подсказка:</strong> Перетаскивайте инструкции для изменения порядка отображения
          </p>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : instructions.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Нет инструкций</h3>
            <p className="text-gray-500 mb-4">Добавьте первую инструкцию по сборке мебели</p>
            <button
              onClick={openCreateModal}
              className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Добавить инструкцию
            </button>
          </div>
        ) : (
          <div className="space-y-3">
            {instructions.map((instruction, index) => (
              <div
                key={instruction.id}
                draggable
                onDragStart={(e) => handleDragStart(e, instruction.id)}
                onDragOver={handleDragOver}
                onDrop={(e) => handleDrop(e, instruction.id)}
                onDragEnd={handleDragEnd}
                className={`bg-white rounded-xl border p-4 cursor-move transition-all ${
                  draggedId === instruction.id ? 'opacity-50 scale-[0.98]' : ''
                } ${instruction.is_active ? 'border-gray-200' : 'border-gray-300 bg-gray-50/50'}`}
              >
                <div className="flex items-center gap-4">
                  {/* Drag handle & order */}
                  <div className="flex items-center gap-2 text-gray-400">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                    </svg>
                    <span className="text-sm font-medium w-6 text-center">{index + 1}</span>
                  </div>

                  {/* PDF icon */}
                  <div className="w-10 h-12 bg-red-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg className="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                      <path d="M14 2v6h6"/>
                      <path fill="none" stroke="currentColor" strokeWidth="1.5" d="M9 13v5m0-2.5h1.5a1.25 1.25 0 0 0 0-2.5H9m5 2.5h1.5a1.25 1.25 0 0 0 0-2.5H14v5"/>
                    </svg>
                  </div>

                  {/* Info */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <h3 className="font-medium text-gray-900 truncate">{instruction.name}</h3>
                      {!instruction.is_active && (
                        <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                          Скрыта
                        </span>
                      )}
                    </div>
                    <div className="flex items-center gap-3 mt-1">
                      <a
                        href={instruction.pdf_path}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-sm text-[#7cb342] hover:underline"
                      >
                        Открыть PDF
                      </a>
                      {instruction.video_url && (
                        <a
                          href={instruction.video_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-sm text-blue-600 hover:underline flex items-center gap-1"
                        >
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          Видео
                        </a>
                      )}
                    </div>
                  </div>

                  {/* Actions */}
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => toggleActive(instruction)}
                      disabled={actionLoading === instruction.id}
                      className={`p-2 rounded-lg transition-colors disabled:opacity-50 ${
                        instruction.is_active
                          ? 'text-green-600 hover:bg-green-50'
                          : 'text-gray-400 hover:bg-gray-100'
                      }`}
                      title={instruction.is_active ? 'Скрыть' : 'Показать'}
                    >
                      {instruction.is_active ? (
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
                      onClick={() => openEditModal(instruction)}
                      className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                      title="Редактировать"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>

                    <button
                      onClick={() => deleteInstruction(instruction)}
                      disabled={actionLoading === instruction.id}
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
          <div className="bg-white rounded-xl max-w-lg w-full my-8">
            <div className="p-6 border-b border-gray-200">
              <h2 className="text-xl font-bold text-gray-900">
                {editingInstruction ? 'Редактирование инструкции' : 'Новая инструкция'}
              </h2>
            </div>

            <div className="p-6 space-y-6">
              {/* Name */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Наименование <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formName}
                  onChange={(e) => setFormName(e.target.value)}
                  placeholder="Например: Шкаф-купе ФЛЭШ"
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                />
              </div>

              {/* PDF Upload */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  PDF-файл инструкции <span className="text-red-500">*</span>
                </label>
                <input
                  ref={fileInputRef}
                  type="file"
                  accept="application/pdf"
                  onChange={handleFileUpload}
                  className="hidden"
                />
                {formPdfPath ? (
                  <div className="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div className="w-10 h-12 bg-red-100 rounded flex items-center justify-center flex-shrink-0">
                      <svg className="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                        <path d="M14 2v6h6"/>
                      </svg>
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900 truncate">
                        {formPdfPath.split('/').pop()}
                      </p>
                      <a
                        href={formPdfPath}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-xs text-[#7cb342] hover:underline"
                      >
                        Открыть файл
                      </a>
                    </div>
                    <button
                      type="button"
                      onClick={() => fileInputRef.current?.click()}
                      disabled={uploading}
                      className="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                    >
                      {uploading ? 'Загрузка...' : 'Заменить'}
                    </button>
                  </div>
                ) : (
                  <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    disabled={uploading}
                    className="w-full h-32 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-500 hover:border-[#7cb342] hover:text-[#7cb342] transition-colors disabled:opacity-50"
                  >
                    {uploading ? (
                      <div className="w-6 h-6 border-2 border-current border-t-transparent rounded-full animate-spin" />
                    ) : (
                      <>
                        <svg className="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>Загрузить PDF-файл</span>
                        <span className="text-xs text-gray-400 mt-1">Максимум 50 МБ</span>
                      </>
                    )}
                  </button>
                )}
              </div>

              {/* Video URL */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Ссылка на видеоинструкцию (необязательно)
                </label>
                <input
                  type="text"
                  value={formVideoUrl}
                  onChange={(e) => setFormVideoUrl(e.target.value)}
                  placeholder="https://youtube.com/watch?v=..."
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
                  {formIsActive ? 'Инструкция видна на сайте' : 'Инструкция скрыта'}
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
                onClick={saveInstruction}
                disabled={saving || !formPdfPath}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingInstruction ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}
