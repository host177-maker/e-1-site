/* eslint-disable @typescript-eslint/no-explicit-any */
'use client';

import { useEffect, useState, useRef, useCallback } from 'react';
import Script from 'next/script';
import { useCity } from '@/context/CityContext';

interface DeliveryPoint {
  id: number;
  name: string;
  coordinates: [number, number];
}

// Simple function to convert city name to dative case (for common Russian cities)
function toDativeCase(city: string): string {
  const dativeMap: Record<string, string> = {
    'Москва': 'Москве',
    'Санкт-Петербург': 'Санкт-Петербурге',
    'Краснодар': 'Краснодаре',
    'Ростов-на-Дону': 'Ростове-на-Дону',
    'Екатеринбург': 'Екатеринбурге',
    'Новосибирск': 'Новосибирске',
    'Казань': 'Казани',
    'Нижний Новгород': 'Нижнем Новгороде',
    'Самара': 'Самаре',
    'Воронеж': 'Воронеже',
    'Волгоград': 'Волгограде',
    'Уфа': 'Уфе',
    'Челябинск': 'Челябинске',
    'Пермь': 'Перми',
    'Саратов': 'Саратове',
    'Тюмень': 'Тюмени',
    'Омск': 'Омске',
    'Красноярск': 'Красноярске',
    'Ставрополь': 'Ставрополе',
    'Сочи': 'Сочи',
    'Тольятти': 'Тольятти',
    'Барнаул': 'Барнауле',
    'Иркутск': 'Иркутске',
    'Хабаровск': 'Хабаровске',
    'Владивосток': 'Владивостоке',
    'Ярославль': 'Ярославле',
    'Махачкала': 'Махачкале',
    'Томск': 'Томске',
    'Оренбург': 'Оренбурге',
    'Кемерово': 'Кемерово',
    'Новокузнецк': 'Новокузнецке',
    'Рязань': 'Рязани',
    'Астрахань': 'Астрахани',
    'Набережные Челны': 'Набережных Челнах',
    'Пенза': 'Пензе',
    'Липецк': 'Липецке',
    'Киров': 'Кирове',
    'Тула': 'Туле',
    'Калуга': 'Калуге',
    'Курск': 'Курске',
    'Ульяновск': 'Ульяновске',
    'Брянск': 'Брянске',
    'Иваново': 'Иваново',
    'Калининград': 'Калининграде',
    'Белгород': 'Белгороде',
  };

  return dativeMap[city] || city;
}

interface Prices {
  delivery_base_price: number;
  delivery_per_km: number;
  floor_lift_price: number;
  elevator_lift_price: number;
  assembly_per_km: number;
}

interface DeliveryResult {
  nearestPoint: DeliveryPoint;
  distance: number;
  cost: number;
  regionName: string;
}

// Calculate distance between two points using Haversine formula
function haversineDistance(
  coord1: [number, number],
  coord2: [number, number]
): number {
  const toRad = (x: number) => (x * Math.PI) / 180;
  const R = 6371;

  const [lon1, lat1] = coord1;
  const [lon2, lat2] = coord2;

  const dLat = toRad(lat2 - lat1);
  const dLon = toRad(lon2 - lon1);

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

function findNearestPoint(
  targetCoords: [number, number],
  points: DeliveryPoint[]
): DeliveryPoint | null {
  if (!points.length) return null;

  let nearest = points[0];
  let minDist = haversineDistance(targetCoords, points[0].coordinates);

  for (const point of points) {
    const dist = haversineDistance(targetCoords, point.coordinates);
    if (dist < minDist) {
      minDist = dist;
      nearest = point;
    }
  }

  return nearest;
}

export default function DeliveryMapPage() {
  const { city } = useCity();
  const [deliveryPoints, setDeliveryPoints] = useState<DeliveryPoint[]>([]);
  const [prices, setPrices] = useState<Prices | null>(null);
  const [deliveryResult, setDeliveryResult] = useState<DeliveryResult | null>(null);
  const [searchAddress, setSearchAddress] = useState('');
  const [isCalculating, setIsCalculating] = useState(false);

  const mapRef = useRef<any>(null);
  const routeRef = useRef<any>(null);
  const destinationMarkerRef = useRef<any>(null);
  const resultPopupRef = useRef<HTMLDivElement>(null);
  const calculateDeliveryRef = useRef<((coords: [number, number], address: string) => void) | null>(null);

  // Load delivery points (once)
  useEffect(() => {
    const loadPoints = async () => {
      try {
        const pointsRes = await fetch('/api/delivery-points');
        const pointsData = await pointsRes.json();

        if (pointsData.success) {
          setDeliveryPoints(pointsData.points);
        }
      } catch (error) {
        console.error('Error loading delivery points:', error);
      }
    };

    loadPoints();
  }, []);

  // Load prices based on city (reload when city changes)
  useEffect(() => {
    const loadPrices = async () => {
      try {
        // Pass city name to get region-specific prices
        const pricesRes = await fetch(`/api/e1admin/service-prices?region=${encodeURIComponent(city.name)}`);
        const pricesData = await pricesRes.json();

        if (pricesData.success && pricesData.data.length > 0) {
          const priceData = pricesData.data[0];
          setPrices({
            delivery_base_price: parseFloat(priceData.delivery_base_price) || 0,
            delivery_per_km: parseFloat(priceData.delivery_per_km) || 0,
            floor_lift_price: parseFloat(priceData.floor_lift_price) || 0,
            elevator_lift_price: parseFloat(priceData.elevator_lift_price) || 0,
            assembly_per_km: parseFloat(priceData.assembly_per_km) || 0,
          });
        }
      } catch (error) {
        console.error('Error loading prices:', error);
      }
    };

    loadPrices();
  }, [city.name]);

  // Initialize map
  const initMap = useCallback(() => {
    if (typeof window === 'undefined' || !(window as any).ymaps) return;
    if (mapRef.current) return; // Already initialized

    const ymaps = (window as any).ymaps;

    ymaps.ready(() => {
      const map = new ymaps.Map('map', {
        center: [37.617644, 55.755819],
        zoom: 4,
        controls: ['zoomControl', 'geolocationControl', 'fullscreenControl'],
      });

      mapRef.current = map;

      // Add search control with custom behavior
      const searchControl = new ymaps.control.SearchControl({
        options: {
          noPlacemark: true,
          placeholderContent: 'Введите адрес доставки',
          size: 'large',
        }
      });

      map.controls.add(searchControl);

      // Handle search results
      searchControl.events.add('resultselect', async (e: any) => {
        const index = e.get('index');
        const results = searchControl.getResultsArray();
        if (results && results[index]) {
          const coords = results[index].geometry.getCoordinates();
          const address = results[index].properties.get('text');
          setSearchAddress(address);
          // Use ref to get latest function
          if (calculateDeliveryRef.current) {
            calculateDeliveryRef.current(coords, address);
          }
        }
      });

      // Handle map click
      map.events.add('click', async (e: any) => {
        const coords = e.get('coords');
        // Reverse geocode to get address
        try {
          const result = await ymaps.geocode(coords);
          const firstGeo = result.geoObjects.get(0);
          if (firstGeo) {
            const address = firstGeo.getAddressLine();
            setSearchAddress(address);
            // Use ref to get latest function
            if (calculateDeliveryRef.current) {
              calculateDeliveryRef.current(coords, address);
            }
          }
        } catch (err) {
          console.error('Reverse geocode error:', err);
        }
      });

      // Load GeoJSON zones
      fetch('/upload/delivery-locations.geojson')
        .then(res => res.json())
        .then(geoJson => {
          ymaps.geoQuery(geoJson).addToMap(map).each((obj: any) => {
            if (obj.geometry.getType() === 'Polygon') {
              obj.options.set({
                fillColor: obj.properties.get('fill') || '#56db40',
                fillOpacity: obj.properties.get('fill-opacity') || 0.2,
                strokeColor: obj.properties.get('stroke') || '#1bad03',
                strokeWidth: obj.properties.get('stroke-width') || 2,
                strokeOpacity: obj.properties.get('stroke-opacity') || 1,
              });
            }
          });

          // Add delivery point markers
          geoJson.features
            .filter((f: any) => f.geometry.type === 'Point')
            .forEach((f: any) => {
              const placemark = new ymaps.Placemark(f.geometry.coordinates, {
                hintContent: f.properties.description,
              }, {
                preset: 'islands#greenDotIcon',
              });
              map.geoObjects.add(placemark);
            });
        })
        .catch(console.error);
    });
  }, []);

  // Calculate delivery from nearest point to destination
  const calculateDelivery = useCallback(async (destCoords: [number, number], address: string) => {
    if (!deliveryPoints.length || !prices || !mapRef.current) return;
    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    setIsCalculating(true);
    setDeliveryResult(null);

    const ymaps = (window as any).ymaps;

    try {
      // Find nearest delivery point
      const nearestPoint = findNearestPoint(destCoords, deliveryPoints);
      if (!nearestPoint) {
        setIsCalculating(false);
        return;
      }

      // Clear previous route and marker
      if (routeRef.current) {
        mapRef.current.geoObjects.remove(routeRef.current);
        routeRef.current = null;
      }
      if (destinationMarkerRef.current) {
        mapRef.current.geoObjects.remove(destinationMarkerRef.current);
        destinationMarkerRef.current = null;
      }

      // Add destination marker
      destinationMarkerRef.current = new ymaps.Placemark(destCoords, {
        hintContent: 'Адрес доставки',
        balloonContent: address,
      }, {
        preset: 'islands#redDotIcon',
      });
      mapRef.current.geoObjects.add(destinationMarkerRef.current);

      // Build route
      const multiRoute = new ymaps.multiRouter.MultiRoute({
        referencePoints: [
          nearestPoint.coordinates,
          destCoords
        ],
        params: {
          routingMode: 'auto'
        }
      }, {
        boundsAutoApply: true,
        routeActiveStrokeWidth: 4,
        routeActiveStrokeColor: '#1bad03',
      });

      mapRef.current.geoObjects.add(multiRoute);
      routeRef.current = multiRoute;

      // Wait for route calculation
      multiRoute.model.events.add('requestsuccess', () => {
        const activeRoute = multiRoute.getActiveRoute();
        if (activeRoute) {
          const routeLength = activeRoute.properties.get('distance').value;
          const distanceKm = Math.round(routeLength / 1000);
          const cost = Math.round(prices.delivery_base_price + distanceKm * prices.delivery_per_km);

          // Get region name from reverse geocode
          ymaps.geocode(destCoords).then((result: any) => {
            const firstGeo = result.geoObjects.get(0);
            let regionName = '';
            if (firstGeo) {
              const addressDetails = firstGeo.properties.get('metaDataProperty.GeocoderMetaData.AddressDetails');
              if (addressDetails?.Country?.AdministrativeArea) {
                regionName = addressDetails.Country.AdministrativeArea.AdministrativeAreaName || '';
              }
            }

            setDeliveryResult({
              nearestPoint,
              distance: distanceKm,
              cost,
              regionName: regionName || nearestPoint.name,
            });
            setIsCalculating(false);
          }).catch(() => {
            setDeliveryResult({
              nearestPoint,
              distance: distanceKm,
              cost,
              regionName: nearestPoint.name,
            });
            setIsCalculating(false);
          });
        }
      });

      multiRoute.model.events.add('requestfail', () => {
        console.error('Route calculation failed');
        setIsCalculating(false);
      });

    } catch (error) {
      console.error('Delivery calculation error:', error);
      setIsCalculating(false);
    }
  }, [deliveryPoints, prices]);

  // Keep ref updated with latest calculateDelivery function
  useEffect(() => {
    calculateDeliveryRef.current = calculateDelivery;
  }, [calculateDelivery]);

  // Search by address
  const handleSearch = useCallback(async () => {
    if (!searchAddress.trim() || !mapRef.current) return;
    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    const ymaps = (window as any).ymaps;

    try {
      const result = await ymaps.geocode(searchAddress);
      const firstGeo = result.geoObjects.get(0);
      if (firstGeo) {
        const coords = firstGeo.geometry.getCoordinates();
        calculateDelivery(coords, searchAddress);
      }
    } catch (error) {
      console.error('Geocode error:', error);
    }
  }, [searchAddress, calculateDelivery]);

  // Close result popup
  const closeResult = useCallback(() => {
    setDeliveryResult(null);
    if (routeRef.current && mapRef.current) {
      mapRef.current.geoObjects.remove(routeRef.current);
      routeRef.current = null;
    }
    if (destinationMarkerRef.current && mapRef.current) {
      mapRef.current.geoObjects.remove(destinationMarkerRef.current);
      destinationMarkerRef.current = null;
    }
  }, []);

  // Initialize map when ymaps is loaded
  useEffect(() => {
    if ((window as any).ymaps && deliveryPoints.length > 0) {
      initMap();
    }
  }, [deliveryPoints, initMap]);

  return (
    <>
      <Script
        src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=51e35aa4-fa5e-432e-a7c6-e5e71105ec3a"
        strategy="afterInteractive"
        onLoad={() => {
          if (deliveryPoints.length > 0) {
            initMap();
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
          {/* Search bar */}
          <div className="mb-6">
            <div className="flex gap-2 max-w-2xl">
              <input
                type="text"
                value={searchAddress}
                onChange={(e) => setSearchAddress(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                placeholder="Введите адрес доставки"
                className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
              />
              <button
                onClick={handleSearch}
                disabled={isCalculating || !searchAddress.trim()}
                className="px-6 py-3 bg-[#62bb46] text-white font-medium rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50"
              >
                {isCalculating ? 'Расчёт...' : 'Найти'}
              </button>
            </div>
            <p className="text-gray-500 text-sm mt-2">
              Или кликните на карте, чтобы выбрать точку доставки
            </p>
          </div>

          {/* Map section */}
          <div className="mb-10 relative">
            <div
              id="map"
              className="map w-full h-[500px] md:h-[600px] rounded-lg border border-gray-200"
            />

            {/* Delivery result popup */}
            {deliveryResult && (
              <div
                ref={resultPopupRef}
                className="absolute top-4 right-4 bg-white rounded-lg shadow-xl p-6 max-w-sm z-10"
              >
                <h3 className="text-lg font-bold text-gray-900 mb-2">
                  Расчетная стоимость доставки
                </h3>
                <p className="text-gray-600 mb-4">
                  {deliveryResult.regionName}
                </p>

                <div className="space-y-2 mb-4">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Расстояние:</span>
                    <span className="font-medium">{deliveryResult.distance} км</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Стоимость доставки:</span>
                    <span className="font-bold text-lg">{deliveryResult.cost.toLocaleString('ru-RU')} рублей</span>
                  </div>
                </div>

                <div className="flex gap-2 flex-wrap">
                  <button
                    onClick={closeResult}
                    className="px-4 py-2 border border-[#f7a600] text-[#f7a600] rounded-lg hover:bg-[#f7a600] hover:text-white transition-colors text-sm font-medium"
                  >
                    ЗАКРЫТЬ
                  </button>
                  <a
                    href="/catalog"
                    className="px-4 py-2 bg-[#62bb46] text-white rounded-lg hover:bg-[#55a83d] transition-colors text-sm font-medium"
                  >
                    ПРОДОЛЖИТЬ ПОКУПКИ
                  </a>
                </div>
              </div>
            )}

            {/* Loading overlay */}
            {isCalculating && (
              <div className="absolute inset-0 bg-white/50 flex items-center justify-center rounded-lg">
                <div className="bg-white rounded-lg shadow-lg p-4 flex items-center gap-3">
                  <div className="w-6 h-6 border-3 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
                  <span className="text-gray-600">Расчёт маршрута...</span>
                </div>
              </div>
            )}
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

            {/* Price info */}
            {prices && (
              <div className="bg-[#f9f9fa] rounded-lg p-6">
                <h3 className="text-lg font-bold text-[#3d4543] mb-4">
                  Тарифы на доставку в {toDativeCase(city.name)}
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <div className="flex justify-between">
                    <span className="text-gray-600">Базовая стоимость:</span>
                    <span className="font-medium">{Math.round(prices.delivery_base_price).toLocaleString('ru-RU')} ₽</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">За каждый км:</span>
                    <span className="font-medium">{Math.round(prices.delivery_per_km).toLocaleString('ru-RU')} ₽</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Подъём без лифта (за этаж):</span>
                    <span className="font-medium">{Math.round(prices.floor_lift_price).toLocaleString('ru-RU')} ₽</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Подъём с грузовым лифтом:</span>
                    <span className="font-medium">{Math.round(prices.elevator_lift_price).toLocaleString('ru-RU')} ₽</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Выезд сборщика (за км):</span>
                    <span className="font-medium">{Math.round(prices.assembly_per_km).toLocaleString('ru-RU')} ₽</span>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
