import Link from 'next/link';

export default function FeaturesSection() {
  return (
    <section className="py-6 bg-[#f5f5f5]">
      <div className="container-custom">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Card 1 - Шкафы */}
          <div className="bg-white rounded-2xl p-5 border-2 border-dashed border-[#c5e1a5] hover:border-[#62bb46] transition-colors">
            <span className="inline-block bg-[#62bb46] text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
              ХИТ
            </span>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">Шкафы</h3>
            <p className="text-sm text-gray-600 mb-4 leading-relaxed">
              В наличии и под заказ • подбор по размерам
            </p>
            <div className="space-y-2">
              <Link
                href="/catalog/shkafi"
                className="block w-full text-center bg-[#62bb46] text-white text-sm font-bold py-2.5 px-4 rounded-full hover:bg-[#55a83d] transition-colors"
              >
                Смотреть витрину
              </Link>
              <Link
                href="/catalog/shkafi?filter=size"
                className="flex items-center justify-center gap-2 w-full text-center text-[#3d4543] text-sm py-2 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Быстрый подбор
              </Link>
            </div>
          </div>

          {/* Card 2 - Гардеробные */}
          <div className="bg-white rounded-2xl p-5 border-2 border-dashed border-[#c5e1a5] hover:border-[#62bb46] transition-colors">
            <span className="inline-block bg-[#3d4543] text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
              НОВОЕ
            </span>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">Гардеробные</h3>
            <p className="text-sm text-gray-600 mb-4 leading-relaxed">
              Проекты хранения • наполнение • стиль
            </p>
            <div className="space-y-2">
              <Link
                href="/catalog/garderobnye"
                className="block w-full text-center bg-[#62bb46] text-white text-sm font-bold py-2.5 px-4 rounded-full hover:bg-[#55a83d] transition-colors"
              >
                Популярные решения
              </Link>
              <Link
                href="/consultant"
                className="flex items-center justify-center gap-2 w-full text-center text-[#3d4543] text-sm py-2 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Умный консультант
              </Link>
            </div>
          </div>

          {/* Card 3 - На заказ под нишу */}
          <div className="bg-white rounded-2xl p-5 border-2 border-dashed border-[#c5e1a5] hover:border-[#62bb46] transition-colors">
            <span className="inline-block border border-[#3d4543] text-[#3d4543] text-xs font-bold px-3 py-1 rounded-full mb-3">
              ИНДИВИДУАЛЬНО
            </span>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">На заказ под нишу</h3>
            <p className="text-sm text-gray-600 mb-4 leading-relaxed">
              Встроенный • нестандарт • замер и проект
            </p>
            <div className="space-y-2">
              <Link
                href="/custom"
                className="block w-full text-center border-2 border-[#62bb46] text-[#62bb46] text-sm font-bold py-2 px-4 rounded-full hover:bg-[#62bb46] hover:text-white transition-colors"
              >
                Вызвать замер
              </Link>
              <Link
                href="/quiz"
                className="flex items-center justify-center gap-2 w-full text-center text-[#3d4543] text-sm py-2 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Квиз-расчёт
              </Link>
            </div>
          </div>

          {/* Card 4 - Серии и стили */}
          <div className="bg-white rounded-2xl p-5 border-2 border-dashed border-[#c5e1a5] hover:border-[#62bb46] transition-colors">
            <span className="inline-block bg-[#ff6b6b] text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
              -10%
            </span>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">Серии и стили</h3>
            <p className="text-sm text-gray-600 mb-4 leading-relaxed">
              Эста • Локер • Прайм • Оптим
            </p>
            <div className="flex gap-2">
              <Link
                href="/series"
                className="flex-1 text-center bg-[#62bb46] text-white text-sm font-bold py-2.5 px-4 rounded-full hover:bg-[#55a83d] transition-colors"
              >
                Открыть
              </Link>
              <Link
                href="/sales"
                className="flex-1 text-center border-2 border-[#62bb46] text-[#62bb46] text-sm font-bold py-2 px-4 rounded-full hover:bg-[#62bb46] hover:text-white transition-colors"
              >
                Акции
              </Link>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
