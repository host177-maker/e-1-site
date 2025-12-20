'use client';

import { useEffect } from 'react';
import Script from 'next/script';

export default function DeliveryPage() {
  useEffect(() => {
    // Инициализация карты после загрузки API
    const initMap = () => {
      if (typeof window !== 'undefined' && (window as any).ymaps) {
        const ymaps = (window as any).ymaps;
        ymaps.ready(() => {
          const map = new ymaps.Map('delivery-map', {
            center: [37.617644, 55.755819],
            zoom: 4,
            controls: ['zoomControl', 'geolocationControl', 'searchControl', 'fullscreenControl'],
          }, {
            searchControlProvider: 'yandex#search',
          });

          map.controls.get('zoomControl').options.set({ size: 'small' });
          map.controls.get('searchControl').options.set({
            noPlacemark: true,
            placeholderContent: 'Введите адрес доставки'
          });

          // Загружаем GeoJSON с зонами доставки
          fetch('/upload/delivery-locations.geojson')
            .then(res => res.json())
            .then(geoJson => {
              ymaps.geoQuery(geoJson).addToMap(map).each((obj: any) => {
                obj.options.set({
                  fillColor: obj.properties.get('fill'),
                  fillOpacity: obj.properties.get('fill-opacity'),
                  strokeColor: obj.properties.get('stroke'),
                  strokeWidth: obj.properties.get('stroke-width'),
                  strokeOpacity: obj.properties.get('stroke-opacity'),
                });
              });
            })
            .catch(() => {
              // Если файл не найден, добавляем базовые метки городов
              const cities = [
                { name: 'Москва', coords: [37.617644, 55.755819] },
                { name: 'Санкт-Петербург', coords: [30.315868, 59.939095] },
                { name: 'Краснодар', coords: [38.975313, 45.035470] },
                { name: 'Ростов-на-Дону', coords: [39.701505, 47.235713] },
                { name: 'Екатеринбург', coords: [60.597474, 56.838011] },
                { name: 'Новосибирск', coords: [82.920430, 55.030199] },
                { name: 'Казань', coords: [49.108891, 55.796127] },
                { name: 'Нижний Новгород', coords: [43.936059, 56.326887] },
                { name: 'Самара', coords: [50.221243, 53.195878] },
                { name: 'Воронеж', coords: [39.200269, 51.660781] },
                { name: 'Волгоград', coords: [44.516939, 48.707067] },
                { name: 'Уфа', coords: [55.958736, 54.735152] },
                { name: 'Челябинск', coords: [61.402554, 55.160026] },
                { name: 'Пермь', coords: [56.229398, 58.010455] },
                { name: 'Саратов', coords: [46.034158, 51.533557] },
                { name: 'Тюмень', coords: [68.970682, 57.153033] },
                { name: 'Омск', coords: [73.368212, 54.989347] },
                { name: 'Красноярск', coords: [92.852572, 56.010563] },
                { name: 'Ставрополь', coords: [41.977418, 45.044820] },
                { name: 'Сочи', coords: [39.730099, 43.585525] },
              ];

              cities.forEach(city => {
                const placemark = new ymaps.Placemark(city.coords, {
                  hintContent: city.name,
                  balloonContent: `<strong>${city.name}</strong><br/>Фирменный салон Е1`,
                }, {
                  preset: 'islands#greenDotIcon',
                });
                map.geoObjects.add(placemark);
              });
            });
        });
      }
    };

    // Проверяем, загружен ли уже API
    if ((window as any).ymaps) {
      initMap();
    } else {
      (window as any).initYandexMap = initMap;
    }
  }, []);

  return (
    <>
      <Script
        src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=51e35aa4-fa5e-432e-a7c6-e5e71105ec3a"
        strategy="afterInteractive"
        onLoad={() => {
          if ((window as any).initYandexMap) {
            (window as any).initYandexMap();
          }
        }}
      />

      <div className="bg-white">
        {/* Hero section */}
        <div className="bg-[#f5f5f5] py-8">
          <div className="container-custom">
            <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-4">
              Доставка и сборка
            </h1>
          </div>
        </div>

        {/* Main content */}
        <div className="container-custom py-8">
          {/* Map section */}
          <div className="mb-10">
            <h2 className="text-2xl font-bold text-[#3d4543] mb-4">Доставка</h2>
            <div
              id="delivery-map"
              className="w-full h-[400px] md:h-[500px] rounded-lg border border-gray-200"
            />
          </div>

          {/* Content sections */}
          <div className="space-y-8">
            <div>
              <h3 className="text-xl font-bold text-[#3d4543] mb-3">Чем сильнее любовь, тем ближе к клиенту.</h3>
              <p className="text-gray-600 leading-relaxed">
                Мы стараемся быть максимально доступными для наших покупателей, и именно поэтому каждый день расширяем географию доставки компании Е1.<br />
                Независимо от того, где вы находитесь — в большом мегаполисе или уютном небольшом городке — мы делаем всё, чтобы ваш новый шкаф был доставлен быстро и надежно.<br />
                Наша команда профессионалов заботится о каждом этапе: от упаковки и транспортировки до установки на месте.
              </p>
            </div>

            <div>
              <h3 className="text-xl font-bold text-[#3d4543] mb-3">Что же мы делаем для того, чтобы вы смогли получить нашу продукцию за 24 часа?</h3>
              <p className="text-gray-600 leading-relaxed">
                Мы осуществляем доставку товаров Е1 в более чем 700 городов. Наша компания не останавливается на достигнутом, поэтому уже в 2024 году мы добавили 215 населенных пунктов.<br />
                Благодаря 23 региональным складам, которые расположены по всей стране, мы можем радовать своих клиентов и доставлять им продукцию Е1 от 24-х часов*.
              </p>
            </div>

            <div>
              <h3 className="text-xl font-bold text-[#3d4543] mb-3">Как можно узнать, доставляют ли шкафы в ваш город:</h3>
              <ol className="list-decimal list-inside space-y-2 text-gray-600">
                <li>На карте вы можете увидеть города и регионы, куда мы доставляем нашу продукцию.</li>
                <li>Перед оформлением заказа в корзине предлагаем воспользоваться калькулятором, чтобы узнать стоимость доставки с учётом удалённости вашего адреса от склада отгрузки.</li>
                <li>Вы всегда сможете уточнить стоимость доставки у наших менеджеров. Позвоните по номеру телефона или напишите в чат.</li>
              </ol>
            </div>

            <div>
              <h3 className="text-xl font-bold text-[#3d4543] mb-3">Вам уже привезли шкаф?</h3>
              <p className="text-gray-600 leading-relaxed">
                Грузчики компании Е1 осуществят подъем шкафа на необходимый этаж, а собственная служба сборки предоставит опытного специалиста, который приедет к Вам в согласованное время и произведет сборку шкафа.
              </p>
            </div>

            <div>
              <h3 className="text-xl font-bold text-[#3d4543] mb-3">Что, если Вы хотите забрать шкаф самостоятельно?</h3>
              <p className="text-gray-600 leading-relaxed">
                Для Вашего удобства мы предлагаем услугу самовывоза нашей продукции из любого склада Е1.<br />
                При оформлении самовывоза Вы также можете заказать у нас услугу сборки.
              </p>
            </div>

            <div className="bg-[#f9f9fa] rounded-lg p-6">
              <p className="text-gray-700 font-medium mb-2">
                Если у Вас остались вопросы, просто свяжитесь с нашей службой клиентского сервиса — мы всегда готовы помочь вам!
              </p>
              <a href="tel:+78001001211" className="text-xl font-bold text-[#62bb46] hover:underline">
                8-800-100-12-11
              </a>
              <p className="text-sm text-gray-500 mt-4">
                *Для удаленных регионов срок доставки может быть увеличен.<br />
                *Стоимость услуг доставки и сборки вы сможете узнать при оформлении заказа.
              </p>
            </div>
          </div>

          {/* Video section */}
          <div className="mt-12">
            <h2 className="text-2xl font-bold text-[#3d4543] mb-6">Смотрите видео о сборке наших шкафов-купе</h2>

            <div className="grid md:grid-cols-2 gap-6">
              <div className="bg-[#f9f9fa] rounded-lg p-4">
                <h4 className="font-bold text-[#3d4543] mb-3">Серия «Экспресс» и «Оптим»</h4>
                <div className="aspect-video">
                  <iframe
                    className="w-full h-full rounded"
                    src="https://www.youtube-nocookie.com/embed/lP-xVdZDNsw"
                    title="Сборка серии Экспресс и Оптим"
                    frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen
                  />
                </div>
              </div>

              <div className="bg-[#f9f9fa] rounded-lg p-4">
                <h4 className="font-bold text-[#3d4543] mb-3">Серия «Эста»</h4>
                <div className="aspect-video">
                  <iframe
                    className="w-full h-full rounded"
                    src="https://www.youtube-nocookie.com/embed/W_5krAAgKkU"
                    title="Сборка серии Эста"
                    frameBorder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen
                  />
                </div>
              </div>

              <div className="bg-[#f9f9fa] rounded-lg p-4">
                <h4 className="font-bold text-[#3d4543] mb-3">Серия «Локер» с системой открывания WingLine L</h4>
                <div className="aspect-video">
                  <iframe
                    className="w-full h-full rounded"
                    src="https://rutube.ru/play/embed/63dd5a22fae35685f3ffe282d55e550e"
                    title="Сборка серии Локер WingLine L"
                    frameBorder="0"
                    allow="clipboard-write; autoplay"
                    allowFullScreen
                  />
                </div>
              </div>

              <div className="bg-[#f9f9fa] rounded-lg p-4">
                <h4 className="font-bold text-[#3d4543] mb-3">Серия «Локер» с системой открывания Push to Move</h4>
                <div className="aspect-video">
                  <iframe
                    className="w-full h-full rounded"
                    src="https://rutube.ru/play/embed/227cbb6a3e6d7e2bc49c33489645c56a"
                    title="Сборка серии Локер Push to Move"
                    frameBorder="0"
                    allow="clipboard-write; autoplay"
                    allowFullScreen
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Delivery and Assembly pricing */}
          <div className="mt-12 grid lg:grid-cols-2 gap-8">
            {/* Delivery section */}
            <div className="bg-[#f9f9fa] rounded-lg p-6">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-12 h-12 rounded-full bg-[#62bb46] flex items-center justify-center">
                  <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                  </svg>
                </div>
                <h2 className="text-2xl font-bold text-[#3d4543]">Стоимость доставки</h2>
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
                <h2 className="text-2xl font-bold text-[#3d4543]">Стоимость сборки</h2>
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
    </>
  );
}
