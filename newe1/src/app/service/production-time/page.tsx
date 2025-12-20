import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Сроки изготовления | E-1.RU",
  description: "Узнайте сроки изготовления шкафов-купе и гардеробных в компании Е1. Один из самых низких процентов рекламаций в отрасли.",
};

export default function ProductionTimePage() {
  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-12">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-4">
            Сроки изготовления
          </h1>
        </div>
      </div>

      {/* Main content */}
      <div className="container-custom py-10">
        {/* Intro text */}
        <div className="mb-8">
          <p className="text-gray-700 leading-relaxed mb-4">
            При выборе мебели большинство клиентов задается вопросом пунктуальности производителя. Читая в интернете отзывы о любой компании, производящей мебель, легко увидеть достаточно большое число отзывов о срыве сроков поставки и других задержках. И действительно — чем крупнее компания, тем больше отрицательных отзывов в сети. А если присутствуют много положительных, то несложно увидеть, что некоторые из них очень похожи и кажутся «проплаченными». Получается, что когда привозят все ровно и в срок, клиент не оставляет никаких отзывов, а когда присутствуют в работе какие-то недочеты, он сразу спешит этим поделиться в интернет-сообществе.
          </p>
        </div>

        {/* Quote block */}
        <div className="border-l-4 border-[#62bb46] bg-[#f9f9fa] p-6 mb-10">
          <p className="text-lg text-[#3d4543] italic">
            Если привозят все в срок, то клиент не оставляет никаких отзывов, а когда присутствуют в работе недочеты, он сразу спешит этим поделиться в интернет-сообществе
          </p>
        </div>

        {/* Statistics intro */}
        <div className="mb-8">
          <p className="text-gray-700 leading-relaxed mb-4">
            По статистике около 70% всей мебели, от шкафа до дивана, производится по заказу клиента. И получается, что над вашим изделием работает в среднем 20 сотрудников мебельной фабрики, также задействованы еще 5-10 поставщиков материалов и комплектующих. Сбой в работе одного из звеньев может приводить к задержке сроков или появлению брака.
          </p>
          <p className="text-gray-700 leading-relaxed">
            И даже если ваши потребности входят в оставшиеся 30% и удовлетворены складскими позициями, то над вашим изделием работают 3 звена: склад, служба доставки и сборщики.
          </p>
        </div>

        {/* Image and stats section */}
        <div className="grid md:grid-cols-2 gap-8 mb-10">
          {/* Production image */}
          <div className="relative h-[280px] rounded-lg overflow-hidden bg-[#f5f5f5]">
            <Image
              src="/images/production-line.jpg"
              alt="Производственная линия Е1"
              fill
              className="object-cover"
              onError={(e) => {
                const target = e.target as HTMLImageElement;
                target.style.display = 'none';
              }}
            />
            <div className="absolute inset-0 flex items-center justify-center">
              <div className="text-center p-4">
                <svg className="w-16 h-16 mx-auto mb-2 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <p className="text-gray-500 text-sm">Производственная линия</p>
              </div>
            </div>
          </div>

          {/* Stats */}
          <div>
            <h2 className="text-2xl font-bold text-[#3d4543] mb-6">
              Над вашим изделием работает в среднем:
            </h2>
            <div className="grid grid-cols-2 gap-6">
              <div className="text-center p-4 bg-[#f9f9fa] rounded-lg">
                <div className="text-5xl font-bold text-[#62bb46] mb-2">20</div>
                <div className="text-sm text-gray-600">
                  сотрудников мебельной<br />фабрики
                </div>
              </div>
              <div className="text-center p-4 bg-[#f9f9fa] rounded-lg">
                <div className="text-5xl font-bold text-[#62bb46] mb-2">5-10</div>
                <div className="text-sm text-gray-600">
                  поставщиков материалов и<br />комплектующих
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Honesty block */}
        <div className="mb-10">
          <p className="text-gray-700 leading-relaxed">
            Честно скажем: не ошибается тот, кто ничего не делает. Но в «Е1» мы снизили процент рекламаций до одного из самых низких в мебельной отрасли.
          </p>
        </div>

        {/* Claims rate section */}
        <div className="bg-[#f9f9fa] rounded-lg p-8 mb-10">
          <div className="md:flex md:items-center md:gap-8">
            <div className="mb-6 md:mb-0 md:flex-1">
              <h2 className="text-2xl font-bold text-[#3d4543] mb-2">
                У «Е1» один из самых низких процентов рекламаций по доставке в отрасли
              </h2>
            </div>
            <div className="text-center md:text-left">
              <div className="text-6xl font-bold text-[#62bb46]">1.64%</div>
              <div className="text-gray-600">от общего числа доставок</div>
            </div>
          </div>
        </div>

        {/* Online stats */}
        <div className="border-t border-gray-200 pt-8 mb-10">
          <p className="text-gray-700 leading-relaxed">
            И на сегодня мы единственный производитель мебели, публикующий в{' '}
            <Link href="https://booking.e-1.ru/check/" target="_blank" rel="noopener noreferrer" className="text-[#62bb46] hover:underline">
              онлайн-режиме
            </Link>
            {' '}статистику выполнения текущих заказов.
          </p>
        </div>

        {/* CTA section */}
        <div className="bg-[#3d4543] text-white rounded-lg p-8 text-center">
          <h3 className="text-xl font-bold mb-3">Проверьте статус вашего заказа</h3>
          <p className="text-gray-300 mb-5 max-w-lg mx-auto">
            Отслеживайте выполнение заказа в реальном времени
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
