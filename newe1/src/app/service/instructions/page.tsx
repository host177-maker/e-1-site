'use client';

import { useEffect, useState } from 'react';

interface Instruction {
  id: number;
  name: string;
  pdf_path: string;
  video_url: string | null;
}

export default function InstructionsPage() {
  const [instructions, setInstructions] = useState<Instruction[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api/instructions')
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          setInstructions(data.data);
        }
      })
      .catch((error) => {
        console.error('Error fetching instructions:', error);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-8">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543]">
            Инструкции для сборки нашей мебели
          </h1>
        </div>
      </div>

      {/* Main content */}
      <div className="container-custom py-8">
        {loading ? (
          <div className="flex justify-center py-12">
            <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
          </div>
        ) : instructions.length === 0 ? (
          <div className="text-center py-12">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">Инструкции скоро появятся</h3>
            <p className="text-gray-500">Мы готовим инструкции по сборке для вас</p>
          </div>
        ) : (
          <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div className="divide-y divide-gray-100">
              {instructions.map((instruction) => (
                <div
                  key={instruction.id}
                  className="flex flex-col sm:flex-row sm:items-center gap-4 p-4 hover:bg-gray-50 transition-colors"
                >
                  {/* PDF icon */}
                  <div className="w-10 h-12 bg-green-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg className="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                      <path d="M14 2v6h6"/>
                      <path fill="none" stroke="currentColor" strokeWidth="1.5" d="M9 13v5m0-2.5h1.5a1.25 1.25 0 0 0 0-2.5H9m5 2.5h1.5a1.25 1.25 0 0 0 0-2.5H14v5"/>
                    </svg>
                  </div>

                  {/* Name */}
                  <div className="flex-1 min-w-0">
                    <h3 className="font-medium text-[#3d4543]">{instruction.name}</h3>
                  </div>

                  {/* Actions */}
                  <div className="flex items-center gap-3">
                    <a
                      href={instruction.pdf_path}
                      download
                      className="inline-flex items-center gap-2 px-4 py-2 bg-[#62bb46] hover:bg-[#55a83d] text-white font-medium rounded-lg transition-colors"
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                      </svg>
                      Скачать
                    </a>

                    {instruction.video_url && (
                      <a
                        href={instruction.video_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 hover:border-[#62bb46] hover:text-[#62bb46] text-gray-700 font-medium rounded-lg transition-colors"
                      >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Видео
                      </a>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Help section */}
        <div className="mt-10 bg-[#f9f9fa] rounded-lg p-6">
          <h2 className="text-xl font-bold text-[#3d4543] mb-3">Нужна помощь со сборкой?</h2>
          <p className="text-gray-600 mb-4">
            Если у вас возникли вопросы по сборке мебели, наши специалисты всегда готовы помочь.
            Вы также можете заказать профессиональную сборку от нашей компании.
          </p>
          <a
            href="https://booking.e-1.ru/service/"
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 px-5 py-3 bg-[#62bb46] hover:bg-[#55a83d] text-white font-medium rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            Чат со службой клиентского сервиса
          </a>
        </div>
      </div>
    </div>
  );
}
