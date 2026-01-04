'use client';

import { useEffect, useState, useCallback, useRef, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface BlogArticle {
  id: number;
  slug: string;
  title: string;
  category: string;
  short_description: string | null;
  content: string;
  cover_image: string | null;
  published_at: string;
  author_name: string;
  is_active: boolean;
  meta_title: string | null;
  meta_description: string | null;
  created_at: string;
  updated_at: string;
}

interface Counts {
  active: number;
  inactive: number;
  total: number;
}

interface Category {
  category: string;
  count: number;
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function formatDateForInput(dateString: string): string {
  const date = new Date(dateString);
  return date.toISOString().split('T')[0];
}

function BlogPageContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const statusFilter = searchParams?.get('status') || 'all';

  const [articles, setArticles] = useState<BlogArticle[]>([]);
  const [counts, setCounts] = useState<Counts>({ active: 0, inactive: 0, total: 0 });
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Create/Edit modal
  const [modalOpen, setModalOpen] = useState(false);
  const [editingArticle, setEditingArticle] = useState<BlogArticle | null>(null);
  const [saving, setSaving] = useState(false);

  // Form fields
  const [formSlug, setFormSlug] = useState('');
  const [formTitle, setFormTitle] = useState('');
  const [formCategory, setFormCategory] = useState('');
  const [formShortDesc, setFormShortDesc] = useState('');
  const [formContent, setFormContent] = useState('');
  const [formCoverImage, setFormCoverImage] = useState('');
  const [formPublishedAt, setFormPublishedAt] = useState('');
  const [formAuthor, setFormAuthor] = useState('');
  const [formIsActive, setFormIsActive] = useState(true);
  const [formMetaTitle, setFormMetaTitle] = useState('');
  const [formMetaDesc, setFormMetaDesc] = useState('');

  // File upload
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const contentFileInputRef = useRef<HTMLInputElement>(null);
  const editorRef = useRef<HTMLDivElement>(null);

  const fetchArticles = useCallback(async () => {
    try {
      const url = `/api/e1admin/blog?status=${statusFilter}`;
      const response = await fetch(url);
      const data = await response.json();
      if (data.success) {
        setArticles(data.data);
        setCounts(data.counts);
        setCategories(data.categories);
      }
    } catch (error) {
      console.error('Error fetching articles:', error);
    } finally {
      setLoading(false);
    }
  }, [statusFilter]);

  useEffect(() => {
    setLoading(true);
    fetchArticles();
  }, [fetchArticles]);

  const setStatus = (status: string) => {
    if (status === 'all') {
      router.push('/e1admin/blog');
    } else {
      router.push(`/e1admin/blog?status=${status}`);
    }
  };

  const generateSlug = (title: string): string => {
    const translitMap: { [key: string]: string } = {
      'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
      'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
      'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
      'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'shch',
      'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
    };
    return title
      .toLowerCase()
      .split('')
      .map(char => translitMap[char] || char)
      .join('')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .substring(0, 100);
  };

  const openCreateModal = () => {
    setEditingArticle(null);
    setFormSlug('');
    setFormTitle('');
    setFormCategory('');
    setFormShortDesc('');
    setFormContent('');
    setFormCoverImage('');
    setFormPublishedAt(new Date().toISOString().split('T')[0]);
    setFormAuthor('');
    setFormIsActive(true);
    setFormMetaTitle('');
    setFormMetaDesc('');
    setModalOpen(true);
  };

  const openEditModal = (article: BlogArticle) => {
    setEditingArticle(article);
    setFormSlug(article.slug);
    setFormTitle(article.title);
    setFormCategory(article.category);
    setFormShortDesc(article.short_description || '');
    setFormContent(article.content);
    setFormCoverImage(article.cover_image || '');
    setFormPublishedAt(formatDateForInput(article.published_at));
    setFormAuthor(article.author_name);
    setFormIsActive(article.is_active);
    setFormMetaTitle(article.meta_title || '');
    setFormMetaDesc(article.meta_description || '');
    setModalOpen(true);
  };

  const handleTitleChange = (title: string) => {
    setFormTitle(title);
    if (!editingArticle && !formSlug) {
      setFormSlug(generateSlug(title));
    }
  };

  const handleCoverUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    setUploading(true);
    try {
      const formData = new FormData();
      formData.append('images', files[0]);

      const response = await fetch('/api/e1admin/blog/upload', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success && data.paths.length > 0) {
        setFormCoverImage(data.paths[0]);
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

  const handleContentImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    setUploading(true);
    try {
      const formData = new FormData();
      Array.from(files).forEach((file) => {
        formData.append('images', file);
      });

      const response = await fetch('/api/e1admin/blog/upload', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        // Insert images at cursor position in content
        const imagesHtml = data.paths
          .map((path: string) => `<img src="${path}" alt="" style="max-width: 100%; height: auto; margin: 16px 0;" />`)
          .join('\n');

        if (editorRef.current) {
          // Insert at current selection
          const selection = window.getSelection();
          if (selection && selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const fragment = range.createContextualFragment(imagesHtml);
            range.insertNode(fragment);
          } else {
            editorRef.current.innerHTML += imagesHtml;
          }
          setFormContent(editorRef.current.innerHTML);
        }
      } else {
        alert(data.message || 'Ошибка загрузки');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setUploading(false);
      if (contentFileInputRef.current) {
        contentFileInputRef.current.value = '';
      }
    }
  };

  const execCommand = (command: string, value?: string) => {
    document.execCommand(command, false, value);
    editorRef.current?.focus();
    if (editorRef.current) {
      setFormContent(editorRef.current.innerHTML);
    }
  };

  const saveArticle = async () => {
    if (!formSlug.trim() || !formTitle.trim() || !formCategory.trim() || !formContent.trim() || !formAuthor.trim()) {
      alert('Заполните все обязательные поля');
      return;
    }

    setSaving(true);
    try {
      const payload = {
        slug: formSlug,
        title: formTitle,
        category: formCategory,
        short_description: formShortDesc || null,
        content: formContent,
        cover_image: formCoverImage || null,
        published_at: formPublishedAt,
        author_name: formAuthor,
        is_active: formIsActive,
        meta_title: formMetaTitle || formTitle,
        meta_description: formMetaDesc || formShortDesc || null,
      };

      let response;
      if (editingArticle) {
        response = await fetch(`/api/e1admin/blog/${editingArticle.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      } else {
        response = await fetch('/api/e1admin/blog', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
      }

      const data = await response.json();
      if (data.success) {
        setModalOpen(false);
        fetchArticles();
      } else {
        alert(data.error || 'Ошибка сохранения');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setSaving(false);
    }
  };

  const toggleActive = async (article: BlogArticle) => {
    setActionLoading(article.id);
    try {
      const response = await fetch(`/api/e1admin/blog/${article.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !article.is_active }),
      });
      const data = await response.json();
      if (data.success) {
        fetchArticles();
      } else {
        alert(data.error || 'Ошибка обновления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  const deleteArticle = async (article: BlogArticle) => {
    if (!confirm(`Удалить статью "${article.title}"? Это действие нельзя отменить.`)) return;

    setActionLoading(article.id);
    try {
      const response = await fetch(`/api/e1admin/blog/${article.id}`, {
        method: 'DELETE',
      });
      const data = await response.json();
      if (data.success) {
        fetchArticles();
      } else {
        alert(data.error || 'Ошибка удаления');
      }
    } catch {
      alert('Ошибка подключения к серверу');
    } finally {
      setActionLoading(null);
    }
  };

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Блог</h1>
            <p className="text-gray-600 mt-1">Управление статьями блога для SEO</p>
          </div>
          <button
            onClick={openCreateModal}
            className="flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Создать статью
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
            Опубликованные ({counts.active})
          </button>
          <button
            onClick={() => setStatus('inactive')}
            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
              statusFilter === 'inactive'
                ? 'bg-gray-500 text-white'
                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
            }`}
          >
            Черновики ({counts.inactive})
          </button>
        </div>

        {/* Categories */}
        {categories.length > 0 && (
          <div className="mb-6">
            <h3 className="text-sm font-medium text-gray-700 mb-2">Разделы:</h3>
            <div className="flex flex-wrap gap-2">
              {categories.map((cat) => (
                <span
                  key={cat.category}
                  className="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full"
                >
                  {cat.category} ({cat.count})
                </span>
              ))}
            </div>
          </div>
        )}

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : articles.length === 0 ? (
          <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Нет статей</h3>
            <p className="text-gray-500 mb-4">Создайте первую статью для блога</p>
            <button
              onClick={openCreateModal}
              className="inline-flex items-center gap-2 px-4 py-2 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Создать статью
            </button>
          </div>
        ) : (
          <div className="space-y-4">
            {articles.map((article) => (
              <div
                key={article.id}
                className={`bg-white rounded-xl border p-6 ${
                  article.is_active ? 'border-gray-200' : 'border-gray-300 bg-gray-50/30'
                }`}
              >
                <div className="flex flex-col lg:flex-row lg:items-start gap-4">
                  {/* Cover image */}
                  {article.cover_image && (
                    <div className="w-32 h-24 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                      <img
                        src={article.cover_image}
                        alt={article.title}
                        className="w-full h-full object-cover"
                      />
                    </div>
                  )}

                  {/* Content */}
                  <div className="flex-1 min-w-0">
                    <div className="flex flex-wrap items-center gap-3 mb-2">
                      <h3 className="font-semibold text-lg text-gray-900">{article.title}</h3>
                      <span className={`px-2 py-0.5 text-xs font-medium rounded-full ${
                        article.is_active
                          ? 'bg-green-100 text-green-700'
                          : 'bg-gray-100 text-gray-700'
                      }`}>
                        {article.is_active ? 'Опубликовано' : 'Черновик'}
                      </span>
                    </div>

                    <div className="flex flex-wrap gap-4 text-sm text-gray-500 mb-2">
                      <span className="flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        {article.category}
                      </span>
                      <span className="flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {formatDate(article.published_at)}
                      </span>
                      <span className="flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {article.author_name}
                      </span>
                    </div>

                    <p className="text-sm text-gray-600 mb-2">
                      <span className="text-gray-400">URL:</span> /client/features/blog/{article.slug}
                    </p>

                    {article.short_description && (
                      <p className="text-gray-700 line-clamp-2">{article.short_description}</p>
                    )}
                  </div>

                  {/* Actions */}
                  <div className="flex lg:flex-col gap-2 flex-wrap">
                    <a
                      href={`/client/features/blog/${article.slug}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center gap-2 px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 text-sm font-medium rounded-lg transition-colors"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                      </svg>
                      Просмотр
                    </a>

                    <button
                      onClick={() => toggleActive(article)}
                      disabled={actionLoading === article.id}
                      className={`flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 ${
                        article.is_active
                          ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                          : 'bg-green-500 hover:bg-green-600 text-white'
                      }`}
                    >
                      {actionLoading === article.id ? (
                        <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                      ) : article.is_active ? (
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                      ) : (
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                      )}
                      {article.is_active ? 'Снять' : 'Опубликовать'}
                    </button>

                    <button
                      onClick={() => openEditModal(article)}
                      className="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                    >
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Редактировать
                    </button>

                    <button
                      onClick={() => deleteArticle(article)}
                      disabled={actionLoading === article.id}
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
            ))}
          </div>
        )}
      </div>

      {/* Create/Edit modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-start justify-center z-50 p-4 overflow-y-auto">
          <div className="bg-white rounded-xl max-w-4xl w-full my-8">
            <div className="p-6 border-b border-gray-200 flex items-center justify-between">
              <h2 className="text-xl font-bold text-gray-900">
                {editingArticle ? 'Редактирование статьи' : 'Создание статьи'}
              </h2>
              <button
                onClick={() => setModalOpen(false)}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div className="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
              {/* Title & Slug */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Заголовок <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formTitle}
                    onChange={(e) => handleTitleChange(e.target.value)}
                    placeholder="Название статьи"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Ссылка браузера <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formSlug}
                    onChange={(e) => setFormSlug(e.target.value)}
                    placeholder="shkaf-kupe-preimushhestva"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                  <p className="mt-1 text-xs text-gray-500">/client/features/blog/{formSlug || '...'}</p>
                </div>
              </div>

              {/* Category & Author */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Раздел <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formCategory}
                    onChange={(e) => setFormCategory(e.target.value)}
                    placeholder="Помощь в выборе шкафа-купе"
                    list="categories-list"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                  <datalist id="categories-list">
                    {categories.map((cat) => (
                      <option key={cat.category} value={cat.category} />
                    ))}
                  </datalist>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Автор <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formAuthor}
                    onChange={(e) => setFormAuthor(e.target.value)}
                    placeholder="Имя автора"
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
              </div>

              {/* Published date & Active toggle */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Дата публикации
                  </label>
                  <input
                    type="date"
                    value={formPublishedAt}
                    onChange={(e) => setFormPublishedAt(e.target.value)}
                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                  />
                </div>
                <div className="flex items-end">
                  <div className="flex items-center gap-3 pb-3">
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
                      {formIsActive ? 'Опубликовать сразу' : 'Сохранить как черновик'}
                    </span>
                  </div>
                </div>
              </div>

              {/* Cover image */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Обложка статьи
                </label>
                <div className="flex gap-4 items-start">
                  {formCoverImage && (
                    <div className="relative w-32 h-24 bg-gray-200 rounded-lg overflow-hidden">
                      <img
                        src={formCoverImage}
                        alt="Cover"
                        className="w-full h-full object-cover"
                      />
                      <button
                        type="button"
                        onClick={() => setFormCoverImage('')}
                        className="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-sm"
                      >
                        ×
                      </button>
                    </div>
                  )}
                  <div>
                    <input
                      ref={fileInputRef}
                      type="file"
                      accept="image/jpeg,image/png,image/webp"
                      onChange={handleCoverUpload}
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
                          <span>Загрузить обложку</span>
                        </>
                      )}
                    </button>
                  </div>
                </div>
              </div>

              {/* Short description */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Короткое описание
                </label>
                <textarea
                  value={formShortDesc}
                  onChange={(e) => setFormShortDesc(e.target.value)}
                  rows={3}
                  placeholder="Краткое описание для превью..."
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none resize-none"
                />
              </div>

              {/* Content editor */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Полное описание <span className="text-red-500">*</span>
                </label>

                {/* Editor toolbar */}
                <div className="flex flex-wrap gap-1 p-2 border border-gray-300 border-b-0 rounded-t-lg bg-gray-50">
                  <button
                    type="button"
                    onClick={() => execCommand('bold')}
                    className="p-2 hover:bg-gray-200 rounded font-bold"
                    title="Жирный"
                  >
                    B
                  </button>
                  <button
                    type="button"
                    onClick={() => execCommand('italic')}
                    className="p-2 hover:bg-gray-200 rounded italic"
                    title="Курсив"
                  >
                    I
                  </button>
                  <button
                    type="button"
                    onClick={() => execCommand('underline')}
                    className="p-2 hover:bg-gray-200 rounded underline"
                    title="Подчеркнутый"
                  >
                    U
                  </button>
                  <div className="w-px bg-gray-300 mx-1" />
                  <button
                    type="button"
                    onClick={() => execCommand('formatBlock', 'h2')}
                    className="p-2 hover:bg-gray-200 rounded text-sm font-bold"
                    title="Заголовок H2"
                  >
                    H2
                  </button>
                  <button
                    type="button"
                    onClick={() => execCommand('formatBlock', 'h3')}
                    className="p-2 hover:bg-gray-200 rounded text-sm font-bold"
                    title="Заголовок H3"
                  >
                    H3
                  </button>
                  <button
                    type="button"
                    onClick={() => execCommand('formatBlock', 'p')}
                    className="p-2 hover:bg-gray-200 rounded text-sm"
                    title="Параграф"
                  >
                    P
                  </button>
                  <div className="w-px bg-gray-300 mx-1" />
                  <button
                    type="button"
                    onClick={() => execCommand('insertUnorderedList')}
                    className="p-2 hover:bg-gray-200 rounded"
                    title="Маркированный список"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                  </button>
                  <button
                    type="button"
                    onClick={() => execCommand('insertOrderedList')}
                    className="p-2 hover:bg-gray-200 rounded"
                    title="Нумерованный список"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                  </button>
                  <div className="w-px bg-gray-300 mx-1" />
                  <input
                    ref={contentFileInputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    onChange={handleContentImageUpload}
                    className="hidden"
                  />
                  <button
                    type="button"
                    onClick={() => contentFileInputRef.current?.click()}
                    disabled={uploading}
                    className="p-2 hover:bg-gray-200 rounded disabled:opacity-50"
                    title="Вставить изображение"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      const url = prompt('Введите URL ссылки:');
                      if (url) execCommand('createLink', url);
                    }}
                    className="p-2 hover:bg-gray-200 rounded"
                    title="Вставить ссылку"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                  </button>
                </div>

                {/* Editor area */}
                <div
                  ref={editorRef}
                  contentEditable
                  onInput={(e) => setFormContent((e.target as HTMLDivElement).innerHTML)}
                  dangerouslySetInnerHTML={{ __html: formContent }}
                  className="w-full min-h-[300px] px-4 py-3 border border-gray-300 rounded-b-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none prose max-w-none"
                  style={{ whiteSpace: 'pre-wrap' }}
                />
              </div>

              {/* SEO fields */}
              <div className="border-t border-gray-200 pt-6">
                <h3 className="text-sm font-medium text-gray-900 mb-4">SEO настройки (опционально)</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Meta Title
                    </label>
                    <input
                      type="text"
                      value={formMetaTitle}
                      onChange={(e) => setFormMetaTitle(e.target.value)}
                      placeholder={formTitle || 'Заголовок для поисковых систем'}
                      className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Meta Description
                    </label>
                    <textarea
                      value={formMetaDesc}
                      onChange={(e) => setFormMetaDesc(e.target.value)}
                      rows={2}
                      placeholder={formShortDesc || 'Описание для поисковых систем'}
                      className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none resize-none"
                    />
                  </div>
                </div>
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
                onClick={saveArticle}
                disabled={saving}
                className="flex-1 px-4 py-3 bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
              >
                {saving ? 'Сохранение...' : editingArticle ? 'Сохранить' : 'Создать'}
              </button>
            </div>
          </div>
        </div>
      )}
    </AdminPageWrapper>
  );
}

export default function BlogPage() {
  return (
    <Suspense fallback={
      <AdminPageWrapper>
        <div className="flex justify-center items-center min-h-[400px]">
          <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
        </div>
      </AdminPageWrapper>
    }>
      <BlogPageContent />
    </Suspense>
  );
}
