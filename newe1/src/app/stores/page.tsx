/* eslint-disable @typescript-eslint/no-explicit-any */
'use client';

import { useEffect, useState, useCallback, useRef } from 'react';
import Script from 'next/script';

interface Salon {
  id: number;
  name: string;
  city: string;
  region: string;
  address: string;
  email: string;
  phone: string;
  working_hours: string;
  latitude: number | null;
  longitude: number | null;
}

interface CityData {
  city: string;
  region: string;
  count: number;
}

export default function StoresPage() {
  const [allSalons, setAllSalons] = useState<Salon[]>([]); // All salons for map
  const [cities, setCities] = useState<CityData[]>([]);
  const [selectedCity, setSelectedCity] = useState<string>('');
  const [selectedRegion, setSelectedRegion] = useState<string>('');
  const [loading, setLoading] = useState(true);
  const [mapReady, setMapReady] = useState(false);

  const mapInstanceRef = useRef<any>(null);
  const clustererRef = useRef<any>(null);
  const markersInitialized = useRef(false);

  // Get unique regions from cities
  const regions = [...new Set(cities.map(c => c.region))].sort();

  // Get cities for selected region
  const filteredCities = selectedRegion
    ? cities.filter(c => c.region === selectedRegion)
    : cities;

  // Filter salons for list display (based on selected city/region)
  const filteredSalons = allSalons.filter(salon => {
    if (selectedCity) {
      return salon.city === selectedCity;
    }
    if (selectedRegion) {
      return salon.region === selectedRegion;
    }
    return true;
  });

  // Fetch cities on mount
  useEffect(() => {
    fetch('/api/salons/cities')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          setCities(data.data.cities);
        }
      })
      .catch(console.error);
  }, []);

  // Fetch ALL salons once on mount (for map)
  useEffect(() => {
    setLoading(true);
    fetch('/api/salons')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          setAllSalons(data.data);
        }
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, []);

  // Create map markers once when all salons are loaded
  const initMapMarkers = useCallback(() => {
    const map = mapInstanceRef.current;
    if (!map || typeof window === 'undefined' || !(window as any).ymaps) return;
    if (markersInitialized.current) return;

    const ymaps = (window as any).ymaps;

    // Create placemarks for all salons with coordinates
    const placemarks = allSalons
      .filter(s => s.latitude && s.longitude)
      .map(salon => {
        return new ymaps.Placemark(
          [salon.longitude, salon.latitude], // longlat order
          {
            hintContent: salon.name,
            balloonContentHeader: salon.name,
            balloonContentBody: `
              <div style="padding: 10px 0;">
                <p style="margin: 0 0 8px;"><strong>Город:</strong> ${salon.city}</p>
                <p style="margin: 0 0 8px;"><strong>Адрес:</strong> ${salon.address || 'Не указан'}</p>
                <p style="margin: 0 0 8px;"><strong>Телефон:</strong> ${salon.phone || 'Не указан'}</p>
                <p style="margin: 0 0 8px;"><strong>Email:</strong> ${salon.email || 'Не указан'}</p>
                <p style="margin: 0;"><strong>Режим работы:</strong> ${salon.working_hours || 'Не указан'}</p>
              </div>
            `,
            balloonContentFooter: `<a href="mailto:${salon.email}" style="color: #7cb342;">Написать в салон</a>`,
          },
          {
            preset: 'islands#greenDotIcon',
          }
        );
      });

    if (placemarks.length === 0) return;

    // Create clusterer
    const newClusterer = new ymaps.Clusterer({
      preset: 'islands#greenClusterIcons',
      groupByCoordinates: false,
      clusterDisableClickZoom: false,
      clusterHideIconOnBalloonOpen: false,
      geoObjectHideIconOnBalloonOpen: false,
    });

    newClusterer.add(placemarks);
    map.geoObjects.add(newClusterer);
    clustererRef.current = newClusterer;
    markersInitialized.current = true;

    // Set initial bounds to show all markers
    const bounds = newClusterer.getBounds();
    if (bounds) {
      map.setBounds(bounds, {
        checkZoomRange: true,
        zoomMargin: 50,
      });
    }
  }, [allSalons]);

  // Initialize markers when map is ready and salons are loaded
  useEffect(() => {
    if (mapReady && allSalons.length > 0 && !markersInitialized.current) {
      initMapMarkers();
    }
  }, [mapReady, allSalons, initMapMarkers]);

  // Center map on selected city/region (without changing markers)
  useEffect(() => {
    const map = mapInstanceRef.current;
    if (!map || !mapReady || typeof window === 'undefined' || !(window as any).ymaps) return;

    const ymaps = (window as any).ymaps;

    // Get salons for the selected filter
    const targetSalons = allSalons.filter(salon => {
      if (selectedCity) {
        return salon.city === selectedCity;
      }
      if (selectedRegion) {
        return salon.region === selectedRegion;
      }
      return false;
    });

    if (targetSalons.length === 0) {
      // No filter or no salons found - show all Russia
      if (!selectedCity && !selectedRegion) {
        map.setCenter([82.920430, 55.030199], 4, { duration: 300 });
      }
      return;
    }

    // Calculate bounds for selected salons
    const coords = targetSalons
      .filter(s => s.latitude && s.longitude)
      .map(s => [s.longitude!, s.latitude!]);

    if (coords.length === 0) return;

    if (coords.length === 1) {
      // Single salon - center on it with reasonable zoom
      map.setCenter(coords[0], 12, { duration: 300 });
    } else {
      // Multiple salons - fit bounds
      const tempClusterer = new ymaps.Clusterer();
      coords.forEach(coord => {
        tempClusterer.add(new ymaps.Placemark(coord));
      });
      const bounds = tempClusterer.getBounds();
      if (bounds) {
        map.setBounds(bounds, {
          checkZoomRange: true,
          zoomMargin: 50,
        }).then(() => {
          const currentZoom = map.getZoom();
          if (currentZoom > 14) {
            map.setZoom(14);
          }
        });
      }
    }
  }, [selectedCity, selectedRegion, allSalons, mapReady]);

  // Initialize map
  const initMap = useCallback(() => {
    if (typeof window === 'undefined' || !(window as any).ymaps) return;
    if (mapInstanceRef.current) return; // Already initialized

    const ymaps = (window as any).ymaps;
    ymaps.ready(() => {
      const map = new ymaps.Map('stores-map', {
        center: [82.920430, 55.030199], // Центр России (longlat)
        zoom: 4,
        controls: ['zoomControl', 'geolocationControl', 'fullscreenControl'],
      });

      map.controls.get('zoomControl').options.set({ size: 'small' });

      mapInstanceRef.current = map;
      setMapReady(true);
    });
  }, []);

  useEffect(() => {
    if ((window as any).ymaps) {
      initMap();
    } else {
      (window as any).initStoresMap = initMap;
    }
  }, [initMap]);

  const handleRegionChange = (region: string) => {
    setSelectedRegion(region);
    setSelectedCity('');
  };

  const handleCityChange = (city: string) => {
    setSelectedCity(city);
    // Find and set region for selected city
    const cityData = cities.find(c => c.city === city);
    if (cityData && cityData.region) {
      setSelectedRegion(cityData.region);
    }
  };

  const clearFilters = () => {
    setSelectedCity('');
    setSelectedRegion('');
  };

  // Group filtered salons by region for display
  const salonsByRegion = filteredSalons.reduce((acc, salon) => {
    const region = salon.region || 'Без региона';
    if (!acc[region]) {
      acc[region] = [];
    }
    acc[region].push(salon);
    return acc;
  }, {} as Record<string, Salon[]>);

  return (
    <>
      <Script
        src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=51e35aa4-fa5e-432e-a7c6-e5e71105ec3a"
        strategy="afterInteractive"
        onLoad={() => {
          if ((window as any).initStoresMap) {
            (window as any).initStoresMap();
          }
        }}
      />

      <div className="bg-white">
        {/* Hero section */}
        <div className="bg-[#f5f5f5] py-8">
          <div className="container-custom">
            <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543]">
              Адреса салонов
            </h1>
          </div>
        </div>

        {/* Filters and Info */}
        <div className="container-custom py-6">
          <div className="flex flex-col md:flex-row md:items-center gap-4 mb-6">
            {/* Region select */}
            <div className="flex-1 max-w-xs">
              <select
                value={selectedRegion}
                onChange={(e) => handleRegionChange(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
              >
                <option value="">— Выберите регион —</option>
                {regions.map(region => (
                  <option key={region} value={region}>{region}</option>
                ))}
              </select>
            </div>

            {/* City select */}
            <div className="flex-1 max-w-xs">
              <select
                value={selectedCity}
                onChange={(e) => handleCityChange(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
              >
                <option value="">— Выберите город —</option>
                {filteredCities.map(city => (
                  <option key={city.city} value={city.city}>
                    {city.city} ({city.count})
                  </option>
                ))}
              </select>
            </div>

            {/* Clear filters */}
            {(selectedCity || selectedRegion) && (
              <button
                onClick={clearFilters}
                className="text-[#7cb342] hover:text-[#689f38] underline"
              >
                Сбросить фильтры
              </button>
            )}

            {/* Contact info */}
            <div className="ml-auto text-right hidden md:block">
              <p className="text-sm text-gray-500">Справочная служба</p>
              <p className="text-lg font-bold text-[#3d4543]">8 (861) 290-85-10</p>
              <p className="text-sm text-gray-500">
                <a href="mailto:best@e-1.ru" className="text-[#7cb342] hover:underline">best@e-1.ru</a>
                {' / '}
                <a href="mailto:service@e-1.ru" className="text-[#7cb342] hover:underline">service@e-1.ru</a>
              </p>
            </div>
          </div>

          {/* Map */}
          <div className="mb-8">
            <div
              id="stores-map"
              className="w-full h-[400px] md:h-[500px] rounded-lg border border-gray-200"
            />
          </div>

          {/* Salons count */}
          <div className="mb-6">
            <p className="text-gray-600">
              {loading ? (
                'Загрузка...'
              ) : (
                <>
                  Найдено салонов: <strong>{filteredSalons.length}</strong>
                  {selectedCity && ` в городе ${selectedCity}`}
                  {selectedRegion && !selectedCity && ` в регионе ${selectedRegion}`}
                  {!selectedCity && !selectedRegion && ` (всего ${allSalons.length})`}
                </>
              )}
            </p>
          </div>

          {/* Salons list grouped by region */}
          <div className="space-y-8">
            {Object.entries(salonsByRegion).map(([region, regionSalons]) => (
              <div key={region}>
                <h2 className="text-xl font-bold text-[#3d4543] mb-4 pb-2 border-b border-gray-200">
                  {region}
                  {regionSalons.length > 0 && (
                    <span className="text-gray-400 font-normal ml-2">
                      ({regionSalons.length})
                    </span>
                  )}
                </h2>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  {regionSalons.map(salon => (
                    <div
                      key={salon.id}
                      className="bg-[#f9f9f9] rounded-lg p-4 hover:shadow-md transition-shadow"
                    >
                      <h3 className="font-bold text-[#3d4543] mb-2">
                        {salon.name}
                      </h3>

                      <div className="space-y-1 text-sm">
                        <p className="text-gray-600">
                          <span className="text-gray-400">Адрес: </span>
                          {salon.address || 'Не указан'}
                        </p>

                        {salon.phone && (
                          <p className="text-gray-600">
                            <span className="text-gray-400">Тел: </span>
                            <a
                              href={`tel:${salon.phone.replace(/[^\d+]/g, '')}`}
                              className="text-[#7cb342] hover:underline"
                            >
                              {salon.phone}
                            </a>
                          </p>
                        )}

                        {salon.email && (
                          <p className="text-gray-600">
                            <span className="text-gray-400">Email: </span>
                            <a
                              href={`mailto:${salon.email}`}
                              className="text-[#7cb342] hover:underline"
                            >
                              {salon.email}
                            </a>
                          </p>
                        )}

                        {salon.working_hours && (
                          <p className="text-gray-600">
                            <span className="text-gray-400">Режим: </span>
                            {salon.working_hours}
                          </p>
                        )}
                      </div>

                      {salon.latitude && salon.longitude && (
                        <button
                          onClick={() => {
                            const map = mapInstanceRef.current;
                            if (map) {
                              map.setCenter([salon.longitude, salon.latitude], 15, {
                                duration: 500,
                              });
                            }
                          }}
                          className="mt-3 text-sm text-[#7cb342] hover:underline flex items-center gap-1"
                        >
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                          </svg>
                          показать на карте
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>

          {!loading && filteredSalons.length === 0 && (
            <div className="text-center py-12">
              <p className="text-gray-500 text-lg">
                Салоны не найдены
              </p>
              {(selectedCity || selectedRegion) && (
                <button
                  onClick={clearFilters}
                  className="mt-4 text-[#7cb342] hover:underline"
                >
                  Сбросить фильтры
                </button>
              )}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
