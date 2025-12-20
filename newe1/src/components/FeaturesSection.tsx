const features = [
  {
    id: 1,
    title: 'Гарантия качества до 10 лет',
    icon: (
      <svg className="w-8 h-8" viewBox="0 0 40 40" fill="none">
        {/* Shield with warranty badge */}
        <path
          d="M20 4L6 10v10c0 9.55 5.98 18.48 14 20 8.02-1.52 14-10.45 14-20V10L20 4z"
          stroke="white"
          strokeWidth="2"
          fill="none"
        />
        <path
          d="M14 20l4 4 8-8"
          stroke="white"
          strokeWidth="2.5"
          strokeLinecap="round"
          strokeLinejoin="round"
          fill="none"
        />
        <text x="20" y="32" textAnchor="middle" fill="white" fontSize="8" fontWeight="bold">10</text>
      </svg>
    ),
  },
  {
    id: 2,
    title: 'Быстрая доставка от 24-х часов',
    icon: (
      <svg className="w-8 h-8" viewBox="0 0 40 40" fill="none">
        {/* Delivery truck */}
        <rect x="4" y="12" width="20" height="14" rx="2" stroke="white" strokeWidth="2" fill="none"/>
        <path d="M24 18h8l4 6v4h-12v-10z" stroke="white" strokeWidth="2" fill="none"/>
        <circle cx="10" cy="28" r="3" stroke="white" strokeWidth="2" fill="none"/>
        <circle cx="30" cy="28" r="3" stroke="white" strokeWidth="2" fill="none"/>
        <path d="M13 28h14" stroke="white" strokeWidth="2"/>
        <path d="M28 18v6" stroke="white" strokeWidth="1.5"/>
      </svg>
    ),
  },
  {
    id: 3,
    title: 'Экологически чистая и безопасная ДСП класса Е1',
    icon: (
      <svg className="w-8 h-8" viewBox="0 0 40 40" fill="none">
        {/* Eco certificate with E1 */}
        <rect x="6" y="4" width="28" height="32" rx="3" stroke="white" strokeWidth="2" fill="none"/>
        <path d="M12 12h16M12 18h16M12 24h10" stroke="white" strokeWidth="1.5" strokeLinecap="round"/>
        <circle cx="28" cy="28" r="6" stroke="white" strokeWidth="2" fill="none"/>
        <text x="28" y="31" textAnchor="middle" fill="white" fontSize="8" fontWeight="bold">E1</text>
      </svg>
    ),
  },
  {
    id: 4,
    title: 'Края защищены от сколов и повреждений',
    icon: (
      <svg className="w-8 h-8" viewBox="0 0 40 40" fill="none">
        {/* Gift box / protection icon */}
        <rect x="6" y="16" width="28" height="20" rx="2" stroke="white" strokeWidth="2" fill="none"/>
        <rect x="6" y="10" width="28" height="6" rx="1" stroke="white" strokeWidth="2" fill="none"/>
        <path d="M20 10v26" stroke="white" strokeWidth="2"/>
        <path d="M6 22h28" stroke="white" strokeWidth="1.5"/>
        <path d="M14 10c0-4 6-6 6-6s6 2 6 6" stroke="white" strokeWidth="2" fill="none"/>
      </svg>
    ),
  },
];

export default function FeaturesSection() {
  return (
    <section className="py-4 bg-[#f0f0f0]">
      <div className="container-custom">
        <div className="bg-white rounded-lg shadow-sm py-6 px-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {features.map((feature) => (
              <div
                key={feature.id}
                className="flex items-center gap-4"
              >
                <div className="w-14 h-14 bg-[#62bb46] rounded-full flex items-center justify-center shrink-0">
                  {feature.icon}
                </div>
                <span className="text-sm font-medium text-[#333] leading-tight">
                  {feature.title}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
