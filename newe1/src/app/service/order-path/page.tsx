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

      {/* Check order status button */}
      <div className="container-custom py-12 text-center">
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
  );
}
