/* eslint-disable @typescript-eslint/no-explicit-any */
'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import Script from 'next/script';

interface Warehouse {
  id: number;
  name: string;
  address: string | null;
  latitude: number | null;
  longitude: number | null;
  phone: string | null;
  working_hours: string | null;
}

interface Prices {
  delivery_base_price: number;
  delivery_per_km: number;
  floor_lift_price: number;
  elevator_lift_price: number;
  assembly_per_km: number;
}

interface DeliveryInfo {
  city: {
    id: number;
    name: string;
    price_group: string | null;
  };
  warehouse: Warehouse | null;
  prices: Prices | null;
}

interface DeliveryOptionsProps {
  cityName: string;
  onDeliveryChange: (data: {
    type: 'delivery' | 'pickup';
    address?: string;
    liftType: 'none' | 'stairs' | 'elevator';
    floor?: number;
    deliveryCost: number;
    liftCost: number;
  }) => void;
}

export default function DeliveryOptions({ cityName, onDeliveryChange }: DeliveryOptionsProps) {
  const [deliveryInfo, setDeliveryInfo] = useState<DeliveryInfo | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Delivery type: pickup or delivery
  const [deliveryType, setDeliveryType] = useState<'delivery' | 'pickup'>('delivery');

  // Delivery address
  const [address, setAddress] = useState('');
  const [addressError, setAddressError] = useState('');

  // Lift options
  const [liftType, setLiftType] = useState<'none' | 'stairs' | 'elevator'>('none');
  const [floor, setFloor] = useState(1);

  // Distance calculation
  const [distance, setDistance] = useState<number | null>(null);
  const [calculating, setCalculating] = useState(false);

  // Map
  const mapRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<any>(null);
  const markerRef = useRef<any>(null);
  const routeRef = useRef<any>(null);

  // Yandex Maps API Key (same as delivery page)
  const YANDEX_API_KEY = '51e35aa4-fa5e-432e-a7c6-e5e71105ec3a';

  // Fetch delivery info for the city
  useEffect(() => {
    const fetchDeliveryInfo = async () => {
      if (!cityName) {
        setLoading(false);
        return;
      }

      try {
        const response = await fetch(`/api/delivery-info?city_name=${encodeURIComponent(cityName)}`);
        const data = await response.json();

        if (data.success) {
          setDeliveryInfo(data.data);
        } else {
          setError(data.error || 'Не удалось получить информацию о доставке');
        }
      } catch {
        setError('Ошибка при загрузке информации о доставке');
      } finally {
        setLoading(false);
      }
    };

    fetchDeliveryInfo();
  }, [cityName]);

  // Calculate delivery cost
  const deliveryCost = useCallback(() => {
    if (deliveryType === 'pickup') return 0;
    if (!deliveryInfo?.prices) return 0;

    const baseCost = deliveryInfo.prices.delivery_base_price;
    const kmCost = distance ? distance * deliveryInfo.prices.delivery_per_km : 0;

    return Math.round(baseCost + kmCost);
  }, [deliveryType, deliveryInfo, distance]);

  // Calculate lift cost
  const liftCost = useCallback(() => {
    if (deliveryType === 'pickup') return 0;
    if (!deliveryInfo?.prices) return 0;

    if (liftType === 'none') return 0;
    if (liftType === 'elevator') return deliveryInfo.prices.elevator_lift_price;
    if (liftType === 'stairs') return deliveryInfo.prices.floor_lift_price * floor;

    return 0;
  }, [deliveryType, deliveryInfo, liftType, floor]);

  // Notify parent of changes
  useEffect(() => {
    onDeliveryChange({
      type: deliveryType,
      address: deliveryType === 'delivery' ? address : undefined,
      liftType,
      floor: liftType === 'stairs' ? floor : undefined,
      deliveryCost: deliveryCost(),
      liftCost: liftCost(),
    });
  }, [deliveryType, address, liftType, floor, deliveryCost, liftCost, onDeliveryChange]);

  // Initialize map for pickup
  const initPickupMap = useCallback(() => {
    if (!deliveryInfo?.warehouse?.latitude || !deliveryInfo?.warehouse?.longitude) return;
    if (!mapRef.current) return;
    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    const ymaps = (window as any).ymaps;

    ymaps.ready(() => {
      // Clear existing map
      if (mapInstanceRef.current) {
        mapInstanceRef.current.destroy();
      }

      const warehouseCoords = [deliveryInfo.warehouse!.longitude, deliveryInfo.warehouse!.latitude];

      mapInstanceRef.current = new ymaps.Map(mapRef.current, {
        center: warehouseCoords,
        zoom: 15,
        controls: ['zoomControl'],
      });

      // Add warehouse marker
      const placemark = new ymaps.Placemark(warehouseCoords, {
        hintContent: deliveryInfo.warehouse!.name,
        balloonContentHeader: deliveryInfo.warehouse!.name,
        balloonContentBody: `
          <div style="font-size: 14px;">
            <p><strong>Адрес:</strong> ${deliveryInfo.warehouse!.address || 'Не указан'}</p>
            <p><strong>Телефон:</strong> ${deliveryInfo.warehouse!.phone || 'Не указан'}</p>
            <p><strong>Время работы:</strong> ${deliveryInfo.warehouse!.working_hours || 'Не указано'}</p>
          </div>
        `,
      }, {
        preset: 'islands#greenDotIcon',
      });

      mapInstanceRef.current.geoObjects.add(placemark);
      placemark.balloon.open();
    });
  }, [deliveryInfo?.warehouse]);

  // Initialize map for delivery
  const initDeliveryMap = useCallback(() => {
    if (!deliveryInfo?.warehouse?.latitude || !deliveryInfo?.warehouse?.longitude) return;
    if (!mapRef.current) return;
    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    const ymaps = (window as any).ymaps;

    ymaps.ready(() => {
      // Clear existing map
      if (mapInstanceRef.current) {
        mapInstanceRef.current.destroy();
      }

      const warehouseCoords = [deliveryInfo.warehouse!.longitude, deliveryInfo.warehouse!.latitude];

      mapInstanceRef.current = new ymaps.Map(mapRef.current, {
        center: warehouseCoords,
        zoom: 10,
        controls: ['zoomControl', 'searchControl'],
      });

      // Add warehouse marker
      const warehousePlacemark = new ymaps.Placemark(warehouseCoords, {
        hintContent: 'Склад',
      }, {
        preset: 'islands#blueWarehouseIcon',
      });

      mapInstanceRef.current.geoObjects.add(warehousePlacemark);
    });
  }, [deliveryInfo?.warehouse]);

  // Calculate route to address
  const calculateRoute = useCallback(async () => {
    if (!address.trim()) {
      setAddressError('Введите адрес доставки');
      return;
    }

    if (!deliveryInfo?.warehouse?.latitude || !deliveryInfo?.warehouse?.longitude) {
      setAddressError('Склад не настроен для этого города');
      return;
    }

    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    setCalculating(true);
    setAddressError('');

    const ymaps = (window as any).ymaps;

    try {
      // Geocode address
      const geocodeResult = await ymaps.geocode(address);
      const firstResult = geocodeResult.geoObjects.get(0);

      if (!firstResult) {
        setAddressError('Адрес не найден');
        setCalculating(false);
        return;
      }

      const addressCoords = firstResult.geometry.getCoordinates();
      const warehouseCoords = [deliveryInfo.warehouse!.longitude, deliveryInfo.warehouse!.latitude];

      // Clear previous route and marker
      if (routeRef.current) {
        mapInstanceRef.current.geoObjects.remove(routeRef.current);
      }
      if (markerRef.current) {
        mapInstanceRef.current.geoObjects.remove(markerRef.current);
      }

      // Add delivery point marker
      markerRef.current = new ymaps.Placemark(addressCoords, {
        hintContent: 'Адрес доставки',
      }, {
        preset: 'islands#redDotIcon',
      });

      mapInstanceRef.current.geoObjects.add(markerRef.current);

      // Build route
      const route = await ymaps.route([warehouseCoords, addressCoords], {
        routingMode: 'auto',
      });

      routeRef.current = route;
      mapInstanceRef.current.geoObjects.add(route);

      // Get distance
      const routeLength = route.getLength();
      const distanceKm = Math.round(routeLength / 1000);
      setDistance(distanceKm);

      // Fit map to route
      mapInstanceRef.current.setBounds(route.getBounds(), {
        checkZoomRange: true,
        zoomMargin: 30,
      });
    } catch {
      setAddressError('Ошибка при расчёте маршрута');
    } finally {
      setCalculating(false);
    }
  }, [address, deliveryInfo?.warehouse]);

  // Initialize appropriate map when delivery type changes
  useEffect(() => {
    if (loading || !deliveryInfo) return;

    // Small delay to ensure DOM is ready
    const timer = setTimeout(() => {
      if (deliveryType === 'pickup') {
        initPickupMap();
      } else {
        initDeliveryMap();
      }
    }, 100);

    return () => clearTimeout(timer);
  }, [deliveryType, loading, deliveryInfo, initPickupMap, initDeliveryMap]);

  if (loading) {
    return (
      <div className="flex justify-center py-8">
        <div className="w-8 h-8 border-4 border-[#62bb46] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700">
        {error}
      </div>
    );
  }

  return (
    <>
      <Script
        src={`https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=${YANDEX_API_KEY}`}
        strategy="afterInteractive"
      />

      <div className="space-y-6">
        {/* Delivery type selection */}
        <div>
          <h4 className="font-medium text-gray-900 mb-3">Способ получения</h4>
          <div className="grid grid-cols-2 gap-3">
            <button
              type="button"
              onClick={() => setDeliveryType('delivery')}
              className={`p-4 rounded-lg border-2 text-left transition-colors ${
                deliveryType === 'delivery'
                  ? 'border-[#62bb46] bg-[#62bb46]/5'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              <div className="flex items-center gap-3">
                <svg className={`w-6 h-6 ${deliveryType === 'delivery' ? 'text-[#62bb46]' : 'text-gray-400'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                <div>
                  <div className="font-medium">Доставка Е1</div>
                  <div className="text-sm text-gray-500">Курьером до двери</div>
                </div>
              </div>
            </button>

            <button
              type="button"
              onClick={() => setDeliveryType('pickup')}
              disabled={!deliveryInfo?.warehouse}
              className={`p-4 rounded-lg border-2 text-left transition-colors ${
                deliveryType === 'pickup'
                  ? 'border-[#62bb46] bg-[#62bb46]/5'
                  : 'border-gray-200 hover:border-gray-300'
              } ${!deliveryInfo?.warehouse ? 'opacity-50 cursor-not-allowed' : ''}`}
            >
              <div className="flex items-center gap-3">
                <svg className={`w-6 h-6 ${deliveryType === 'pickup' ? 'text-[#62bb46]' : 'text-gray-400'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
                <div>
                  <div className="font-medium">Самовывоз</div>
                  <div className="text-sm text-gray-500">Бесплатно</div>
                </div>
              </div>
            </button>
          </div>
        </div>

        {/* Pickup info */}
        {deliveryType === 'pickup' && deliveryInfo?.warehouse && (
          <div className="bg-gray-50 rounded-lg p-4 space-y-4">
            <div>
              <h4 className="font-medium text-gray-900 mb-2">Склад самовывоза</h4>
              <div className="space-y-2 text-sm">
                <p className="font-medium">{deliveryInfo.warehouse.name}</p>
                {deliveryInfo.warehouse.address && (
                  <p className="text-gray-600">
                    <span className="text-gray-500">Адрес:</span> {deliveryInfo.warehouse.address}
                  </p>
                )}
                {deliveryInfo.warehouse.phone && (
                  <p className="text-gray-600">
                    <span className="text-gray-500">Телефон:</span>{' '}
                    <a href={`tel:${deliveryInfo.warehouse.phone}`} className="text-[#62bb46]">
                      {deliveryInfo.warehouse.phone}
                    </a>
                  </p>
                )}
                {deliveryInfo.warehouse.working_hours && (
                  <p className="text-gray-600">
                    <span className="text-gray-500">Время работы:</span> {deliveryInfo.warehouse.working_hours}
                  </p>
                )}
              </div>
            </div>

            {deliveryInfo.warehouse.latitude && deliveryInfo.warehouse.longitude && (
              <div
                ref={mapRef}
                className="w-full h-[250px] rounded-lg border border-gray-200"
              />
            )}

            <div className="text-lg font-bold text-[#62bb46]">
              Стоимость: 0 ₽
            </div>
          </div>
        )}

        {/* Delivery options */}
        {deliveryType === 'delivery' && (
          <div className="space-y-4">
            {/* Address input */}
            <div>
              <h4 className="font-medium text-gray-900 mb-3">Адрес доставки</h4>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  placeholder="Введите адрес доставки"
                  className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
                />
                <button
                  type="button"
                  onClick={calculateRoute}
                  disabled={calculating || !address.trim()}
                  className="px-4 py-3 bg-[#62bb46] text-white rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50"
                >
                  {calculating ? 'Расчёт...' : 'Рассчитать'}
                </button>
              </div>
              {addressError && (
                <p className="text-sm text-red-500 mt-1">{addressError}</p>
              )}
            </div>

            {/* Map */}
            {deliveryInfo?.warehouse?.latitude && deliveryInfo?.warehouse?.longitude && (
              <div
                ref={mapRef}
                className="w-full h-[300px] rounded-lg border border-gray-200"
              />
            )}

            {/* Distance and cost */}
            {distance !== null && (
              <div className="bg-gray-50 rounded-lg p-4">
                <div className="flex justify-between items-center text-sm">
                  <span className="text-gray-600">Расстояние:</span>
                  <span className="font-medium">{distance} км</span>
                </div>
                <div className="flex justify-between items-center text-sm mt-2">
                  <span className="text-gray-600">Стоимость доставки:</span>
                  <span className="font-medium">{deliveryCost().toLocaleString('ru-RU')} ₽</span>
                </div>
              </div>
            )}

            {/* Lift options */}
            <div>
              <h4 className="font-medium text-gray-900 mb-3">Подъём на этаж</h4>
              <div className="space-y-2">
                <label className="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-gray-300">
                  <input
                    type="radio"
                    name="liftType"
                    value="none"
                    checked={liftType === 'none'}
                    onChange={() => setLiftType('none')}
                    className="w-4 h-4 text-[#62bb46]"
                  />
                  <div className="flex-1">
                    <span className="font-medium">Подъём не нужен</span>
                  </div>
                  <span className="text-gray-500">0 ₽</span>
                </label>

                <label className="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-gray-300">
                  <input
                    type="radio"
                    name="liftType"
                    value="stairs"
                    checked={liftType === 'stairs'}
                    onChange={() => setLiftType('stairs')}
                    className="w-4 h-4 text-[#62bb46]"
                  />
                  <div className="flex-1">
                    <span className="font-medium">Подъём без грузового лифта</span>
                    {deliveryInfo?.prices && (
                      <span className="text-sm text-gray-500 ml-2">
                        ({deliveryInfo.prices.floor_lift_price.toLocaleString('ru-RU')} ₽ за этаж)
                      </span>
                    )}
                  </div>
                </label>

                {liftType === 'stairs' && (
                  <div className="ml-7 flex items-center gap-3">
                    <label className="text-sm text-gray-600">Этаж:</label>
                    <select
                      value={floor}
                      onChange={(e) => setFloor(parseInt(e.target.value))}
                      className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
                    >
                      {Array.from({ length: 30 }, (_, i) => i + 1).map((f) => (
                        <option key={f} value={f}>{f}</option>
                      ))}
                    </select>
                    <span className="font-medium text-gray-900">
                      = {liftCost().toLocaleString('ru-RU')} ₽
                    </span>
                  </div>
                )}

                <label className="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-gray-300">
                  <input
                    type="radio"
                    name="liftType"
                    value="elevator"
                    checked={liftType === 'elevator'}
                    onChange={() => setLiftType('elevator')}
                    className="w-4 h-4 text-[#62bb46]"
                  />
                  <div className="flex-1">
                    <span className="font-medium">Подъём с грузовым лифтом</span>
                  </div>
                  {deliveryInfo?.prices && (
                    <span className="text-gray-500">
                      {deliveryInfo.prices.elevator_lift_price.toLocaleString('ru-RU')} ₽
                    </span>
                  )}
                </label>
              </div>
            </div>

            {/* Total delivery cost */}
            <div className="bg-[#62bb46]/10 rounded-lg p-4">
              <div className="flex justify-between items-center">
                <span className="font-medium text-gray-900">Итого за доставку:</span>
                <span className="text-xl font-bold text-[#62bb46]">
                  {(deliveryCost() + liftCost()).toLocaleString('ru-RU')} ₽
                </span>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
