'use client';

import { useState } from 'react';
import MeasurementModal from './MeasurementModal';

export default function MeasurementCTA() {
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <>
      <section className="py-8 bg-gradient-to-r from-[#62bb46] to-[#5cb141]">
        <div className="container-custom">
          <div className="flex flex-col md:flex-row items-center justify-between gap-6">
            <div className="text-white text-center md:text-left">
              <h2 className="text-2xl md:text-3xl font-bold mb-2">
                Бесплатный замер у вас дома
              </h2>
              <p className="text-white/80 text-lg">
                Наш специалист приедет, произведёт замеры и поможет подобрать идеальное решение
              </p>
            </div>
            <button
              onClick={() => setIsModalOpen(true)}
              className="flex items-center gap-3 px-8 py-4 bg-white text-[#62bb46] font-bold rounded-lg hover:bg-gray-50 transition-colors shadow-lg hover:shadow-xl text-lg whitespace-nowrap"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Вызвать замерщика
            </button>
          </div>
        </div>
      </section>

      <MeasurementModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
      />
    </>
  );
}
