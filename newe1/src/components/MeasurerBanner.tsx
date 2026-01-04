'use client';

import { useState } from 'react';
import MeasurementModal from './MeasurementModal';

export default function MeasurerBanner() {
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <>
      <MeasurementModal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} />

      <section className="bg-gradient-to-r from-[#3d4543] to-[#2a302e]">
        <div className="container-custom">
          <div
            onClick={() => setIsModalOpen(true)}
            className="py-8 md:py-10 flex flex-col md:flex-row items-center justify-between gap-6 cursor-pointer group"
          >
            {/* Левая часть - текст */}
            <div className="text-center md:text-left">
              <h2 className="text-2xl md:text-3xl font-bold text-white mb-2">
                Шкафы по вашим размерам
              </h2>
              <p className="text-white/80 text-base md:text-lg">
                Замер и консультация на дому — бесплатно
              </p>
            </div>

            {/* Правая часть - кнопка */}
            <button
              onClick={(e) => {
                e.stopPropagation();
                setIsModalOpen(true);
              }}
              className="flex items-center gap-3 px-8 py-4 bg-[#62bb46] text-white font-bold rounded-lg hover:bg-[#55a83d] transition-all group-hover:scale-105 shadow-lg"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span className="text-lg">Вызвать замерщика</span>
            </button>
          </div>
        </div>
      </section>
    </>
  );
}
