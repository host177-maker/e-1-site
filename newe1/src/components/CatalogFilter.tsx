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
  widthRanges: string[];
  heights: number[];
  depthRanges: string[];
  priceRanges: string[];
}

interface CatalogFilterProps {
  filterOptions: FilterOptions | null;
  filters: FilterValues;
  onFiltersChange: (filters: FilterValues) => void;
  isOpen: boolean;
  onClose: () => void;
  isMobileOnly?: boolean;
  totalProducts?: number;
}

// Маппинг типов шкафов для красивого отображения
const doorTypeLabels: Record<string, string> = {
  'kupe': 'Шкаф-купе',
  'raspashnoy': 'Шкаф распашной',
  'garmoshka': 'Шкаф гармошка',
  'tolkatel': 'Гардероб',
};
// Функция для получения отображаемого имени типа шкафа
const getDoorTypeLabel = (dt: { slug: string; name: string }): string => {
  return doorTypeLabels[dt.slug] || dt.name;
};

// Градации цены (с ₽ в конце)
const priceRangesList = [
  { key: '0-19999', label: 'до 19 999 ₽', min: 0, max: 19999 },
  { key: '20000-34999', label: '20 000 - 34 999 ₽', min: 20000, max: 34999 },
  { key: '35000-49999', label: '35 000 - 49 999 ₽', min: 35000, max: 49999 },
  { key: '50000-79999', label: '50 000 - 79 999 ₽', min: 50000, max: 79999 },
  { key: '80000-109999', label: '80 000 - 109 999 ₽', min: 80000, max: 109999 },
  { key: '110000-999999', label: 'от 110 000 ₽', min: 110000, max: 999999 },
];

// Градации ширины (в мм для поиска в БД, с см в значении)
const widthRangesList = [
  { key: '0-1090', label: 'до 109 см', min: 0, max: 1090 },
  { key: '1100-1390', label: '110-139 см', min: 1100, max: 1390 },
  { key: '1400-1610', label: '140-161 см', min: 1400, max: 1610 },
  { key: '1620-2000', label: '162-200 см', min: 1620, max: 2000 },
  { key: '2010-2390', label: '201-239 см', min: 2010, max: 2390 },
  { key: '2400-9990', label: 'от 240 см', min: 2400, max: 9990 },
];

// Градации глубины (в мм для поиска в БД, с см в значении)
const depthRangesList = [
  { key: '0-450', label: 'до 45 см', min: 0, max: 450 },
  { key: '450-530', label: '45-53 см', min: 450, max: 530 },
  { key: '530-9990', label: 'свыше 53 см', min: 530, max: 9990 },
];

export default function CatalogFilter({
  filterOptions,
  filters,
  onFiltersChange,
  isOpen,
  onClose,
  isMobileOnly = false,
  totalProducts = 0,
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

  const handleWidthRangeToggle = (key: string) => {
    const newWidthRanges = localFilters.widthRanges.includes(key)
      ? localFilters.widthRanges.filter(w => w !== key)
      : [...localFilters.widthRanges, key];
    const newFilters = { ...localFilters, widthRanges: newWidthRanges };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handleDepthRangeToggle = (key: string) => {
    const newDepthRanges = localFilters.depthRanges.includes(key)
      ? localFilters.depthRanges.filter(d => d !== key)
      : [...localFilters.depthRanges, key];
    const newFilters = { ...localFilters, depthRanges: newDepthRanges };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const handlePriceRangeToggle = (key: string) => {
    const newPriceRanges = localFilters.priceRanges.includes(key)
      ? localFilters.priceRanges.filter(p => p !== key)
      : [...localFilters.priceRanges, key];
    const newFilters = { ...localFilters, priceRanges: newPriceRanges };
    setLocalFilters(newFilters);
    onFiltersChange(newFilters);
  };

  const resetFilters = () => {
    const defaultFilters: FilterValues = {
      doorTypes: [],
      series: [],
      widthRanges: [],
      heights: [],
      depthRanges: [],
      priceRanges: [],
    };
    setLocalFilters(defaultFilters);
    onFiltersChange(defaultFilters);
  };

  const hasActiveFilters =
    localFilters.doorTypes.length > 0 ||
    localFilters.series.length > 0 ||
    localFilters.heights.length > 0 ||
    localFilters.depthRanges.length > 0 ||
    localFilters.widthRanges.length > 0 ||
    localFilters.priceRanges.length > 0;

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

  // Фильтруем высоты: убираем 390, показываем в см (делим на 10)
  const filteredHeights = filterOptions.heights
    .filter(h => h.value !== 390 && h.value > 100) // убираем 390 и слишком маленькие
    .map(h => ({ ...h, displayValue: Math.round(h.value / 10) })); // конвертируем мм в см

  const filterContent = (
    <div className="space-y-4">
      {/* Тип шкафа (без "Без дверей") */}
      <div>
        <h3 className="font-medium text-gray-900 mb-1.5 text-xs">Тип шкафа</h3>
        <div className="space-y-1">
          {filterOptions.doorTypes
            .filter(dt => dt.slug !== 'bez-dverey')
            .map(dt => (
              <label key={dt.id} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={localFilters.doorTypes.includes(dt.slug)}
                  onChange={() => handleDoorTypeToggle(dt.slug)}
                  className="w-3.5 h-3.5 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
                />
                <span className="text-xs text-gray-700 flex-1">{getDoorTypeLabel(dt)}</span>
                <span className="text-[10px] text-gray-400">({dt.count})</span>
              </label>
            ))}
        </div>
      </div>

      {/* Серия */}
      <div>
        <h3 className="font-medium text-gray-900 mb-1.5 text-xs">Серия</h3>
        <div className="space-y-1">
          {filterOptions.series.map(s => (
            <label key={s.id} className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.series.includes(s.slug)}
                onChange={() => handleSeriesToggle(s.slug)}
                className="w-3.5 h-3.5 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-xs text-gray-700 flex-1">{s.name}</span>
              <span className="text-[10px] text-gray-400">({s.count})</span>
            </label>
          ))}
        </div>
      </div>

      {/* Цена (мультивыбор, ₽ в значениях) */}
      <div>
        <h3 className="font-medium text-gray-900 mb-1.5 text-xs">Цена</h3>
        <div className="grid grid-cols-2 gap-1">
          {priceRangesList.map(range => (
            <label key={range.key} className="flex items-center gap-1 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.priceRanges.includes(range.key)}
                onChange={() => handlePriceRangeToggle(range.key)}
                className="w-3 h-3 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-[11px] text-gray-700">{range.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Ширина (мультивыбор, см в значениях) */}
      <div>
        <h3 className="font-medium text-gray-900 mb-1.5 text-xs">Ширина</h3>
        <div className="grid grid-cols-2 gap-1">
          {widthRangesList.map(range => (
            <label key={range.key} className="flex items-center gap-1 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.widthRanges.includes(range.key)}
                onChange={() => handleWidthRangeToggle(range.key)}
                className="w-3 h-3 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-[11px] text-gray-700">{range.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Высота (см в значениях) */}
      {filteredHeights.length > 0 && (
        <div>
          <h3 className="font-medium text-gray-900 mb-1.5 text-xs">Высота</h3>
          <div className="flex flex-wrap gap-1">
            {filteredHeights.slice(0, 8).map(h => (
              <button
                key={h.value}
                onClick={() => handleHeightToggle(h.value)}
                className={`px-1.5 py-0.5 text-[11px] rounded border transition-colors ${
                  localFilters.heights.includes(h.value)
                    ? 'bg-[#62bb46] text-white border-[#62bb46]'
                    : 'bg-white text-gray-700 border-gray-300 hover:border-[#62bb46]'
                }`}
              >
                {h.displayValue} см <span className="opacity-70">({h.count})</span>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Глубина (мультивыбор, см в значениях) */}
      <div>
        <h3 className="font-medium text-gray-900 mb-1.5 text-xs">Глубина</h3>
        <div className="grid grid-cols-2 gap-1">
          {depthRangesList.map(range => (
            <label key={range.key} className="flex items-center gap-1 cursor-pointer">
              <input
                type="checkbox"
                checked={localFilters.depthRanges.includes(range.key)}
                onChange={() => handleDepthRangeToggle(range.key)}
                className="w-3 h-3 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
              />
              <span className="text-[11px] text-gray-700">{range.label}</span>
            </label>
          ))}
        </div>
      </div>

      {/* Кнопка сброса внизу */}
      {hasActiveFilters && (
        <button
          onClick={resetFilters}
          className="w-full py-1.5 text-xs text-[#62bb46] border border-[#62bb46] rounded-lg hover:bg-[#62bb46] hover:text-white transition-colors"
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
            <div>
              <h2 className="font-bold text-base text-gray-900">Фильтры</h2>
              <p className="text-[11px] text-gray-500">Найдено: {totalProducts}</p>
            </div>
            <div className="flex items-center gap-2">
              {hasActiveFilters && (
                <button
                  onClick={resetFilters}
                  className="text-[11px] text-[#62bb46] hover:underline"
                >
                  Сбросить
                </button>
              )}
              <button onClick={onClose} className="p-2 hover:bg-gray-100 rounded-lg">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          <div className="p-4">
            {filterContent}
          </div>
        </div>
      </div>
    ) : null;
  }

  return (
    <div className="bg-white rounded-xl shadow-sm p-3 sticky top-4">
      <div className="flex items-center justify-between mb-1">
        <h2 className="font-bold text-base text-gray-900">Фильтры</h2>
        {hasActiveFilters && (
          <button
            onClick={resetFilters}
            className="text-[11px] text-[#62bb46] hover:underline"
          >
            Сбросить
          </button>
        )}
      </div>
      <p className="text-[11px] text-gray-500 mb-3">Найдено: {totalProducts}</p>
      {filterContent}
    </div>
  );
}
