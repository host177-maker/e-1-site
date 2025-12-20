import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Путь заказа | Е1.RU",
  description: "Узнайте, как происходит процесс заказа шкафа-купе в компании Е1: от оформления до доставки и сборки.",
};

export default function OrderPathPage() {
  const steps = [
    {
      number: 1,
      title: "Оформление заказа",
      description: "Вы оформляете заказ в салоне или на сайте. Наши специалисты помогут подобрать оптимальную конфигурацию шкафа под ваши потребности и размеры помещения.",
      icon: (
        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
      ),
    },
    {
      number: 2,
      title: "Производство",
      description: "Производственная система автоматически проверяет, что требуется произвести для исполнения заказа, а что берётся из складских остатков. Все заказы автоматически передаются на станки и рабочие места исполнителей на мебельном производстве. Так мы практически исключили ошибки при выполнении заказов.",
      icon: (
        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
      ),
    },
    {
      number: 3,
      title: "Отгрузка",
      description: "После изготовления всех компонентов заказа они передаются на региональный склад для подготовки к доставке.",
      icon: (
        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
        </svg>
      ),
    },
    {
      number: 4,
      title: "Согласование доставки",
      description: "С вами связываются сотрудники клиентского сервиса и согласовывают удобную дату и время доставки.",
      icon: (
        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
        </svg>
      ),
    },
    {
      number: 5,
      title: "Информирование",
      description: "Вам отправляется СМС с контактными данными водителя и временем прибытия. У нас работает служба СМС и e-mail информирования о заказе, доставке и сборке.",
      icon: (
        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
      ),
    },
    {
      number: 6,
      title: "Доставка и сборка",
      description: "Грузчики компании Е1 осуществят подъём шкафа на необходимый этаж, а собственная служба сборки предоставит опытного специалиста, который приедет в согласованное время и произведёт сборку шкафа.",
      icon: (
        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
      ),
    },
  ];

  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-12">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-4">
            Путь заказа
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl">
            Узнайте, как происходит процесс заказа шкафа-купе в компании Е1 — от оформления до доставки и профессиональной сборки.
          </p>
        </div>
      </div>

      {/* Steps section */}
      <div className="container-custom py-12">
        <div className="space-y-8">
          {steps.map((step, index) => (
            <div
              key={step.number}
              className={`flex flex-col md:flex-row gap-6 items-start p-6 rounded-lg ${
                index % 2 === 0 ? "bg-white" : "bg-[#f9f9fa]"
              }`}
            >
              {/* Step number and icon */}
              <div className="flex items-center gap-4 shrink-0">
                <div className="w-14 h-14 rounded-full bg-[#62bb46] text-white flex items-center justify-center text-2xl font-bold">
                  {step.number}
                </div>
                <div className="text-[#62bb46] md:hidden">
                  {step.icon}
                </div>
              </div>

              {/* Content */}
              <div className="flex-1">
                <h2 className="text-xl font-bold text-[#3d4543] mb-2 flex items-center gap-3">
                  <span className="hidden md:block text-[#62bb46]">{step.icon}</span>
                  {step.title}
                </h2>
                <p className="text-gray-600 leading-relaxed">
                  {step.description}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Benefits section */}
      <div className="bg-[#62bb46] text-white py-12">
        <div className="container-custom">
          <h2 className="text-2xl font-bold mb-8 text-center">Наши преимущества</h2>
          <div className="grid md:grid-cols-3 gap-8">
            <div className="text-center">
              <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h3 className="font-bold text-lg mb-2">Гарантия до 10 лет</h3>
              <p className="text-white/80">Мы уверены в качестве нашей продукции</p>
            </div>
            <div className="text-center">
              <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h3 className="font-bold text-lg mb-2">Доставка от 3-х дней</h3>
              <p className="text-white/80">Быстрое изготовление и доставка</p>
            </div>
            <div className="text-center">
              <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h3 className="font-bold text-lg mb-2">20 регионов России</h3>
              <p className="text-white/80">Фирменные салоны по всей стране</p>
            </div>
          </div>
        </div>
      </div>

      {/* Online tracking */}
      <div className="container-custom py-12">
        <div className="bg-[#f9f9fa] rounded-lg p-8 flex flex-col md:flex-row items-center gap-6">
          <div className="shrink-0">
            <div className="w-20 h-20 rounded-full bg-[#62bb46]/10 flex items-center justify-center">
              <svg className="w-10 h-10 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <div className="flex-1 text-center md:text-left">
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">
              Отслеживание заказа онлайн
            </h3>
            <p className="text-gray-600 mb-4">
              Е1 является единственным производителем мебели, публикующим в онлайн-режиме статистику выполнения текущих заказов. Вы всегда можете проверить статус своего заказа.
            </p>
            <a
              href="https://booking.e-1.ru/check/"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-2 bg-[#62bb46] text-white px-6 py-3 rounded font-bold hover:bg-[#55a83d] transition-colors"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              Проверить статус заказа
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
