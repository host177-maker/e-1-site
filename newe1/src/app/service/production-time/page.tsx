import type { Metadata } from "next";
import Image from "next/image";

export const metadata: Metadata = {
  title: "Сроки изготовления | E-1.RU",
  description: "Узнайте сроки изготовления шкафов-купе и гардеробных в компании Е1. Производство от 10 рабочих дней.",
};

export default function ProductionTimePage() {
  const timelineSteps = [
    {
      days: "1-2 дня",
      title: "Обработка заказа",
      description: "Проверка заказа, подготовка документации и передача в производство.",
    },
    {
      days: "7-10 дней",
      title: "Производство",
      description: "Изготовление всех компонентов шкафа на современном оборудовании.",
    },
    {
      days: "1-3 дня",
      title: "Контроль качества",
      description: "Проверка готовой продукции и упаковка для транспортировки.",
    },
    {
      days: "1-5 дней",
      title: "Доставка",
      description: "Транспортировка до вашего города и согласование времени доставки.",
    },
  ];

  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-12">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-4">
            Сроки изготовления
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl">
            Мы ценим ваше время и делаем всё возможное, чтобы вы получили свой идеальный шкаф в кратчайшие сроки.
          </p>
        </div>
      </div>

      {/* Main info section */}
      <div className="container-custom py-12">
        <div className="grid md:grid-cols-2 gap-8 mb-12">
          {/* Image placeholder */}
          <div className="relative h-[300px] md:h-[400px] rounded-lg overflow-hidden bg-[#f5f5f5]">
            <Image
              src="/images/production.jpg"
              alt="Производство шкафов Е1"
              fill
              className="object-cover"
              onError={(e) => {
                const target = e.target as HTMLImageElement;
                target.style.display = 'none';
              }}
            />
            <div className="absolute inset-0 flex items-center justify-center bg-[#f5f5f5]">
              <div className="text-center text-gray-400">
                <svg className="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <p>Производство Е1</p>
              </div>
            </div>
          </div>

          {/* Main times */}
          <div className="space-y-6">
            <div className="bg-[#62bb46] text-white rounded-lg p-6">
              <div className="text-4xl font-bold mb-2">10-14</div>
              <div className="text-lg">рабочих дней</div>
              <p className="text-white/80 mt-2 text-sm">
                Стандартный срок изготовления шкафа-купе
              </p>
            </div>

            <div className="bg-[#f9f9fa] rounded-lg p-6">
              <h3 className="font-bold text-[#3d4543] mb-3">Что влияет на сроки:</h3>
              <ul className="space-y-2 text-gray-600">
                <li className="flex items-start gap-2">
                  <svg className="w-5 h-5 text-[#62bb46] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  Сложность конструкции и наполнения
                </li>
                <li className="flex items-start gap-2">
                  <svg className="w-5 h-5 text-[#62bb46] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  Тип материалов и фурнитуры
                </li>
                <li className="flex items-start gap-2">
                  <svg className="w-5 h-5 text-[#62bb46] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  Наличие фотопечати на фасадах
                </li>
                <li className="flex items-start gap-2">
                  <svg className="w-5 h-5 text-[#62bb46] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  Удалённость региона доставки
                </li>
              </ul>
            </div>
          </div>
        </div>

        {/* Timeline section */}
        <div className="mb-12">
          <h2 className="text-2xl font-bold text-[#3d4543] mb-6">Этапы выполнения заказа</h2>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {timelineSteps.map((step, index) => (
              <div key={index} className="relative">
                <div className="bg-white border border-gray-200 rounded-lg p-5 h-full hover:border-[#62bb46] hover:shadow-md transition-all">
                  <div className="text-[#62bb46] font-bold text-lg mb-1">{step.days}</div>
                  <h3 className="font-bold text-[#3d4543] mb-2">{step.title}</h3>
                  <p className="text-sm text-gray-600">{step.description}</p>
                </div>
                {index < timelineSteps.length - 1 && (
                  <div className="hidden lg:block absolute top-1/2 -right-2 transform -translate-y-1/2 text-gray-300">
                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Additional info */}
        <div className="grid md:grid-cols-2 gap-6 mb-12">
          <div className="bg-[#f9f9fa] rounded-lg p-6">
            <h3 className="font-bold text-[#3d4543] mb-3 flex items-center gap-2">
              <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              Срочное изготовление
            </h3>
            <p className="text-gray-600 mb-3">
              Для срочных заказов мы предлагаем ускоренное производство. Срок изготовления может быть сокращён до 5-7 рабочих дней.
            </p>
            <p className="text-sm text-gray-500">
              *Услуга доступна для стандартных моделей. Уточняйте возможность у менеджера.
            </p>
          </div>

          <div className="bg-[#f9f9fa] rounded-lg p-6">
            <h3 className="font-bold text-[#3d4543] mb-3 flex items-center gap-2">
              <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Доставка в регионы
            </h3>
            <p className="text-gray-600 mb-3">
              Для удалённых регионов общий срок от заказа до доставки может составлять до 45 дней, включая время на транспортировку.
            </p>
            <p className="text-sm text-gray-500">
              *23 региональных склада позволяют сократить сроки доставки.
            </p>
          </div>
        </div>

        {/* Tracking section */}
        <div className="bg-[#3d4543] text-white rounded-lg p-8 text-center">
          <h3 className="text-xl font-bold mb-3">Отслеживайте статус вашего заказа</h3>
          <p className="text-gray-300 mb-5 max-w-lg mx-auto">
            Вы всегда можете узнать, на каком этапе находится ваш заказ, воспользовавшись онлайн-сервисом отслеживания.
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

        {/* Contact section */}
        <div className="mt-12 text-center">
          <p className="text-gray-600 mb-3">
            Остались вопросы о сроках? Свяжитесь с нами!
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
