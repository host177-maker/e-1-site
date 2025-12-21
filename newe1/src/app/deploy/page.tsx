'use client';

import { useState } from 'react';

export default function DeployPage() {
  const [branchName, setBranchName] = useState('main');
  const [isLoading, setIsLoading] = useState(false);
  const [result, setResult] = useState<{
    success: boolean;
    message: string;
    details?: string;
  } | null>(null);

  const handleDeploy = async () => {
    if (!branchName.trim()) {
      setResult({
        success: false,
        message: 'Введите название ветки',
      });
      return;
    }

    setIsLoading(true);
    setResult(null);

    try {
      const response = await fetch('/api/deploy', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          branch: branchName.trim(),
        }),
      });

      const data = await response.json();
      setResult(data);
    } catch (error) {
      setResult({
        success: false,
        message: 'Ошибка подключения к серверу',
        details: error instanceof Error ? error.message : 'Unknown error',
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-900 text-white p-8">
      <div className="max-w-2xl mx-auto">
        <h1 className="text-3xl font-bold mb-8">Deploy Service</h1>

        <div className="bg-gray-800 rounded-lg p-6 mb-6">
          <label className="block mb-2 text-gray-300">
            Название ветки (branch):
          </label>
          <input
            type="text"
            value={branchName}
            onChange={(e) => setBranchName(e.target.value)}
            placeholder="main"
            className="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500"
            disabled={isLoading}
          />
        </div>

        <button
          onClick={handleDeploy}
          disabled={isLoading}
          className={`w-full py-4 rounded-lg font-bold text-lg transition-all ${
            isLoading
              ? 'bg-gray-600 cursor-not-allowed'
              : 'bg-blue-600 hover:bg-blue-700 cursor-pointer'
          }`}
        >
          {isLoading ? (
            <span className="flex items-center justify-center gap-2">
              <svg
                className="animate-spin h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  className="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  strokeWidth="4"
                ></circle>
                <path
                  className="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              Деплой в процессе...
            </span>
          ) : (
            'Deploy'
          )}
        </button>

        {result && (
          <div
            className={`mt-6 p-4 rounded-lg ${
              result.success
                ? 'bg-green-900 border border-green-700'
                : 'bg-red-900 border border-red-700'
            }`}
          >
            <div className="font-bold mb-2">
              {result.success ? 'Успешно!' : 'Ошибка'}
            </div>
            <div className="text-gray-300">{result.message}</div>
            {result.details && (
              <pre className="mt-4 p-3 bg-black/30 rounded text-sm overflow-x-auto whitespace-pre-wrap">
                {result.details}
              </pre>
            )}
          </div>
        )}

        <div className="mt-8 text-gray-500 text-sm">
          <p>Deploy выполняет следующие действия:</p>
          <ol className="list-decimal list-inside mt-2 space-y-1">
            <li>Скачивает указанную ветку из репозитория (git fetch)</li>
            <li>Переключается на ветку (git checkout)</li>
            <li>Обновляет код (git pull)</li>
            <li>Пересобирает Next.js (npm run build)</li>
            <li>Перезагружает Node.js сервер</li>
          </ol>
        </div>
      </div>
    </div>
  );
}
