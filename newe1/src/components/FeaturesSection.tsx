import Link from 'next/link';

export default function FeaturesSection() {
  return (
    <section className="py-4 bg-[#f5f5f5]">
      <div className="container-custom">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          {/* Card 1 - Шкафы в наличии */}
          <div className="bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all">
            <span className="inline-block bg-[#62bb46] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
              ХИТ
            </span>
            <h3 className="text-base font-bold text-[#3d4543] mb-1">Шкафы</h3>
            <p className="text-xs text-gray-500 mb-3 leading-relaxed">
              В наличии и под заказ • подбор по размерам
            </p>
            <div className="space-y-1.5">
              <Link
                href="/catalog/shkafi"
                className="block w-full text-center bg-[#62bb46] text-white text-xs font-bold py-2 px-3 rounded-full hover:bg-[#55a83d] transition-colors"
              >
                Смотреть витрину
              </Link>
              <Link
                href="/catalog/shkafi?filter=size"
                className="flex items-center justify-center gap-1.5 w-full text-center text-gray-500 text-xs py-1.5 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Быстрый подбор
              </Link>
            </div>
          </div>

          {/* Card 2 - Гардеробные */}
          <div className="bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all">
            <span className="inline-block bg-[#3d4543] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
              НОВОЕ
            </span>
            <h3 className="text-base font-bold text-[#3d4543] mb-1">Гардеробные</h3>
            <p className="text-xs text-gray-500 mb-3 leading-relaxed">
              Проекты хранения • наполнение • стиль
            </p>
            <div className="space-y-1.5">
              <Link
                href="/catalog/garderobnye"
                className="block w-full text-center bg-[#62bb46] text-white text-xs font-bold py-2 px-3 rounded-full hover:bg-[#55a83d] transition-colors"
              >
                Популярные решения
              </Link>
              <Link
                href="/consultant"
                className="flex items-center justify-center gap-1.5 w-full text-center text-gray-500 text-xs py-1.5 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Умный консультант
              </Link>
            </div>
          </div>

          {/* Card 3 - Мебель по вашим размерам */}
          <div className="bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all">
            <span className="inline-block border border-[#3d4543] text-[#3d4543] text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
              ИНДИВИДУАЛЬНО
            </span>
            <h3 className="text-base font-bold text-[#3d4543] mb-1">На заказ под нишу</h3>
            <p className="text-xs text-gray-500 mb-3 leading-relaxed">
              Встроенный • нестандарт • замер и проект
            </p>
            <div className="space-y-1.5">
              <Link
                href="/custom"
                className="block w-full text-center border border-[#62bb46] text-[#62bb46] text-xs font-bold py-1.5 px-3 rounded-full hover:bg-[#62bb46] hover:text-white transition-colors"
              >
                Вызвать замер
              </Link>
              <Link
                href="/quiz"
                className="flex items-center justify-center gap-1.5 w-full text-center text-gray-500 text-xs py-1.5 hover:text-[#62bb46] transition-colors"
              >
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Квиз-расчёт
              </Link>
            </div>
          </div>

          {/* Card 4 - Серии и стили */}
          <div className="bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all">
            <span className="inline-block bg-[#ff6b6b] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
              -10%
            </span>
            <h3 className="text-base font-bold text-[#3d4543] mb-1">Серии и стили</h3>
            <p className="text-xs text-gray-500 mb-3 leading-relaxed">
              Эста • Локер • Прайм • Оптим
            </p>
            <div className="flex gap-1.5">
              <Link
                href="/series"
                className="flex-1 text-center bg-[#62bb46] text-white text-xs font-bold py-2 px-2 rounded-full hover:bg-[#55a83d] transition-colors"
              >
                Открыть
              </Link>
              <Link
                href="/sales"
                className="flex-1 text-center border border-[#62bb46] text-[#62bb46] text-xs font-bold py-1.5 px-2 rounded-full hover:bg-[#62bb46] hover:text-white transition-colors"
              >
                Акции
              </Link>
            </div>
          </div>

          {/* Card 5 - Распродажа */}
          <div className="bg-white rounded-2xl p-4 border border-[#e0e0e0] hover:border-[#c5e1a5] hover:shadow-md transition-all sm:col-span-2 lg:col-span-1">
            <span className="inline-block bg-[#f5b800] text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full mb-2">
              ВЫГОДНО
            </span>
            <h3 className="text-base font-bold text-[#3d4543] mb-1">Распродажа</h3>
            <p className="text-xs text-gray-500 mb-3 leading-relaxed">
              Самые выгодные позиции тут
            </p>
            <Link
              href="/sales"
              className="block w-full text-center bg-[#f5b800] text-white text-xs font-bold py-2 px-3 rounded-full hover:bg-[#e0a800] transition-colors"
            >
              Смотреть скидки
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}
