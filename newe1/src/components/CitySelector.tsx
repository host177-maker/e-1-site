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
  const [showOnlyWithSalons, setShowOnlyWithSalons] = useState(true);

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

  // Filter cities based on search and salon filter
  const filteredCities = useMemo(() => {
    let result = cities;

    if (showOnlyWithSalons) {
      result = result.filter(c => c.count > 0);
    }

    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      result = result.filter(c => c.city.toLowerCase().includes(query));
    }

    return result;
  }, [cities, searchQuery, showOnlyWithSalons]);

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
    // Sort letters
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
    // Sort regions
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
        <div className="flex items-center justify-between p-4 border-b">
          <h2 className="text-xl font-bold text-[#3d4543]">Выберите город</h2>
          <button
            onClick={onClose}
            className="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Search and filters */}
        <div className="p-4 border-b">
          <div className="flex flex-col sm:flex-row gap-4">
            {/* Search input */}
            <div className="flex-1 relative">
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Введите название города"
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
              />
              <svg
                className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            {/* View mode tabs */}
            <div className="flex items-center gap-2 text-sm">
              <button
                onClick={() => setViewMode('alphabet')}
                className={`px-3 py-2 rounded transition-colors ${
                  viewMode === 'alphabet'
                    ? 'text-[#7cb342] font-medium underline'
                    : 'text-gray-600 hover:text-[#7cb342]'
                }`}
              >
                По алфавиту
              </button>
              <button
                onClick={() => setViewMode('regions')}
                className={`px-3 py-2 rounded transition-colors ${
                  viewMode === 'regions'
                    ? 'text-[#7cb342] font-medium underline'
                    : 'text-gray-600 hover:text-[#7cb342]'
                }`}
              >
                По регионам
              </button>
            </div>

            {/* Salon filter */}
            <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer whitespace-nowrap">
              <input
                type="checkbox"
                checked={showOnlyWithSalons}
                onChange={(e) => setShowOnlyWithSalons(e.target.checked)}
                className="w-4 h-4 text-[#7cb342] rounded border-gray-300 focus:ring-[#7cb342]"
              />
              <span className="flex items-center gap-1">
                <svg className="w-4 h-4 text-[#7cb342]" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                </svg>
                Города с салоном-магазином
              </span>
            </label>
          </div>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-4">
          {loading ? (
            <div className="flex items-center justify-center py-12">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#7cb342]"></div>
            </div>
          ) : viewMode === 'alphabet' ? (
            /* Alphabet view */
            <div className="space-y-6">
              {Object.entries(citiesByLetter).map(([letter, letterCities]) => (
                <div key={letter}>
                  <h3 className="text-lg font-bold text-[#3d4543] mb-2">{letter}</h3>
                  <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    {letterCities.map(cityData => (
                      <button
                        key={cityData.city}
                        onClick={() => handleSelectCity(cityData)}
                        className="flex items-center gap-1 text-left text-sm hover:text-[#7cb342] transition-colors py-1"
                      >
                        {cityData.count > 0 && (
                          <svg className="w-4 h-4 text-[#7cb342] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                          </svg>
                        )}
                        <span className={cityData.count > 0 ? 'underline' : ''}>{cityData.city}</span>
                      </button>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          ) : (
            /* Regions view */
            <div className="space-y-6">
              {Object.entries(citiesByRegion).map(([region, regionCities]) => (
                <div key={region}>
                  <h3 className="text-base font-bold text-[#3d4543] mb-2">{region}</h3>
                  <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    {regionCities.map(cityData => (
                      <button
                        key={cityData.city}
                        onClick={() => handleSelectCity(cityData)}
                        className="flex items-center gap-1 text-left text-sm hover:text-[#7cb342] transition-colors py-1"
                      >
                        {cityData.count > 0 && (
                          <svg className="w-4 h-4 text-[#7cb342] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                          </svg>
                        )}
                        <span className={cityData.count > 0 ? 'underline' : ''}>{cityData.city}</span>
                      </button>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          )}

          {!loading && filteredCities.length === 0 && (
            <div className="text-center py-12 text-gray-500">
              Города не найдены
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
