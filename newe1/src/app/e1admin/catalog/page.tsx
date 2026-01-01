'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import AdminPageWrapper from '@/components/admin/AdminPageWrapper';

interface CatalogStats {
  series: number;
  products: number;
  variants: number;
}

interface ImportHistory {
  id: number;
  imported_at: string;
  products_count: number;
  variants_count: number;
  status: string;
  error_message?: string;
}

interface ImportResult {
  success: boolean;
  message: string;
  stats: {
    series: number;
    bodyColors: number;
    fillings: number;
    products: number;
    variants: number;
  };
  errors: string[];
}

export default function CatalogPage() {
  const [stats, setStats] = useState<CatalogStats>({ series: 0, products: 0, variants: 0 });
  const [imports, setImports] = useState<ImportHistory[]>([]);
  const [loading, setLoading] = useState(true);
  const [importing, setImporting] = useState(false);
  const [importResult, setImportResult] = useState<ImportResult | null>(null);
  const [googleSheetUrl, setGoogleSheetUrl] = useState('https://docs.google.com/spreadsheets/d/1rzihgEmV69qLreTWNkYIXWEuL8J_I0t-cTngqrulnXY/edit');
  const fileInputRef = useRef<HTMLInputElement>(null);

  const fetchStatus = useCallback(async () => {
    try {
      const response = await fetch('/api/e1admin/catalog/import');
      const data = await response.json();
      if (data.stats) {
        setStats(data.stats);
      }
      if (data.imports) {
        setImports(data.imports);
      }
    } catch (error) {
      console.error('Error fetching catalog status:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchStatus();
  }, [fetchStatus]);

  const handleImportFromFile = async (file: File) => {
    setImporting(true);
    setImportResult(null);

    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await fetch('/api/e1admin/catalog/import', {
        method: 'POST',
        body: formData,
      });

      const result = await response.json();
      setImportResult(result);

      if (result.success || result.stats) {
        fetchStatus();
      }
    } catch (error) {
      console.error('Import error:', error);
      setImportResult({
        success: false,
        message: 'Ошибка подключения к серверу',
        stats: { series: 0, bodyColors: 0, fillings: 0, products: 0, variants: 0 },
        errors: [error instanceof Error ? error.message : 'Неизвестная ошибка']
      });
    } finally {
      setImporting(false);
    }
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      handleImportFromFile(file);
    }
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const handleImportFromUrl = async () => {
    if (!googleSheetUrl.trim()) {
      alert('Введите URL Google Sheets');
      return;
    }

    setImporting(true);
    setImportResult(null);

    try {
      const formData = new FormData();
      formData.append('googleSheetUrl', googleSheetUrl);

      const response = await fetch('/api/e1admin/catalog/import', {
        method: 'POST',
        body: formData,
      });

      const result = await response.json();
      setImportResult(result);

      if (result.success || result.stats) {
        fetchStatus();
      }
    } catch (error) {
      console.error('Import error:', error);
      setImportResult({
        success: false,
        message: 'Ошибка подключения к серверу',
        stats: { series: 0, bodyColors: 0, fillings: 0, products: 0, variants: 0 },
        errors: [error instanceof Error ? error.message : 'Неизвестная ошибка']
      });
    } finally {
      setImporting(false);
    }
  };

  const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  return (
    <AdminPageWrapper>
      <div className="p-6 lg:p-8">
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-gray-900">Каталог товаров</h1>
          <p className="text-gray-600 mt-1">Управление каталогом шкафов-купе</p>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : (
          <>
            {/* Статистика */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Серий</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.series}</p>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Товаров</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.products}</p>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500">Вариантов (SKU)</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.variants}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Импорт */}
            <div className="bg-white rounded-xl border border-gray-200 p-6 mb-8">
              <h2 className="text-lg font-bold text-gray-900 mb-4">Импорт каталога</h2>
              <p className="text-gray-600 mb-6">
                Загрузите xlsx файл с данными каталога или укажите ссылку на Google Sheets
              </p>

              <div className="space-y-6">
                {/* Загрузка файла */}
                <div>
                  <h3 className="font-medium text-gray-700 mb-2">Загрузка из файла (.xlsx)</h3>
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept=".xlsx,.xls"
                    onChange={handleFileSelect}
                    className="hidden"
                  />
                  <button
                    onClick={() => fileInputRef.current?.click()}
                    disabled={importing}
                    className="flex items-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-[#7cb342] hover:text-[#7cb342] transition-colors disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-auto"
                  >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Выбрать файл
                  </button>
                </div>

                {/* Разделитель */}
                <div className="flex items-center gap-4">
                  <div className="flex-1 h-px bg-gray-200" />
                  <span className="text-gray-400 text-sm">или</span>
                  <div className="flex-1 h-px bg-gray-200" />
                </div>

                {/* Google Sheets URL */}
                <div>
                  <h3 className="font-medium text-gray-700 mb-2">Загрузка из Google Sheets</h3>
                  <div className="flex flex-col md:flex-row gap-3">
                    <input
                      type="text"
                      value={googleSheetUrl}
                      onChange={(e) => setGoogleSheetUrl(e.target.value)}
                      placeholder="https://docs.google.com/spreadsheets/d/..."
                      className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#7cb342] focus:border-transparent outline-none"
                    />
                    <button
                      onClick={handleImportFromUrl}
                      disabled={importing || !googleSheetUrl.trim()}
                      className="flex items-center justify-center gap-2 px-6 py-3 bg-[#7cb342] hover:bg-[#689f38] text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {importing ? (
                        <>
                          <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                          Импорт...
                        </>
                      ) : (
                        <>
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                          </svg>
                          Импортировать
                        </>
                      )}
                    </button>
                  </div>
                  <p className="text-sm text-gray-500 mt-2">
                    Таблица должна быть опубликована в веб (Файл → Опубликовать в веб)
                  </p>
                </div>
              </div>
            </div>

            {/* Результат импорта */}
            {importResult && (
              <div className={`rounded-xl border p-6 mb-8 ${
                importResult.success
                  ? 'bg-green-50 border-green-200'
                  : 'bg-yellow-50 border-yellow-200'
              }`}>
                <div className="flex items-start gap-4">
                  {importResult.success ? (
                    <svg className="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  ) : (
                    <svg className="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  )}
                  <div className="flex-1">
                    <h3 className={`font-bold ${importResult.success ? 'text-green-800' : 'text-yellow-800'}`}>
                      {importResult.message}
                    </h3>
                    <div className="mt-3 grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                      <div>
                        <span className="text-gray-500">Серий:</span>
                        <span className="font-medium ml-1">{importResult.stats.series}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Цветов:</span>
                        <span className="font-medium ml-1">{importResult.stats.bodyColors}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Наполнений:</span>
                        <span className="font-medium ml-1">{importResult.stats.fillings}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Товаров:</span>
                        <span className="font-medium ml-1">{importResult.stats.products}</span>
                      </div>
                      <div>
                        <span className="text-gray-500">Вариантов:</span>
                        <span className="font-medium ml-1">{importResult.stats.variants}</span>
                      </div>
                    </div>
                    {importResult.errors.length > 0 && (
                      <details className="mt-4">
                        <summary className="cursor-pointer text-yellow-700 hover:text-yellow-800">
                          Показать ошибки ({importResult.errors.length})
                        </summary>
                        <ul className="mt-2 space-y-1 text-sm text-yellow-700 max-h-40 overflow-y-auto">
                          {importResult.errors.map((error, i) => (
                            <li key={i}>• {error}</li>
                          ))}
                        </ul>
                      </details>
                    )}
                  </div>
                </div>
              </div>
            )}

            {/* История импортов */}
            {imports.length > 0 && (
              <div className="bg-white rounded-xl border border-gray-200 p-6">
                <h2 className="text-lg font-bold text-gray-900 mb-4">История импортов</h2>
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b border-gray-200">
                        <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Дата</th>
                        <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Товаров</th>
                        <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Вариантов</th>
                        <th className="text-left py-3 px-4 text-sm font-medium text-gray-500">Статус</th>
                      </tr>
                    </thead>
                    <tbody>
                      {imports.map((imp) => (
                        <tr key={imp.id} className="border-b border-gray-100 last:border-0">
                          <td className="py-3 px-4 text-sm text-gray-900">
                            {formatDate(imp.imported_at)}
                          </td>
                          <td className="py-3 px-4 text-sm text-gray-600">{imp.products_count}</td>
                          <td className="py-3 px-4 text-sm text-gray-600">{imp.variants_count}</td>
                          <td className="py-3 px-4">
                            <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                              imp.status === 'completed'
                                ? 'bg-green-100 text-green-700'
                                : imp.status === 'failed'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-yellow-100 text-yellow-700'
                            }`}>
                              {imp.status === 'completed' ? 'Успешно' :
                               imp.status === 'failed' ? 'Ошибка' : 'В процессе'}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </AdminPageWrapper>
  );
}
