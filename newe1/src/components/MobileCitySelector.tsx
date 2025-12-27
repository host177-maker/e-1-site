'use client';

import { useState, useEffect, useMemo } from 'react';
import { useCity } from '@/context/CityContext';

interface CityData {
  city: string;
  region: string;
  count: number;
}

interface MobileCitySelectorProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function MobileCitySelector({ isOpen, onClose }: MobileCitySelectorProps) {
  const { setCity } = useCity();
  const [searchQuery, setSearchQuery] = useState('');
  const [cities, setCities] = useState<CityData[]>([]);
  const [loading, setLoading] = useState(true);

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

  // Filter and sort cities - simple flat list without regions
  const filteredCities = useMemo(() => {
    let result = cities.filter(c => c.count > 0);

    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      result = result.filter(c => c.city.toLowerCase().includes(query));
    }

    // Sort alphabetically
    return result.sort((a, b) => a.city.localeCompare(b.city, 'ru'));
  }, [cities, searchQuery]);

  const handleSelectCity = (cityData: CityData) => {
    setCity({
      name: cityData.city,
      region: cityData.region,
    });
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[100] flex items-end justify-center">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/50"
        onClick={onClose}
      />

      {/* Modal - slides up from bottom on mobile */}
      <div className="relative bg-white rounded-t-2xl shadow-2xl w-full max-h-[80vh] flex flex-col animate-in slide-in-from-bottom duration-300">
        {/* Header */}
        <div className="flex items-center justify-between p-4 border-b">
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

        {/* Search */}
        <div className="p-4 border-b">
          <div className="relative">
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Введите название города"
              className="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] focus:border-transparent"
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
        </div>

        {/* Cities list */}
        <div className="flex-1 overflow-y-auto">
          {loading ? (
            <div className="flex items-center justify-center py-8">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#62bb46]"></div>
            </div>
          ) : filteredCities.length === 0 ? (
            <div className="text-center py-8 text-gray-500 text-sm">
              Города не найдены
            </div>
          ) : (
            <ul className="divide-y divide-gray-100">
              {filteredCities.map((cityData) => (
                <li key={cityData.city}>
                  <button
                    onClick={() => handleSelectCity(cityData)}
                    className="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center gap-3"
                  >
                    <svg className="w-4 h-4 text-[#62bb46] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span className="text-[#3d4543]">{cityData.city}</span>
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </div>
  );
}
