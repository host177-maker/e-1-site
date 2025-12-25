/* eslint-disable @typescript-eslint/no-explicit-any */
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
        </div>
      </div>
    </>
  );
}
