'use client';

import { useState, useEffect, useCallback } from 'react';

interface FilterOptions {
  doorTypes: { id: number; name: string; slug: string }[];
  series: { id: number; name: string; slug: string }[];
  widthRange: { min: number; max: number };
  heights: number[];
  depths: number[];
  priceRange: { min: number; max: number };
}

interface FilterValues {
  doorType: string;
  series: string;
  widthMin: number;
  widthMax: number;
  heights: number[];
  depths: number[];
  priceMin: number;
  priceMax: number;
}

interface CatalogFilterProps {
  filterOptions: FilterOptions | null;
  filters: FilterValues;
  onFiltersChange: (filters: FilterValues) => void;
  isOpen: boolean;
  onClose: () => void;
}

// Маппинг типов шкафов для отображения
const doorTypeLabels: Record<string, string> = {
  'kupe': 'Шкаф-купе',
  'raspashnoy': 'Шкаф распашной',
  'garmoshka': 'Шкаф гармошка',
  'tolkatel': 'Гардероб',
  'bez-dverey': 'Без дверей',
};

export default function CatalogFilter({
  filterOptions,
  filters,
  onFiltersChange,
  isOpen,
  onClose,
}: CatalogFilterProps) {
  const [localFilters, setLocalFilters] = useState<FilterValues>(filters);
  const [widthInputMin, setWidthInputMin] = useState(filters.widthMin.toString());
  const [widthInputMax, setWidthInputMax] = useState(filters.widthMax.toString());
  const [priceInputMin, setPriceInputMin] = useState(filters.priceMin.toString());
  const [priceInputMax, setPriceInputMax] = useState(filters.priceMax.toString());

  // Синхронизация с внешними фильтрами
  useEffect(() => {
    setLocalFilters(filters);
    setWidthInputMin(filters.widthMin.toString());
    setWidthInputMax(filters.widthMax.toString());
    setPriceInputMin(filters.priceMin.toString());
    setPriceInputMax(filters.priceMax.toString());
  }, [filters]);

  const handleDoorTypeChange = (slug: string) => {
    const newFilters = {
      ...localFilters,
      doorType: localFilters.doorType === slug ? '' : slug,
    };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handleSeriesChange = (slug: string) => {
    const newFilters = {
      ...localFilters,
      series: localFilters.series === slug ? '' : slug,
    };
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

  const handleWidthChange = useCallback(() => {
    const min = parseInt(widthInputMin) || filterOptions?.widthRange.min || 80;
    const max = parseInt(widthInputMax) || filterOptions?.widthRange.max || 300;
    if (min !== localFilters.widthMin || max !== localFilters.widthMax) {
      const newFilters = { ...localFilters, widthMin: min, widthMax: max };
      setLocalFilters(newFilters);
      onFiltersChange(newFilters);
    }
  }, [widthInputMin, widthInputMax, localFilters, filterOptions, onFiltersChange]);

  const handlePriceChange = useCallback(() => {
    const min = parseInt(priceInputMin) || filterOptions?.priceRange.min || 2000;
    const max = parseInt(priceInputMax) || filterOptions?.priceRange.max || 170000;
    if (min !== localFilters.priceMin || max !== localFilters.priceMax) {
      const newFilters = { ...localFilters, priceMin: min, priceMax: max };
      setLocalFilters(newFilters);
      onFiltersChange(newFilters);
    }
  }, [priceInputMin, priceInputMax, localFilters, filterOptions, onFiltersChange]);

  const resetFilters = () => {
    const defaultFilters: FilterValues = {
      doorType: '',
      series: '',
      widthMin: filterOptions?.widthRange.min || 80,
      widthMax: filterOptions?.widthRange.max || 300,
      heights: [],
      depths: [],
      priceMin: filterOptions?.priceRange.min || 2000,
      priceMax: filterOptions?.priceRange.max || 170000,
    };
    setLocalFilters(defaultFilters);
    setWidthInputMin(defaultFilters.widthMin.toString());
    setWidthInputMax(defaultFilters.widthMax.toString());
    setPriceInputMin(defaultFilters.priceMin.toString());
    setPriceInputMax(defaultFilters.priceMax.toString());
    onFiltersChange(defaultFilters);
  };

  const hasActiveFilters =
    localFilters.doorType ||
    localFilters.series ||
    localFilters.heights.length > 0 ||
    localFilters.depths.length > 0 ||
    (filterOptions && (
      localFilters.widthMin !== filterOptions.widthRange.min ||
      localFilters.widthMax !== filterOptions.widthRange.max ||
      localFilters.priceMin !== filterOptions.priceRange.min ||
      localFilters.priceMax !== filterOptions.priceRange.max
    ));

  if (!filterOptions) {
    return (
      <div className="bg-white rounded-xl shadow-sm p-4">
        <div className="animate-pulse space-y-4">
          <div className="h-4 bg-gray-200 rounded w-1/2"></div>
          <div className="space-y-2">
            <div className="h-8 bg-gray-200 rounded"></div>
            <div className="h-8 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
    );
  }

  const filterContent = (
    <div className="space-y-6">
      {/* Тип шкафа */}
      <div>
        <h3 className="font-medium text-gray-900 mb-3">Тип шкафа</h3>
        <div className="space-y-2">
          {filterOptions.doorTypes
            .filter(dt => doorTypeLabels[dt.slug])
            .map(dt => (
              <label key={dt.id} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={localFilters.doorType === dt.slug}
                  onChange={() => handleDoorTypeChange(dt.slug)}
                  className="w-4 h-4 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
                />
                <span className="text-sm text-gray-700">{doorTypeLabels[dt.slug] || dt.name}</span>
              </label>
            ))}
        </div>
      </div>

      {/* Серия */}
      <div>
        <h3 className="font-medium text-gray-900 mb-3">Серия</h3>
        <div className="space-y-2">
          {filterOptions.series.map(s => (
            <label key={s.id} className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.series === s.slug}
                onChange={() => handleSeriesChange(s.slug)}
                className="w-4 h-4 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-sm text-gray-700">{s.name}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Ширина */}
      <div>
        <h3 className="font-medium text-gray-900 mb-3">Ширина, см</h3>
        <div className="flex items-center gap-2">
          <input
            type="number"
            value={widthInputMin}
            onChange={(e) => setWidthInputMin(e.target.value)}
            onBlur={handleWidthChange}
            onKeyDown={(e) => e.key === 'Enter' && handleWidthChange()}
            min={filterOptions.widthRange.min}
            max={filterOptions.widthRange.max}
            placeholder="от"
            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
          />
          <span className="text-gray-400">—</span>
          <input
            type="number"
            value={widthInputMax}
            onChange={(e) => setWidthInputMax(e.target.value)}
            onBlur={handleWidthChange}
            onKeyDown={(e) => e.key === 'Enter' && handleWidthChange()}
            min={filterOptions.widthRange.min}
            max={filterOptions.widthRange.max}
            placeholder="до"
            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
          />
        </div>
        {/* Слайдер */}
        <div className="mt-3 px-1">
          <input
            type="range"
            min={filterOptions.widthRange.min}
            max={filterOptions.widthRange.max}
            value={localFilters.widthMin}
            onChange={(e) => {
              const val = parseInt(e.target.value);
              setWidthInputMin(val.toString());
              if (val <= localFilters.widthMax) {
                const newFilters = { ...localFilters, widthMin: val };
                setLocalFilters(newFilters);
                onFiltersChange(newFilters);
              }
            }}
            className="w-full accent-[#62bb46]"
          />
        </div>
      </div>

      {/* Высота */}
      {filterOptions.heights.length > 0 && (
        <div>
          <h3 className="font-medium text-gray-900 mb-3">Высота, см</h3>
          <div className="flex flex-wrap gap-2">
            {filterOptions.heights.slice(0, 10).map(h => (
              <button
                key={h}
                onClick={() => handleHeightToggle(h)}
                className={`px-3 py-1.5 text-sm rounded-lg border transition-colors ${
                  localFilters.heights.includes(h)
                    ? 'bg-[#62bb46] text-white border-[#62bb46]'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-[#62bb46]'
                }`}
              >
                {h}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Глубина */}
      {filterOptions.depths.length > 0 && (
        <div>
          <h3 className="font-medium text-gray-900 mb-3">Глубина, см</h3>
          <div className="flex flex-wrap gap-2">
            {filterOptions.depths.slice(0, 10).map(d => (
              <button
                key={d}
                onClick={() => handleDepthToggle(d)}
                className={`px-3 py-1.5 text-sm rounded-lg border transition-colors ${
                  localFilters.depths.includes(d)
                    ? 'bg-[#62bb46] text-white border-[#62bb46]'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-[#62bb46]'
                }`}
              >
                {d}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Цена */}
      <div>
        <h3 className="font-medium text-gray-900 mb-3">Цена, ₽</h3>
        <div className="flex items-center gap-2">
          <input
            type="number"
            value={priceInputMin}
            onChange={(e) => setPriceInputMin(e.target.value)}
            onBlur={handlePriceChange}
            onKeyDown={(e) => e.key === 'Enter' && handlePriceChange()}
            min={filterOptions.priceRange.min}
            max={filterOptions.priceRange.max}
            placeholder="от"
            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
          />
          <span className="text-gray-400">—</span>
          <input
            type="number"
            value={priceInputMax}
            onChange={(e) => setPriceInputMax(e.target.value)}
            onBlur={handlePriceChange}
            onKeyDown={(e) => e.key === 'Enter' && handlePriceChange()}
            min={filterOptions.priceRange.min}
            max={filterOptions.priceRange.max}
            placeholder="до"
            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none"
          />
        </div>
        {/* Слайдер */}
        <div className="mt-3 px-1">
          <input
            type="range"
            min={filterOptions.priceRange.min}
            max={filterOptions.priceRange.max}
            step={1000}
            value={localFilters.priceMin}
            onChange={(e) => {
              const val = parseInt(e.target.value);
              setPriceInputMin(val.toString());
              if (val <= localFilters.priceMax) {
                const newFilters = { ...localFilters, priceMin: val };
                setLocalFilters(newFilters);
                onFiltersChange(newFilters);
              }
            }}
            className="w-full accent-[#62bb46]"
          />
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

  return (
    <>
      {/* Desktop filter */}
      <div className="hidden lg:block bg-white rounded-xl shadow-sm p-4 sticky top-4">
        <h2 className="font-bold text-lg text-gray-900 mb-4">Фильтры</h2>
        {filterContent}
      </div>

      {/* Mobile filter modal */}
      {isOpen && (
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
      )}
    </>
  );
}
