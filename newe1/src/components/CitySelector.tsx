'use client';

import { useState, useEffect, useMemo } from 'react';
import { useCity } from '@/context/CityContext';

interface CityData {
  city: string;
  region: string;
  count: number;
}

interface CitySelectorProps {
  isOpen: boolean;
  onClose: () => void;
}

type ViewMode = 'alphabet' | 'regions';

export default function CitySelector({ isOpen, onClose }: CitySelectorProps) {
  const { setCity } = useCity();
  const [viewMode, setViewMode] = useState<ViewMode>('alphabet');
  const [searchQuery, setSearchQuery] = useState('');
  const [cities, setCities] = useState<CityData[]>([]);
  const [loading, setLoading] = useState(true);
  const [geoLoading, setGeoLoading] = useState(false);
  const [geoError, setGeoError] = useState<string | null>(null);

  // Fetch cities on mount
  useEffect(() => {
    if (isOpen && cities.length === 0) {
      fetchCities();
    }
  }, [isOpen, cities.length]);

  const fetchCities = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/salons/cities');
      const data = await response.json();
      if (data.success) {
        setCities(data.data.cities);
      }
    } catch (error) {
      console.error('Failed to fetch cities:', error);
    } finally {
      setLoading(false);
    }
  };

  // Filter cities based on search - show only cities with salons
  const filteredCities = useMemo(() => {
    let result = cities.filter(c => c.count > 0);

    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      result = result.filter(c => c.city.toLowerCase().includes(query));
    }

    return result;
  }, [cities, searchQuery]);

  // Group cities by first letter for alphabet view
  const citiesByLetter = useMemo(() => {
    const groups: Record<string, CityData[]> = {};
    filteredCities.forEach(city => {
      const letter = city.city[0].toUpperCase();
      if (!groups[letter]) {
        groups[letter] = [];
      }
      groups[letter].push(city);
    });
    return Object.keys(groups)
      .sort()
      .reduce((acc, letter) => {
        acc[letter] = groups[letter].sort((a, b) => a.city.localeCompare(b.city, 'ru'));
        return acc;
      }, {} as Record<string, CityData[]>);
  }, [filteredCities]);

  // Group cities by region
  const citiesByRegion = useMemo(() => {
    const groups: Record<string, CityData[]> = {};
    filteredCities.forEach(city => {
      const region = city.region || 'Без региона';
      if (!groups[region]) {
        groups[region] = [];
      }
      groups[region].push(city);
    });
    return Object.keys(groups)
      .sort((a, b) => a.localeCompare(b, 'ru'))
      .reduce((acc, region) => {
        acc[region] = groups[region].sort((a, b) => a.city.localeCompare(b.city, 'ru'));
        return acc;
      }, {} as Record<string, CityData[]>);
  }, [filteredCities]);

  const handleSelectCity = (cityData: CityData) => {
    setCity({
      name: cityData.city,
      region: cityData.region,
    });
    onClose();
  };

  // Find nearest city based on coordinates
  const findNearestCity = async (lat: number, lon: number) => {
    try {
      // Use Yandex Geocoder to get city name from coordinates
      const response = await fetch(
        `https://geocode-maps.yandex.ru/1.x/?apikey=51e35aa4-fa5e-432e-a7c6-e5e71105ec3a&format=json&geocode=${lon},${lat}&kind=locality`
      );
      const data = await response.json();

      const geoObject = data.response?.GeoObjectCollection?.featureMember?.[0]?.GeoObject;
      if (geoObject) {
        const cityName = geoObject.name;

        // Find this city in our list
        const foundCity = cities.find(c =>
          c.city.toLowerCase() === cityName.toLowerCase()
        );

        if (foundCity) {
          return foundCity;
        }

        // If exact match not found, find nearest city with salon
        // by searching in nearby regions
        const addressComponents = geoObject.metaDataProperty?.GeocoderMetaData?.Address?.Components || [];
        const regionComponent = addressComponents.find((c: { kind: string }) =>
          c.kind === 'province' || c.kind === 'area'
        );

        if (regionComponent) {
          const regionCities = cities.filter(c =>
            c.region?.toLowerCase().includes(regionComponent.name?.toLowerCase()) && c.count > 0
          );
          if (regionCities.length > 0) {
            return regionCities[0];
          }
        }
      }

      // Fallback: return first city with salons
      return cities.find(c => c.count > 0) || null;
    } catch (error) {
      console.error('Geocoding error:', error);
      return null;
    }
  };

  const handleAutoDetect = () => {
    if (!navigator.geolocation) {
      setGeoError('Геолокация не поддерживается вашим браузером');
      return;
    }

    setGeoLoading(true);
    setGeoError(null);

    navigator.geolocation.getCurrentPosition(
      async (position) => {
        const { latitude, longitude } = position.coords;
        const nearestCity = await findNearestCity(latitude, longitude);

        if (nearestCity) {
          setCity({
            name: nearestCity.city,
            region: nearestCity.region,
          });
          onClose();
        } else {
          setGeoError('Не удалось определить ближайший город');
        }
        setGeoLoading(false);
      },
      (error) => {
        setGeoLoading(false);
        switch (error.code) {
          case error.PERMISSION_DENIED:
            setGeoError('Доступ к геолокации запрещен');
            break;
          case error.POSITION_UNAVAILABLE:
            setGeoError('Информация о местоположении недоступна');
            break;
          case error.TIMEOUT:
            setGeoError('Время ожидания истекло');
            break;
          default:
            setGeoError('Ошибка определения местоположения');
        }
      },
      {
        enableHighAccuracy: false,
        timeout: 10000,
        maximumAge: 300000,
      }
    );
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[100] flex items-start justify-center pt-10 px-4">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="relative bg-white rounded-lg shadow-2xl w-full max-w-3xl max-h-[80vh] flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between p-3 border-b">
          <h2 className="text-lg font-bold text-[#3d4543]">Выберите город</h2>
          <button
            onClick={onClose}
            className="p-1.5 hover:bg-gray-100 rounded-full transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Search and filters */}
        <div className="p-3 border-b">
          <div className="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
            {/* Search input */}
            <div className="flex-1 relative">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Введите название города"
                className="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
              />
              <svg
                className="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            {/* View mode tabs */}
            <div className="flex items-center gap-1 text-sm">
              <button
                onClick={() => setViewMode('alphabet')}
                className={`px-2 py-1 rounded transition-colors ${
                  viewMode === 'alphabet'
                    ? 'text-[#7cb342] font-medium underline'
                    : 'text-gray-600 hover:text-[#7cb342]'
                }`}
              >
                По алфавиту
              </button>
              <button
                onClick={() => setViewMode('regions')}
                className={`px-2 py-1 rounded transition-colors ${
                  viewMode === 'regions'
                    ? 'text-[#7cb342] font-medium underline'
                    : 'text-gray-600 hover:text-[#7cb342]'
                }`}
              >
                По регионам
              </button>
            </div>

            {/* Auto-detect button */}
            <button
              onClick={handleAutoDetect}
              disabled={geoLoading}
              className="flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm bg-[#7cb342] text-white rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50 whitespace-nowrap"
            >
              {geoLoading ? (
                <div className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
              ) : (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              )}
              Автоопределение
            </button>
          </div>

          {geoError && (
            <div className="mt-2 text-sm text-red-500">{geoError}</div>
          )}
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-3">
          {loading ? (
            <div className="flex items-center justify-center py-8">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#7cb342]"></div>
            </div>
          ) : viewMode === 'alphabet' ? (
            /* Alphabet view - compact */
            <div className="space-y-3">
              {Object.entries(citiesByLetter).map(([letter, letterCities]) => (
                <div key={letter}>
                  <h3 className="text-sm font-bold text-[#3d4543] mb-1">{letter}</h3>
                  <div className="flex flex-wrap gap-x-3 gap-y-0.5">
                    {letterCities.map(cityData => (
                      <button
                        key={cityData.city}
                        onClick={() => handleSelectCity(cityData)}
                        className="flex items-center gap-0.5 text-xs hover:text-[#7cb342] transition-colors py-0.5"
                      >
                        <svg className="w-3 h-3 text-[#7cb342] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                        </svg>
                        <span className="underline">{cityData.city}</span>
                      </button>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            /* Regions view - compact */
            <div className="space-y-3">
              {Object.entries(citiesByRegion).map(([region, regionCities]) => (
                <div key={region}>
                  <h3 className="text-sm font-bold text-[#3d4543] mb-1">{region}</h3>
                  <div className="flex flex-wrap gap-x-3 gap-y-0.5">
                    {regionCities.map(cityData => (
                      <button
                        key={cityData.city}
                        onClick={() => handleSelectCity(cityData)}
                        className="flex items-center gap-0.5 text-xs hover:text-[#7cb342] transition-colors py-0.5"
                      >
                        <svg className="w-3 h-3 text-[#7cb342] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                        </svg>
                        <span className="underline">{cityData.city}</span>
                      </button>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          )}

          {!loading && filteredCities.length === 0 && (
            <div className="text-center py-8 text-gray-500 text-sm">
              Города не найдены
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
