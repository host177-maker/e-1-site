'use client';

interface FilterValues {
  doorTypes: string[];
  wardrobeTypes: string[];
  series: string[];
  widthRanges: string[];
  heights: number[];
  depthRanges: string[];
  priceRanges: string[];
}

interface FilterOption {
  id?: number;
  key: string;
  slug?: string;
  label: string;
  count?: number;
  icon: React.ReactNode;
}

interface CatalogTopFilterProps {
  doorTypes: { id: number; name: string; slug: string; count: number }[];
  wardrobeTypeCounts?: { key: string; count: number }[];
  filters: FilterValues;
  onFiltersChange: (filters: FilterValues) => void;
}

// Иконки для типов шкафов
const doorTypeIcons: Record<string, React.ReactNode> = {
  'kupe': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="6" y="8" width="36" height="32" rx="2" />
      <line x1="24" y1="8" x2="24" y2="40" />
      <path d="M10 20 L14 24 L10 28" />
      <path d="M38 20 L34 24 L38 28" />
    </svg>
  ),
  'raspashnoy': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="6" y="8" width="36" height="32" rx="2" />
      <line x1="24" y1="8" x2="24" y2="40" />
      <circle cx="18" cy="24" r="1.5" fill="currentColor" />
      <circle cx="30" cy="24" r="1.5" fill="currentColor" />
    </svg>
  ),
  'garmoshka': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="6" y="8" width="36" height="32" rx="2" />
      <path d="M12 8 L12 40 M18 8 L18 40 M24 8 L24 40 M30 8 L30 40 M36 8 L36 40" strokeWidth="1" />
    </svg>
  ),
  'garderob': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="6" y="8" width="36" height="32" rx="2" />
      <line x1="6" y1="16" x2="42" y2="16" />
      <line x1="24" y1="16" x2="24" y2="40" />
      <path d="M14 28 L14 32" strokeLinecap="round" />
      <path d="M34 28 L34 32" strokeLinecap="round" />
    </svg>
  ),
};

// Иконки для типов назначения
const wardrobeTypeIcons: Record<string, React.ReactNode> = {
  'bedroom': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="6" y="20" width="36" height="18" rx="2" />
      <path d="M10 20 Q24 10 38 20" />
      <rect x="10" y="24" width="12" height="8" rx="1" />
      <rect x="26" y="24" width="12" height="8" rx="1" />
    </svg>
  ),
  'hallway': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="10" y="8" width="28" height="32" rx="2" />
      <rect x="14" y="12" width="20" height="24" rx="1" />
      <line x1="24" y1="12" x2="24" y2="36" strokeDasharray="2 2" />
      <circle cx="24" cy="24" r="3" />
    </svg>
  ),
  'living': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="8" y="6" width="32" height="36" rx="2" />
      <line x1="8" y1="14" x2="40" y2="14" />
      <rect x="12" y="18" width="10" height="8" rx="1" />
      <rect x="26" y="18" width="10" height="8" rx="1" />
      <rect x="12" y="30" width="10" height="8" rx="1" />
      <rect x="26" y="30" width="10" height="8" rx="1" />
    </svg>
  ),
  'mirror': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="10" y="6" width="28" height="36" rx="2" />
      <rect x="14" y="10" width="20" height="28" rx="1" />
      <path d="M18 16 Q24 14 30 16" strokeLinecap="round" />
      <ellipse cx="24" cy="24" rx="6" ry="8" strokeDasharray="3 2" />
    </svg>
  ),
  'corner': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <path d="M8 40 L8 12 L20 8 L40 8 L40 40 Z" />
      <line x1="20" y1="8" x2="20" y2="40" />
      <path d="M8 12 L20 8" />
    </svg>
  ),
  'two-door': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="8" y="8" width="32" height="32" rx="2" />
      <line x1="24" y1="8" x2="24" y2="40" />
      <circle cx="18" cy="24" r="1.5" fill="currentColor" />
      <circle cx="30" cy="24" r="1.5" fill="currentColor" />
      <text x="16" y="44" fontSize="6" fill="currentColor">2</text>
    </svg>
  ),
  'three-door': (
    <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" strokeWidth="1.5" className="w-full h-full">
      <rect x="6" y="8" width="36" height="32" rx="2" />
      <line x1="18" y1="8" x2="18" y2="40" />
      <line x1="30" y1="8" x2="30" y2="40" />
      <circle cx="12" cy="24" r="1" fill="currentColor" />
      <circle cx="24" cy="24" r="1" fill="currentColor" />
      <circle cx="36" cy="24" r="1" fill="currentColor" />
      <text x="22" y="44" fontSize="6" fill="currentColor">3</text>
    </svg>
  ),
};

// Маппинг типов шкафов для красивого отображения
const doorTypeLabels: Record<string, string> = {
  'kupe': 'Шкаф-купе',
  'raspashnoy': 'Распашной',
  'garmoshka': 'Гармошка',
  'garderob': 'Гардероб',
};

// Порядок отображения типов назначения
const wardrobeTypesOrder = [
  'two-door',
  'three-door',
  'bedroom',
  'hallway',
  'living',
  'mirror',
  'corner',
];

const wardrobeTypeLabels: Record<string, string> = {
  'two-door': 'Двухдверные',
  'three-door': 'Трёхдверные',
  'bedroom': 'В спальню',
  'hallway': 'В прихожую',
  'living': 'В гостиную',
  'mirror': 'С зеркалом',
  'corner': 'Угловые',
};

export default function CatalogTopFilter({
  doorTypes,
  wardrobeTypeCounts = [],
  filters,
  onFiltersChange,
}: CatalogTopFilterProps) {

  // Получить count для wardrobe type
  const getWardrobeTypeCount = (key: string): number | undefined => {
    const found = wardrobeTypeCounts.find(wt => wt.key === key);
    return found?.count;
  };

  const handleDoorTypeToggle = (slug: string) => {
    const newDoorTypes = filters.doorTypes.includes(slug)
      ? filters.doorTypes.filter(dt => dt !== slug)
      : [...filters.doorTypes, slug];
    onFiltersChange({ ...filters, doorTypes: newDoorTypes });
  };

  const handleWardrobeTypeToggle = (key: string) => {
    const newWardrobeTypes = filters.wardrobeTypes.includes(key)
      ? filters.wardrobeTypes.filter(wt => wt !== key)
      : [...filters.wardrobeTypes, key];
    onFiltersChange({ ...filters, wardrobeTypes: newWardrobeTypes });
  };

  // Собираем все опции для отображения
  const allOptions: FilterOption[] = [
    // Типы шкафов
    ...doorTypes
      .filter(dt => dt.slug !== 'bez-dverey')
      .map(dt => ({
        id: dt.id,
        key: `door-${dt.slug}`,
        slug: dt.slug,
        label: doorTypeLabels[dt.slug] || dt.name,
        count: dt.count,
        icon: doorTypeIcons[dt.slug] || doorTypeIcons['kupe'],
      })),
    // Типы назначения (в заданном порядке)
    ...wardrobeTypesOrder.map(key => ({
      key: `wardrobe-${key}`,
      slug: key,
      label: wardrobeTypeLabels[key],
      count: getWardrobeTypeCount(key),
      icon: wardrobeTypeIcons[key],
    })),
  ];

  const isSelected = (option: FilterOption) => {
    if (option.key.startsWith('door-')) {
      return filters.doorTypes.includes(option.slug!);
    }
    return filters.wardrobeTypes.includes(option.slug!);
  };

  const handleToggle = (option: FilterOption) => {
    if (option.key.startsWith('door-')) {
      handleDoorTypeToggle(option.slug!);
    } else {
      handleWardrobeTypeToggle(option.slug!);
    }
  };

  return (
    <div className="bg-white border-b border-gray-200 py-4">
      <div className="max-w-[1440px] mx-auto px-4">
        {/* Desktop: показываем все в ряд с переносом */}
        <div className="hidden md:flex flex-wrap justify-center gap-4 lg:gap-6">
          {allOptions.map((option) => {
            const selected = isSelected(option);
            return (
              <button
                key={option.key}
                onClick={() => handleToggle(option)}
                className={`flex flex-col items-center gap-1.5 p-2 rounded-lg transition-colors min-w-[70px] ${
                  selected
                    ? 'text-[#62bb46]'
                    : 'text-gray-600 hover:text-gray-900'
                }`}
              >
                <div className={`w-10 h-10 lg:w-12 lg:h-12 ${selected ? 'text-[#62bb46]' : 'text-gray-500'}`}>
                  {option.icon}
                </div>
                <span className={`text-[11px] lg:text-xs text-center leading-tight ${selected ? 'font-medium' : ''}`}>
                  {option.label}
                  {option.count !== undefined && (
                    <span className="text-gray-400 ml-0.5">({option.count})</span>
                  )}
                </span>
              </button>
            );
          })}
        </div>

        {/* Mobile: горизонтальный скролл */}
        <div className="md:hidden overflow-x-auto scrollbar-hide -mx-4 px-4">
          <div className="flex gap-3 pb-2" style={{ width: 'max-content' }}>
            {allOptions.map((option) => {
              const selected = isSelected(option);
              return (
                <button
                  key={option.key}
                  onClick={() => handleToggle(option)}
                  className={`flex flex-col items-center gap-1 p-2 rounded-lg transition-colors min-w-[60px] flex-shrink-0 ${
                    selected
                      ? 'text-[#62bb46] bg-green-50'
                      : 'text-gray-600'
                  }`}
                >
                  <div className={`w-8 h-8 ${selected ? 'text-[#62bb46]' : 'text-gray-500'}`}>
                    {option.icon}
                  </div>
                  <span className={`text-[10px] text-center leading-tight whitespace-nowrap ${selected ? 'font-medium' : ''}`}>
                    {option.label}
                  </span>
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}
