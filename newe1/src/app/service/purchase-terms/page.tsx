import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Условия покупки | E-1.RU",
  description: "Условия покупки шкафов-купе и гардеробных в компании Е1. Гарантия до 10 лет, удобные способы оплаты, рассрочка 0-0-6.",
};

export default function PurchaseTermsPage() {
  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-12">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-4">
            Условия покупки
          </h1>
          <p className="text-gray-600 text-lg">
            Всё, что нужно знать перед заказом шкафа-купе
          </p>
        </div>
      </div>

      {/* Main content */}
      <div className="container-custom py-10">
        {/* Order process */}
        <section className="mb-12">
          <h2 className="text-2xl font-bold text-[#3d4543] mb-6">
            Как оформить заказ
          </h2>
          <div className="grid md:grid-cols-4 gap-6">
            <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">
                1
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Выбор товара</h3>
              <p className="text-sm text-gray-600">
                Выберите шкаф в каталоге, укажите размер, цвет и дополнительные элементы
              </p>
            </div>
            <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">
                2
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Оформление</h3>
              <p className="text-sm text-gray-600">
                Нажмите «Заказать» — менеджер свяжется для уточнения деталей
              </p>
            </div>
            <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">
                3
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Оплата</h3>
              <p className="text-sm text-gray-600">
                Выберите удобный способ оплаты: наличные, карта или рассрочка
              </p>
            </div>
            <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-4">
                4
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Доставка</h3>
              <p className="text-sm text-gray-600">
                Получите шкаф с доставкой и профессиональной сборкой
              </p>
            </div>
          </div>
        </section>

        {/* Custom orders */}
        <section className="mb-12">
          <div className="bg-[#3d4543] text-white rounded-lg p-8">
            <h2 className="text-2xl font-bold mb-4">Индивидуальный заказ</h2>
            <p className="text-gray-300 mb-4 leading-relaxed">
              Если вам нужны нестандартные размеры — мы можем изготовить шкафы-купе,
              гардеробные и встроенные шкафы практически любого размера.
              Задайте вопрос онлайн-консультанту для расчета стоимости.
            </p>
            <div className="flex flex-wrap gap-4">
              <Link
                href="/custom"
                className="inline-flex items-center gap-2 bg-[#62bb46] text-white px-6 py-3 rounded font-bold hover:bg-[#55a83d] transition-colors"
              >
                Шкафы на заказ
              </Link>
              <Link
                href="/catalog/garderobnye"
                className="inline-flex items-center gap-2 bg-white/10 text-white px-6 py-3 rounded font-bold hover:bg-white/20 transition-colors"
              >
                Гардеробные
              </Link>
            </div>
          </div>
        </section>

        {/* Warranty section */}
        <section className="mb-12">
          <h2 className="text-2xl font-bold text-[#3d4543] mb-6">
            Гарантия
          </h2>

          <div className="grid md:grid-cols-2 gap-8 mb-8">
            <div className="border-l-4 border-[#62bb46] bg-[#f9f9fa] p-6 rounded-r-lg">
              <div className="text-5xl font-bold text-[#62bb46] mb-2">10 лет</div>
              <p className="text-gray-600">
                Максимальный срок гарантии на шкафы-купе от компании Е1
              </p>
            </div>
            <div className="border-l-4 border-[#62bb46] bg-[#f9f9fa] p-6 rounded-r-lg">
              <div className="text-5xl font-bold text-[#62bb46] mb-2">5 лет</div>
              <p className="text-gray-600">
                Минимальный срок службы корпусного шкафа-купе при правильной эксплуатации
              </p>
            </div>
          </div>

          <p className="text-gray-700 leading-relaxed mb-6">
            Мы помогаем с любыми вопросами по эксплуатации мебели в течение пяти лет,
            а также предоставляем постгарантийное обслуживание.
          </p>

          {/* Non-warranty cases */}
          <div className="bg-amber-50 border border-amber-200 rounded-lg p-6">
            <h3 className="font-bold text-[#3d4543] mb-4 flex items-center gap-2">
              <svg className="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              Что не является гарантийным случаем:
            </h3>
            <ul className="space-y-2 text-gray-700">
              <li className="flex items-start gap-2">
                <span className="text-amber-500 mt-1">•</span>
                Разбухание и деформация панелей ДСП вследствие неправильной эксплуатации
              </li>
              <li className="flex items-start gap-2">
                <span className="text-amber-500 mt-1">•</span>
                Разбитые зеркала и стекла (заменяются за счет покупателя)
              </li>
              <li className="flex items-start gap-2">
                <span className="text-amber-500 mt-1">•</span>
                Лампы подсветки (гарантия не распространяется)
              </li>
            </ul>
          </div>
        </section>

        {/* Operating conditions */}
        <section className="mb-12">
          <h2 className="text-2xl font-bold text-[#3d4543] mb-6">
            Условия эксплуатации
          </h2>
          <div className="grid md:grid-cols-2 gap-6">
            <div className="flex items-start gap-4 p-4 bg-[#f9f9fa] rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-full flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-[#3d4543] mb-1">Влажность воздуха</h3>
                <p className="text-sm text-gray-600">
                  Храните и эксплуатируйте шкаф в сухих, проветриваемых помещениях при влажности 50-70%
                </p>
              </div>
            </div>

            <div className="flex items-start gap-4 p-4 bg-[#f9f9fa] rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-full flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-[#3d4543] mb-1">Расстояние от батарей</h3>
                <p className="text-sm text-gray-600">
                  Не располагайте шкаф ближе 40 см к отопительным приборам, сырым и холодным стенам
                </p>
              </div>
            </div>

            <div className="flex items-start gap-4 p-4 bg-[#f9f9fa] rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-full flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-[#3d4543] mb-1">Солнечный свет</h3>
                <p className="text-sm text-gray-600">
                  Избегайте длительного попадания прямых солнечных лучей на поверхность мебели
                </p>
              </div>
            </div>

            <div className="flex items-start gap-4 p-4 bg-[#f9f9fa] rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-full flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-[#3d4543] mb-1">Безопасность</h3>
                <p className="text-sm text-gray-600">
                  Закрепите шкаф к стене для сохранения устойчивости и безопасности
                </p>
              </div>
            </div>

            <div className="flex items-start gap-4 p-4 bg-[#f9f9fa] rounded-lg md:col-span-2">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-full flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-[#3d4543] mb-1">Транспортировка</h3>
                <p className="text-sm text-gray-600">
                  Транспортируйте корпусные шкафы-купе только в разобранном и упакованном виде
                </p>
              </div>
            </div>
          </div>
        </section>

        {/* Payment options */}
        <section className="mb-12">
          <h2 className="text-2xl font-bold text-[#3d4543] mb-6">
            Способы оплаты
          </h2>
          <div className="grid md:grid-cols-3 gap-6">
            <div className="p-6 border border-gray-200 rounded-lg hover:border-[#62bb46] transition-colors">
              <div className="w-12 h-12 bg-[#62bb46]/10 rounded-full flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Наличные</h3>
              <p className="text-sm text-gray-600">
                Оплата наличными при получении товара или в салоне
              </p>
            </div>

            <div className="p-6 border border-gray-200 rounded-lg hover:border-[#62bb46] transition-colors">
              <div className="w-12 h-12 bg-[#62bb46]/10 rounded-full flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Банковская карта</h3>
              <p className="text-sm text-gray-600">
                Оплата картой онлайн или при получении товара
              </p>
            </div>

            <div className="p-6 border border-[#62bb46] rounded-lg bg-[#62bb46]/5">
              <div className="w-12 h-12 bg-[#62bb46] rounded-full flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="font-bold text-[#3d4543] mb-2">Рассрочка 0-0-6</h3>
              <p className="text-sm text-gray-600">
                Без первоначального взноса, без переплат, на 6 месяцев
              </p>
              <Link
                href="/service/installment"
                className="inline-block mt-3 text-sm text-[#62bb46] hover:underline font-medium"
              >
                Подробнее о рассрочке →
              </Link>
            </div>
          </div>
        </section>

        {/* Related links */}
        <section className="bg-[#f9f9fa] rounded-lg p-8">
          <h2 className="text-xl font-bold text-[#3d4543] mb-6">Полезные ссылки</h2>
          <div className="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
            <Link
              href="/service/warranty"
              className="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow"
            >
              <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span className="text-sm font-medium text-[#3d4543]">Гарантия</span>
            </Link>
            <Link
              href="/service/delivery"
              className="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow"
            >
              <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
              </svg>
              <span className="text-sm font-medium text-[#3d4543]">Доставка</span>
            </Link>
            <Link
              href="/service/returns"
              className="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow"
            >
              <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
              </svg>
              <span className="text-sm font-medium text-[#3d4543]">Возврат</span>
            </Link>
            <Link
              href="/service/faq"
              className="flex items-center gap-3 p-4 bg-white rounded-lg hover:shadow-md transition-shadow"
            >
              <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span className="text-sm font-medium text-[#3d4543]">Вопросы</span>
            </Link>
          </div>
        </section>

        {/* Contact */}
        <div className="mt-10 text-center">
          <p className="text-gray-600 mb-3">
            Остались вопросы? Свяжитесь с нами!
          </p>
          <a href="tel:+78001001211" className="text-2xl font-bold text-[#62bb46] hover:underline">
            8-800-100-12-11
          </a>
          <p className="text-sm text-gray-500 mt-2">Звонок бесплатный по России</p>
        </div>
      </div>
    </div>
  );
}
