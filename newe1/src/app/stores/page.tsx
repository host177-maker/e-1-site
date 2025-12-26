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

interface RegionData {
  id: number;
  name: string;
  salonCount: number;
}

export default function StoresPage() {
  const [allSalons, setAllSalons] = useState<Salon[]>([]); // All salons for map
  const [regions, setRegions] = useState<RegionData[]>([]);
  const [cities, setCities] = useState<CityData[]>([]);
  const [filteredCities, setFilteredCities] = useState<CityData[]>([]);
  const [selectedCity, setSelectedCity] = useState<string>('');
  const [selectedRegion, setSelectedRegion] = useState<string>('');
  const [loading, setLoading] = useState(true);
  const [citiesLoading, setCitiesLoading] = useState(false);
  const [mapReady, setMapReady] = useState(false);

  const mapInstanceRef = useRef<any>(null);
  const clustererRef = useRef<any>(null);
  const markersInitialized = useRef(false);

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

  // Fetch regions on mount
  useEffect(() => {
    fetch('/api/salons/cities')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          setCities(data.data.cities);
          setFilteredCities(data.data.cities);
          // Extract unique regions from cities
          const uniqueRegions = [...new Set(data.data.cities.map((c: CityData) => c.region))]
            .filter(Boolean)
            .sort() as string[];
          setRegions(uniqueRegions.map((name, idx) => ({
            id: idx + 1,
            name,
            salonCount: data.data.cities.filter((c: CityData) => c.region === name).reduce((acc: number, c: CityData) => acc + c.count, 0),
          })));
        }
      })
      .catch(console.error);
  }, []);

  // Update filtered cities when region changes
  useEffect(() => {
    if (selectedRegion) {
      setCitiesLoading(true);
      // Filter cities by selected region
      const regionCities = cities.filter(c => c.region === selectedRegion);
      setFilteredCities(regionCities);
      setCitiesLoading(false);
    } else {
      setFilteredCities(cities);
    }
  }, [selectedRegion, cities]);

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
                  <option key={region.name} value={region.name}>
                    {region.name} ({region.salonCount})
                  </option>
                ))}
              </select>
            </div>

            {/* City select */}
            <div className="flex-1 max-w-xs">
              <select
                value={selectedCity}
                onChange={(e) => handleCityChange(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
                disabled={citiesLoading}
              >
                <option value="">
                  {citiesLoading ? 'Загрузка...' : selectedRegion ? '— Выберите город —' : '— Сначала выберите регион —'}
                </option>
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

                <div className="divide-y divide-gray-200">
                  {regionSalons.map(salon => (
                    <div
                      key={salon.id}
                      className="py-3 flex flex-col md:flex-row md:items-center gap-2 md:gap-6"
                    >
                      <div className="flex-1">
                        <span className="font-medium text-[#3d4543]">{salon.name}</span>
                        {salon.address && (
                          <span className="text-gray-500 ml-2">{salon.address}</span>
                        )}
                      </div>

                      <div className="flex items-center gap-4 text-sm">
                        {salon.phone && (
                          <a
                            href={`tel:${salon.phone.replace(/[^\d+]/g, '')}`}
                            className="text-[#7cb342] hover:underline whitespace-nowrap"
                          >
                            {salon.phone}
                          </a>
                        )}

                        {salon.working_hours && (
                          <span className="text-gray-500 whitespace-nowrap">
                            {salon.working_hours}
                          </span>
                        )}

                        {salon.latitude && salon.longitude && (
                          <button
                            onClick={() => {
                              const map = mapInstanceRef.current;
                              if (map) {
                                map.setCenter([salon.longitude, salon.latitude], 15, {
                                  duration: 500,
                                });
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                              }
                            }}
                            className="text-[#7cb342] hover:underline whitespace-nowrap"
                          >
                            на карте
                          </button>
                        )}
                      </div>
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
