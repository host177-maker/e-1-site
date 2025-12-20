import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Доставка и сборка | Е1.RU",
  description: "Условия доставки и сборки шкафов-купе от компании Е1. Доставка в 700+ городов России, собственная служба сборки.",
};

export default function DeliveryPage() {
  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-12">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-4">
            Доставка и сборка
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl">
            Мы доставляем мебель в более чем 700 городов России. Собственная служба сборки гарантирует качественную установку.
          </p>
        </div>
      </div>

      {/* Main content */}
      <div className="container-custom py-12">
        <div className="grid lg:grid-cols-2 gap-8">
          {/* Delivery section */}
          <div className="bg-[#f9f9fa] rounded-lg p-6">
            <div className="flex items-center gap-3 mb-6">
              <div className="w-12 h-12 rounded-full bg-[#62bb46] flex items-center justify-center">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
              </div>
              <h2 className="text-2xl font-bold text-[#3d4543]">Доставка</h2>
            </div>

            <div className="space-y-4">
              <div className="bg-white rounded-lg p-4">
                <h3 className="font-bold text-[#3d4543] mb-2">Льготная доставка</h3>
                <p className="text-gray-600 text-sm mb-2">По городу нахождения салона</p>
                <p className="text-2xl font-bold text-[#62bb46]">395 ₽</p>
                <p className="text-xs text-gray-500 mt-1">В близлежащие населённые пункты: 395 ₽ + 26 ₽/км</p>
              </div>

              <div className="bg-white rounded-lg p-4">
                <h3 className="font-bold text-[#3d4543] mb-2">Срочная доставка</h3>
                <p className="text-gray-600 text-sm mb-2">По городу нахождения салона</p>
                <p className="text-2xl font-bold text-[#62bb46]">2 300 ₽</p>
                <p className="text-xs text-gray-500 mt-1">В близлежащие населённые пункты: 2300 ₽ + 26 ₽/км</p>
              </div>

              <div className="bg-white rounded-lg p-4">
                <h3 className="font-bold text-[#3d4543] mb-2">Самовывоз</h3>
                <p className="text-gray-600 text-sm">
                  Вы можете забрать заказ самостоятельно из любого склада Е1. При оформлении самовывоза также можно заказать услугу сборки.
                </p>
              </div>

              <p className="text-sm text-gray-500">
                Максимальное расстояние для доставки и сборки шкафа — 100 км от черты города, в котором есть фирменный салон.
              </p>
            </div>
          </div>

          {/* Assembly section */}
          <div className="bg-[#f9f9fa] rounded-lg p-6">
            <div className="flex items-center gap-3 mb-6">
              <div className="w-12 h-12 rounded-full bg-[#62bb46] flex items-center justify-center">
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h2 className="text-2xl font-bold text-[#3d4543]">Сборка</h2>
            </div>

            <div className="space-y-4">
              <div className="bg-white rounded-lg p-4">
                <h3 className="font-bold text-[#3d4543] mb-2">Выезд сборщика</h3>
                <p className="text-gray-600 text-sm mb-2">По городу нахождения салона</p>
                <p className="text-2xl font-bold text-[#62bb46]">Бесплатно</p>
                <p className="text-xs text-gray-500 mt-1">В близлежащие населённые пункты: 10 ₽/км</p>
              </div>

              <div className="bg-white rounded-lg p-4">
                <h3 className="font-bold text-[#3d4543] mb-3">Стоимость сборки</h3>
                <ul className="space-y-2 text-sm">
                  <li className="flex justify-between">
                    <span className="text-gray-600">Сборка гардеробной системы</span>
                    <span className="font-bold text-[#3d4543]">300 ₽/секция</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-gray-600">Установка внешней стоевой</span>
                    <span className="font-bold text-[#3d4543]">300 ₽</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-gray-600">Установка дверей-купе (с наполнением)</span>
                    <span className="font-bold text-[#3d4543]">200 ₽/дверь</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-gray-600">Установка встроенных дверей-купе</span>
                    <span className="font-bold text-[#3d4543]">700 ₽/дверь</span>
                  </li>
                  <li className="flex justify-between">
                    <span className="text-gray-600">Сборка корпусного заказного шкафа</span>
                    <span className="font-bold text-[#3d4543]">400 ₽/секция</span>
                  </li>
                </ul>
              </div>

              <div className="bg-white rounded-lg p-4">
                <h3 className="font-bold text-[#3d4543] mb-2">Срочная сборка</h3>
                <p className="text-gray-600 text-sm mb-2">Всех заказных позиций в день доставки</p>
                <p className="text-2xl font-bold text-[#62bb46]">15%</p>
                <p className="text-xs text-gray-500">от стоимости комплекта мебели</p>
              </div>

              <p className="text-sm text-gray-500">
                Льготная сборка оказывается в течение 3-х рабочих дней после доставки.
              </p>
            </div>
          </div>
        </div>

        {/* Additional info */}
        <div className="mt-12 grid md:grid-cols-3 gap-6">
          <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
            <div className="w-16 h-16 rounded-full bg-[#62bb46]/10 flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 className="font-bold text-[#3d4543] mb-2">700+ городов</h3>
            <p className="text-sm text-gray-600">Доставка по всей России</p>
          </div>

          <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
            <div className="w-16 h-16 rounded-full bg-[#62bb46]/10 flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 className="font-bold text-[#3d4543] mb-2">От 3-х дней</h3>
            <p className="text-sm text-gray-600">Минимальные сроки доставки</p>
          </div>

          <div className="text-center p-6 bg-[#f9f9fa] rounded-lg">
            <div className="w-16 h-16 rounded-full bg-[#62bb46]/10 flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 className="font-bold text-[#3d4543] mb-2">Гарантия 10 лет</h3>
            <p className="text-sm text-gray-600">На всю продукцию</p>
          </div>
        </div>

        {/* Map section */}
        <div className="mt-12">
          <h2 className="text-2xl font-bold text-[#3d4543] mb-6 text-center">География доставки</h2>
          <div className="bg-[#f9f9fa] rounded-lg p-6 text-center">
            <p className="text-gray-600 mb-4">
              Фирменные салоны Е1 расположены в 20 регионах России. Посмотрите на карте ближайший к вам салон.
            </p>
            <a
              href="https://yandex.ru/maps/?um=constructor%3A46ae22e1bae65db44f0e2e71f21b758fcae2ed2c9e8b8c8e5c1c2c5c1c2c5c1c"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 bg-[#62bb46] text-white px-6 py-3 rounded font-bold hover:bg-[#55a83d] transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
              </svg>
              Открыть карту салонов
            </a>
          </div>
        </div>

        {/* Contact section */}
        <div className="mt-12 bg-[#3d4543] rounded-lg p-8 text-white">
          <h2 className="text-2xl font-bold mb-6 text-center">Остались вопросы?</h2>
          <div className="grid md:grid-cols-2 gap-6 max-w-2xl mx-auto">
            <div className="text-center">
              <p className="text-white/60 text-sm mb-1">Отдел продаж</p>
              <a href="mailto:best@e-1.ru" className="text-lg font-bold hover:text-[#62bb46] transition-colors">
                best@e-1.ru
              </a>
            </div>
            <div className="text-center">
              <p className="text-white/60 text-sm mb-1">Служба клиентского сервиса</p>
              <a href="mailto:service@e-1.ru" className="text-lg font-bold hover:text-[#62bb46] transition-colors">
                service@e-1.ru
              </a>
            </div>
          </div>
          <p className="text-center text-white/60 text-sm mt-6">
            Вы всегда можете уточнить стоимость доставки у менеджеров, позвонив по номеру телефона или написав в чат.
          </p>
        </div>
      </div>
    </div>
  );
}
