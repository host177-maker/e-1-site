/* eslint-disable @typescript-eslint/no-explicit-any */
'use client';

import { useEffect } from 'react';
import Script from 'next/script';

export default function DeliveryMapPage() {
  useEffect(() => {
    // Инициализация карты после загрузки API
    const initMap = () => {
      if (typeof window !== 'undefined' && (window as any).ymaps) {
        const ymaps = (window as any).ymaps;
        ymaps.ready(() => {
          const map = new ymaps.Map('map', {
            center: [37.617644, 55.755819],
            zoom: 4,
            controls: ['zoomControl', 'geolocationControl', 'searchControl', 'fullscreenControl'],
          }, {
            searchControlProvider: 'yandex#search',
          });

          map.controls.get('zoomControl').options.set({ size: 'small' });
          map.controls.get('searchControl').options.set({
            noPlacemark: true,
            placeholderContent: 'Введите ваш город и адрес'
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
            <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543]">
              География доставки
            </h1>
          </div>
        </div>

        {/* Main content */}
        <div className="container-custom py-8">
          {/* Intro text */}
          <p className="text-gray-600 leading-relaxed mb-6 text-lg">
            Введите в поисковой строке карты ваш город и адрес — калькулятор рассчитает стоимость доставки
          </p>

          {/* Map section */}
          <div className="mb-10">
            <div
              id="map"
              className="map w-full h-[400px] md:h-[500px] rounded-lg border border-gray-200"
            />
          </div>

          {/* Content sections */}
          <div className="space-y-6">
            <div>
              <h2 className="text-xl font-bold text-[#3d4543] mb-3">
                Как можно узнать, доставляют ли шкафы в ваш город:
              </h2>
              <p className="text-gray-600 leading-relaxed">
                На карте вы можете увидеть города и регионы, куда мы доставляем нашу продукцию.
              </p>
              <p className="text-gray-600 leading-relaxed mt-3">
                Перед оформлением заказа в корзине предлагаем воспользоваться калькулятором, чтобы узнать стоимость доставки с учётом удалённости вашего адреса от склада отгрузки.
              </p>
              <p className="text-gray-600 leading-relaxed mt-3">
                Если что-то непонятно или не получилось рассчитать — напишите нам или позвоните.
              </p>
            </div>

          </div>
        </div>
      </div>
    </>
  );
}
