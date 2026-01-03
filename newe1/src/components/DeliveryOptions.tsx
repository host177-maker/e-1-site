/* eslint-disable @typescript-eslint/no-explicit-any */
'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import Script from 'next/script';

interface DeliveryPoint {
  id: number;
  name: string;
  coordinates: [number, number]; // [longitude, latitude]
}

interface Prices {
  delivery_base_price: number;
  delivery_per_km: number;
  floor_lift_price: number;
  elevator_lift_price: number;
  assembly_per_km: number;
}

interface DeliveryOptionsProps {
  cityName: string;
  hasAssembly?: boolean;
  onDeliveryChange: (data: {
    type: 'delivery' | 'pickup';
    address?: string;
    liftType: 'none' | 'stairs' | 'elevator';
    floor?: number;
    deliveryCost: number;
    liftCost: number;
    assemblyCost: number;
  }) => void;
}

// Calculate distance between two points using Haversine formula
function haversineDistance(
  coord1: [number, number],
  coord2: [number, number]
): number {
  const toRad = (x: number) => (x * Math.PI) / 180;
  const R = 6371; // Earth's radius in km

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

// Find nearest delivery point to given coordinates
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

export default function DeliveryOptions({ cityName, hasAssembly = false, onDeliveryChange }: DeliveryOptionsProps) {
  const [deliveryPoints, setDeliveryPoints] = useState<DeliveryPoint[]>([]);
  const [nearestPoint, setNearestPoint] = useState<DeliveryPoint | null>(null);
  const [prices, setPrices] = useState<Prices | null>(null);
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

  // Yandex Maps API Key
  const YANDEX_API_KEY = '51e35aa4-fa5e-432e-a7c6-e5e71105ec3a';

  // Load delivery points (once)
  useEffect(() => {
    const loadPoints = async () => {
      try {
        const pointsRes = await fetch('/api/delivery-points');
        const pointsData = await pointsRes.json();

        if (pointsData.success) {
          setDeliveryPoints(pointsData.points);
        }
      } catch (err) {
        console.error('Error loading delivery points:', err);
        setError('Ошибка загрузки данных доставки');
      }
    };

    loadPoints();
  }, []);

  // Load prices based on city (reload when city changes)
  useEffect(() => {
    const loadPrices = async () => {
      try {
        // Pass city name to get region-specific prices
        const pricesRes = await fetch(`/api/e1admin/service-prices?region=${encodeURIComponent(cityName)}`);
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
      } catch (err) {
        console.error('Error loading prices:', err);
      } finally {
        setLoading(false);
      }
    };

    loadPrices();
  }, [cityName]);

  // Find nearest point when city changes and we have points
  useEffect(() => {
    const findNearestByCity = async () => {
      if (!cityName || deliveryPoints.length === 0) return;

      // First, try to find exact match by name
      const exactMatch = deliveryPoints.find(
        p => p.name.toLowerCase() === cityName.toLowerCase()
      );

      if (exactMatch) {
        setNearestPoint(exactMatch);
        return;
      }

      // Otherwise, geocode the city and find nearest point
      if (typeof window !== 'undefined' && (window as any).ymaps) {
        const ymaps = (window as any).ymaps;

        try {
          await ymaps.ready();
          const result = await ymaps.geocode(cityName);
          const firstGeo = result.geoObjects.get(0);

          if (firstGeo) {
            const cityCoords = firstGeo.geometry.getCoordinates();
            // Yandex returns [lat, lon] when coordorder is not set
            // But we set coordorder=longlat, so it returns [lon, lat]
            const nearest = findNearestPoint(cityCoords as [number, number], deliveryPoints);
            setNearestPoint(nearest);
          }
        } catch {
          // If geocoding fails, just use first point
          if (deliveryPoints.length > 0) {
            setNearestPoint(deliveryPoints[0]);
          }
        }
      } else {
        // If ymaps not available yet, try to find by partial name match
        const partialMatch = deliveryPoints.find(
          p => p.name.toLowerCase().includes(cityName.toLowerCase()) ||
               cityName.toLowerCase().includes(p.name.toLowerCase())
        );
        if (partialMatch) {
          setNearestPoint(partialMatch);
        } else if (deliveryPoints.length > 0) {
          // Default to Moscow or first point
          const moscow = deliveryPoints.find(p => p.name.toLowerCase() === 'москва');
          setNearestPoint(moscow || deliveryPoints[0]);
        }
      }
    };

    findNearestByCity();
  }, [cityName, deliveryPoints]);

  // Calculate delivery cost
  const deliveryCost = useCallback(() => {
    if (deliveryType === 'pickup') return 0;
    if (!prices) return 0;

    const baseCost = prices.delivery_base_price;
    const kmCost = distance ? distance * prices.delivery_per_km : 0;

    return Math.round(baseCost + kmCost);
  }, [deliveryType, prices, distance]);

  // Calculate lift cost
  const liftCost = useCallback(() => {
    if (deliveryType === 'pickup') return 0;
    if (!prices) return 0;

    if (liftType === 'none') return 0;
    if (liftType === 'elevator') return prices.elevator_lift_price;
    if (liftType === 'stairs') return prices.floor_lift_price * floor;

    return 0;
  }, [deliveryType, prices, liftType, floor]);

  // Calculate assembly travel cost (when assembly is selected)
  const assemblyCost = useCallback(() => {
    if (deliveryType === 'pickup') return 0;
    if (!hasAssembly) return 0;
    if (!prices) return 0;
    if (!distance) return 0;

    return Math.round(distance * prices.assembly_per_km);
  }, [deliveryType, hasAssembly, prices, distance]);

  // Notify parent of changes
  useEffect(() => {
    onDeliveryChange({
      type: deliveryType,
      address: deliveryType === 'delivery' ? address : undefined,
      liftType,
      floor: liftType === 'stairs' ? floor : undefined,
      deliveryCost: deliveryCost(),
      liftCost: liftCost(),
      assemblyCost: assemblyCost(),
    });
  }, [deliveryType, address, liftType, floor, deliveryCost, liftCost, assemblyCost, onDeliveryChange]);

  // Initialize map for pickup
  const initPickupMap = useCallback(() => {
    if (!nearestPoint) return;
    if (!mapRef.current) return;
    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    const ymaps = (window as any).ymaps;

    ymaps.ready(() => {
      // Clear existing map
      if (mapInstanceRef.current) {
        mapInstanceRef.current.destroy();
      }

      mapInstanceRef.current = new ymaps.Map(mapRef.current, {
        center: nearestPoint.coordinates,
        zoom: 12,
        controls: ['zoomControl'],
      });

      // Add pickup point marker
      const placemark = new ymaps.Placemark(nearestPoint.coordinates, {
        hintContent: nearestPoint.name,
        balloonContentHeader: `Склад ${nearestPoint.name}`,
        balloonContentBody: `
          <div style="font-size: 14px;">
            <p><strong>Пункт выдачи</strong></p>
            <p>Самовывоз - бесплатно</p>
          </div>
        `,
      }, {
        preset: 'islands#greenDotIcon',
      });

      mapInstanceRef.current.geoObjects.add(placemark);
      placemark.balloon.open();
    });
  }, [nearestPoint]);

  // Initialize map for delivery
  const initDeliveryMap = useCallback(() => {
    if (!nearestPoint) return;
    if (!mapRef.current) return;
    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    const ymaps = (window as any).ymaps;

    ymaps.ready(() => {
      // Clear existing map
      if (mapInstanceRef.current) {
        mapInstanceRef.current.destroy();
      }

      mapInstanceRef.current = new ymaps.Map(mapRef.current, {
        center: nearestPoint.coordinates,
        zoom: 9,
        controls: ['zoomControl', 'searchControl'],
      });

      // Add warehouse marker
      const warehousePlacemark = new ymaps.Placemark(nearestPoint.coordinates, {
        hintContent: `Склад: ${nearestPoint.name}`,
      }, {
        preset: 'islands#blueWarehouseIcon',
      });

      mapInstanceRef.current.geoObjects.add(warehousePlacemark);
    });
  }, [nearestPoint]);

  // Calculate route to address
  const calculateRoute = useCallback(async () => {
    if (!address.trim()) {
      setAddressError('Введите адрес доставки');
      return;
    }

    if (!nearestPoint) {
      setAddressError('Точка отправки не определена');
      return;
    }

    if (typeof window === 'undefined' || !(window as any).ymaps) return;

    setCalculating(true);
    setAddressError('');

    const ymaps = (window as any).ymaps;

    try {
      await ymaps.ready();

      // Geocode address
      const geocodeResult = await ymaps.geocode(address);
      const firstResult = geocodeResult.geoObjects.get(0);

      if (!firstResult) {
        setAddressError('Адрес не найден');
        setCalculating(false);
        return;
      }

      const addressCoords = firstResult.geometry.getCoordinates();

      // Find nearest delivery point to the destination address
      const nearestToAddress = findNearestPoint(addressCoords as [number, number], deliveryPoints);
      if (nearestToAddress && nearestToAddress.id !== nearestPoint.id) {
        setNearestPoint(nearestToAddress);
      }
      const startPoint = nearestToAddress || nearestPoint;

      // Clear previous route and marker
      if (routeRef.current && mapInstanceRef.current) {
        mapInstanceRef.current.geoObjects.remove(routeRef.current);
      }
      if (markerRef.current && mapInstanceRef.current) {
        mapInstanceRef.current.geoObjects.remove(markerRef.current);
      }

      // Add delivery point marker
      markerRef.current = new ymaps.Placemark(addressCoords, {
        hintContent: 'Адрес доставки',
      }, {
        preset: 'islands#redDotIcon',
      });

      mapInstanceRef.current.geoObjects.add(markerRef.current);

      // Build route using multiRouter for better routing
      const multiRoute = new ymaps.multiRouter.MultiRoute({
        referencePoints: [
          startPoint.coordinates,
          addressCoords
        ],
        params: {
          routingMode: 'auto'
        }
      }, {
        boundsAutoApply: true,
        routeActiveStrokeWidth: 4,
        routeActiveStrokeColor: '#1bad03',
      });

      mapInstanceRef.current.geoObjects.add(multiRoute);
      routeRef.current = multiRoute;

      // Wait for route to be built and get distance
      multiRoute.model.events.add('requestsuccess', () => {
        const activeRoute = multiRoute.getActiveRoute();
        if (activeRoute) {
          const routeLength = activeRoute.properties.get('distance').value;
          const distanceKm = Math.round(routeLength / 1000);
          setDistance(distanceKm);
        }
      });

      multiRoute.model.events.add('requestfail', () => {
        setAddressError('Не удалось построить маршрут');
        setCalculating(false);
      });

    } catch (err) {
      console.error('Route calculation error:', err);
      setAddressError('Ошибка при расчёте маршрута');
    } finally {
      setCalculating(false);
    }
  }, [address, nearestPoint, deliveryPoints]);

  // Initialize appropriate map when delivery type changes
  useEffect(() => {
    if (loading || !nearestPoint) return;

    // Small delay to ensure DOM is ready
    const timer = setTimeout(() => {
      if (deliveryType === 'pickup') {
        initPickupMap();
      } else {
        initDeliveryMap();
      }
    }, 100);

    return () => clearTimeout(timer);
  }, [deliveryType, loading, nearestPoint, initPickupMap, initDeliveryMap]);

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
                </div>
              </div>
            </button>

            <button
              type="button"
              onClick={() => setDeliveryType('pickup')}
              disabled={!nearestPoint}
              className={`p-4 rounded-lg border-2 text-left transition-colors ${
                deliveryType === 'pickup'
                  ? 'border-[#62bb46] bg-[#62bb46]/5'
                  : 'border-gray-200 hover:border-gray-300'
              } ${!nearestPoint ? 'opacity-50 cursor-not-allowed' : ''}`}
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
        {deliveryType === 'pickup' && nearestPoint && (
          <div className="bg-gray-50 rounded-lg p-4 space-y-4">
            <div>
              <h4 className="font-medium text-gray-900 mb-2">Пункт самовывоза</h4>
              <div className="space-y-2 text-sm">
                <p className="font-medium">{nearestPoint.name}</p>
                <p className="text-gray-600">
                  Ближайший пункт выдачи к вашему городу
                </p>
              </div>
            </div>

            <div
              ref={mapRef}
              className="w-full h-[250px] rounded-lg border border-gray-200"
            />

            <div className="text-lg font-bold text-[#62bb46]">
              Стоимость: 0 ₽
            </div>
          </div>
        )}

        {/* Delivery options */}
        {deliveryType === 'delivery' && (
          <div className="space-y-4">
            {/* Nearest warehouse info */}
            {nearestPoint && (
              <div className="p-3 bg-blue-50 rounded-lg text-sm">
                <span className="text-blue-600">Ближайший склад: </span>
                <span className="font-medium text-blue-800">{nearestPoint.name}</span>
              </div>
            )}

            {/* Address input */}
            <div>
              <h4 className="font-medium text-gray-900 mb-3">Адрес доставки</h4>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && calculateRoute()}
                  placeholder="Введите адрес доставки"
                  className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
                />
                <button
                  type="button"
                  onClick={calculateRoute}
                  disabled={calculating || !address.trim()}
                  className="px-4 py-3 bg-[#62bb46] text-white rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50"
                >
                  {calculating ? 'Расчёт...' : 'Найти'}
                </button>
              </div>
              {addressError && (
                <p className="text-sm text-red-500 mt-1">{addressError}</p>
              )}
            </div>

            {/* Map */}
            {nearestPoint && (
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
                    {prices && (
                      <span className="text-sm text-gray-500 ml-2">
                        ({Math.round(prices.floor_lift_price).toLocaleString('ru-RU')} ₽ за этаж)
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
                      = {Math.round(liftCost()).toLocaleString('ru-RU')} ₽
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
                  {prices && (
                    <span className="text-gray-500">
                      {Math.round(prices.elevator_lift_price).toLocaleString('ru-RU')} ₽
                    </span>
                  )}
                </label>
              </div>
            </div>

            {/* Total delivery cost */}
            <div className="bg-[#62bb46]/10 rounded-lg p-4 space-y-2">
              {assemblyCost() > 0 && (
                <div className="flex justify-between items-center text-sm">
                  <span className="text-gray-600">Выезд сборщика:</span>
                  <span className="font-medium">{assemblyCost().toLocaleString('ru-RU')} ₽</span>
                </div>
              )}
              <div className="flex justify-between items-center">
                <span className="font-medium text-gray-900">Итого за доставку:</span>
                <span className="text-xl font-bold text-[#62bb46]">
                  {Math.round(deliveryCost() + liftCost() + assemblyCost()).toLocaleString('ru-RU')} ₽
                </span>
              </div>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
