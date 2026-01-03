'use client';

import { useState, useEffect } from 'react';

interface FilterOptions {
  doorTypes: { id: number; name: string; slug: string; count: number }[];
  series: { id: number; name: string; slug: string; count: number }[];
  widthRange: { min: number; max: number };
  heights: { value: number; count: number }[];
  depths: { value: number; count: number }[];
  priceRange: { min: number; max: number };
}

interface FilterValues {
  doorTypes: string[];
  series: string[];
  widthRange: string;
  heights: number[];
  depths: number[];
  priceRange: string;
}

interface CatalogFilterProps {
  filterOptions: FilterOptions | null;
  filters: FilterValues;
  onFiltersChange: (filters: FilterValues) => void;
  isOpen: boolean;
  onClose: () => void;
  isMobileOnly?: boolean;
}

// Маппинг типов шкафов для отображения
const doorTypeLabels: Record<string, string> = {
  'kupe': 'Шкаф-купе',
  'raspashnoy': 'Шкаф распашной',
  'garmoshka': 'Шкаф гармошка',
  'tolkatel': 'Гардероб',
  'bez-dverey': 'Без дверей',
};

// Градации цены
const priceRanges = [
  { key: '0-19999', label: 'до 19 999', min: 0, max: 19999 },
  { key: '20000-34999', label: '20 000 - 34 999', min: 20000, max: 34999 },
  { key: '35000-49999', label: '35 000 - 49 999', min: 35000, max: 49999 },
  { key: '50000-79999', label: '50 000 - 79 999', min: 50000, max: 79999 },
  { key: '80000-109999', label: '80 000 - 109 999', min: 80000, max: 109999 },
  { key: '110000-999999', label: 'от 110 000', min: 110000, max: 999999 },
];

// Градации ширины
const widthRanges = [
  { key: '0-109', label: 'до 109', min: 0, max: 109 },
  { key: '110-139', label: '110-139', min: 110, max: 139 },
  { key: '140-161', label: '140-161', min: 140, max: 161 },
  { key: '162-200', label: '162-200', min: 162, max: 200 },
  { key: '201-239', label: '201-239', min: 201, max: 239 },
  { key: '240-999', label: 'от 240', min: 240, max: 999 },
];

export default function CatalogFilter({
  filterOptions,
  filters,
  onFiltersChange,
  isOpen,
  onClose,
  isMobileOnly = false,
}: CatalogFilterProps) {
  const [localFilters, setLocalFilters] = useState<FilterValues>(filters);

  // Синхронизация с внешними фильтрами
  useEffect(() => {
    setLocalFilters(filters);
  }, [filters]);

  const handleDoorTypeToggle = (slug: string) => {
    const newDoorTypes = localFilters.doorTypes.includes(slug)
      ? localFilters.doorTypes.filter(dt => dt !== slug)
      : [...localFilters.doorTypes, slug];
    const newFilters = { ...localFilters, doorTypes: newDoorTypes };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handleSeriesToggle = (slug: string) => {
    const newSeries = localFilters.series.includes(slug)
      ? localFilters.series.filter(s => s !== slug)
      : [...localFilters.series, slug];
    const newFilters = { ...localFilters, series: newSeries };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handleHeightToggle = (height: number) => {
    const newHeights = localFilters.heights.includes(height)
      ? localFilters.heights.filter(h => h !== height)
      : [...localFilters.heights, height];
    const newFilters = { ...localFilters, heights: newHeights };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handleDepthToggle = (depth: number) => {
    const newDepths = localFilters.depths.includes(depth)
      ? localFilters.depths.filter(d => d !== depth)
      : [...localFilters.depths, depth];
    const newFilters = { ...localFilters, depths: newDepths };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handleWidthRangeChange = (key: string) => {
    const newFilters = { ...localFilters, widthRange: localFilters.widthRange === key ? '' : key };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handlePriceRangeChange = (key: string) => {
    const newFilters = { ...localFilters, priceRange: localFilters.priceRange === key ? '' : key };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const resetFilters = () => {
    const defaultFilters: FilterValues = {
      doorTypes: [],
      series: [],
      widthRange: '',
      heights: [],
      depths: [],
      priceRange: '',
    };
    setLocalFilters(defaultFilters);
    onFiltersChange(defaultFilters);
  };

  const hasActiveFilters =
    localFilters.doorTypes.length > 0 ||
    localFilters.series.length > 0 ||
    localFilters.heights.length > 0 ||
    localFilters.depths.length > 0 ||
    localFilters.widthRange !== '' ||
    localFilters.priceRange !== '';

  if (!filterOptions) {
    return (
      <div className="bg-white rounded-xl shadow-sm p-4 min-h-[400px]">
        <div className="animate-pulse space-y-4">
          <div className="h-4 bg-gray-200 rounded w-1/2"></div>
          <div className="space-y-2">
            <div className="h-6 bg-gray-200 rounded"></div>
            <div className="h-6 bg-gray-200 rounded"></div>
            <div className="h-6 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
    );
  }

  const filterContent = (
    <div className="space-y-5">
      {/* Тип шкафа */}
      <div>
        <h3 className="font-medium text-gray-900 mb-2 text-sm">Тип шкафа</h3>
        <div className="space-y-1.5">
          {filterOptions.doorTypes
            .filter(dt => doorTypeLabels[dt.slug])
            .map(dt => (
              <label key={dt.id} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={localFilters.doorTypes.includes(dt.slug)}
                  onChange={() => handleDoorTypeToggle(dt.slug)}
                  className="w-4 h-4 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
                />
                <span className="text-sm text-gray-700 flex-1">{doorTypeLabels[dt.slug]}</span>
                <span className="text-xs text-gray-400">({dt.count})</span>
              </label>
            ))}
        </div>
      </div>

      {/* Серия */}
      <div>
        <h3 className="font-medium text-gray-900 mb-2 text-sm">Серия</h3>
        <div className="space-y-1.5">
          {filterOptions.series.map(s => (
            <label key={s.id} className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.series.includes(s.slug)}
                onChange={() => handleSeriesToggle(s.slug)}
                className="w-4 h-4 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-sm text-gray-700 flex-1">{s.name}</span>
              <span className="text-xs text-gray-400">({s.count})</span>
            </label>
          ))}
        </div>
      </div>

      {/* Ширина */}
      <div>
        <h3 className="font-medium text-gray-900 mb-2 text-sm">Ширина, см</h3>
        <div className="grid grid-cols-2 gap-1.5">
          {widthRanges.map(range => (
            <label key={range.key} className="flex items-center gap-1.5 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.widthRange === range.key}
                onChange={() => handleWidthRangeChange(range.key)}
                className="w-3.5 h-3.5 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-xs text-gray-700">{range.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Высота */}
      {filterOptions.heights.length > 0 && (
        <div>
          <h3 className="font-medium text-gray-900 mb-2 text-sm">Высота, см</h3>
          <div className="flex flex-wrap gap-1.5">
            {filterOptions.heights.slice(0, 8).map(h => (
              <button
                key={h.value}
                onClick={() => handleHeightToggle(h.value)}
                className={`px-2 py-1 text-xs rounded border transition-colors ${
                  localFilters.heights.includes(h.value)
                    ? 'bg-[#62bb46] text-white border-[#62bb46]'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-[#62bb46]'
                }`}
              >
                {h.value} <span className="opacity-70">({h.count})</span>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Глубина */}
      {filterOptions.depths.length > 0 && (
        <div>
          <h3 className="font-medium text-gray-900 mb-2 text-sm">Глубина, см</h3>
          <div className="flex flex-wrap gap-1.5">
            {filterOptions.depths.slice(0, 8).map(d => (
              <button
                key={d.value}
                onClick={() => handleDepthToggle(d.value)}
                className={`px-2 py-1 text-xs rounded border transition-colors ${
                  localFilters.depths.includes(d.value)
                    ? 'bg-[#62bb46] text-white border-[#62bb46]'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-[#62bb46]'
                }`}
              >
                {d.value} <span className="opacity-70">({d.count})</span>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Цена */}
      <div>
        <h3 className="font-medium text-gray-900 mb-2 text-sm">Цена, ₽</h3>
        <div className="grid grid-cols-2 gap-1.5">
          {priceRanges.map(range => (
            <label key={range.key} className="flex items-center gap-1.5 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.priceRange === range.key}
                onChange={() => handlePriceRangeChange(range.key)}
                className="w-3.5 h-3.5 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-xs text-gray-700">{range.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Кнопка сброса */}
      {hasActiveFilters && (
        <button
          onClick={resetFilters}
          className="w-full py-2 text-sm text-[#62bb46] border border-[#62bb46] rounded-lg hover:bg-[#62bb46] hover:text-white transition-colors"
        >
          Сбросить фильтры
        </button>
      )}
    </div>
  );

  // Если это только мобильная версия, не рендерим десктоп
  if (isMobileOnly) {
    return isOpen ? (
      <div className="fixed inset-0 z-50 lg:hidden">
        <div className="absolute inset-0 bg-black/50" onClick={onClose} />
        <div className="absolute inset-y-0 left-0 w-full max-w-sm bg-white overflow-y-auto">
          <div className="sticky top-0 bg-white border-b border-gray-200 p-4 flex items-center justify-between">
            <h2 className="font-bold text-lg text-gray-900">Фильтры</h2>
            <button onClick={onClose} className="p-2 hover:bg-gray-100 rounded-lg">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div className="p-4">
            {filterContent}
          </div>
        </div>
      </div>
    ) : null;
  }

  return (
    <div className="bg-white rounded-xl shadow-sm p-4 sticky top-4">
      <h2 className="font-bold text-lg text-gray-900 mb-4">Фильтры</h2>
      {filterContent}
    </div>
  );
}
