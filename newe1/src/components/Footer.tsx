import Link from 'next/link';

const footerLinks = {
  catalog: {
    title: 'Каталог',
    links: [
      { label: 'Шкафы-купе', href: '/catalog/shkafi-kupe' },
      { label: 'Гардеробные', href: '/catalog/garderobnye' },
      { label: 'Встроенные шкафы', href: '/catalog/vstroennye-shkafi' },
      { label: 'Прихожие', href: '/catalog/prihozhie' },
    ],
  },
  company: {
    title: 'Компания',
    links: [
      { label: 'О компании', href: '/about' },
      { label: 'Отзывы', href: '/reviews' },
      { label: 'Акции', href: '/sales' },
      { label: 'Новости', href: '/news' },
    ],
  },
  customers: {
    title: 'Покупателям',
    links: [
      { label: 'Как купить', href: '/how-to-buy' },
      { label: 'Доставка', href: '/delivery' },
      { label: 'Оплата', href: '/payment' },
      { label: 'Гарантия', href: '/warranty' },
    ],
  },
  help: {
    title: 'Помощь',
    links: [
      { label: 'Контакты', href: '/contacts' },
      { label: 'Адреса салонов', href: '/stores' },
      { label: 'Производители', href: '/brands' },
      { label: 'Услуги', href: '/services' },
    ],
  },
};

const socialLinks = [
  {
    label: 'VK',
    href: 'https://vk.com',
    icon: (
      <svg className="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
        <path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.263c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z" />
      </svg>
    ),
  },
  {
    label: 'Telegram',
    href: 'https://t.me',
    icon: (
      <svg className="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
      </svg>
    ),
  },
  {
    label: 'YouTube',
    href: 'https://youtube.com',
    icon: (
      <svg className="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
      </svg>
    ),
  },
];

// E1 Logo for footer (green)
function FooterLogo() {
  return (
    <svg viewBox="0 0 80 56" className="h-12 w-auto">
      <g fill="#62BB46">
        <polygon points="76.82,9.79 76.82,51.74 65.78,51.74 65.78,15.47 69.73,15.47 58.69,9.94 58.69,4.26 65.78,4.26" />
        <polygon points="47.48,15.47 11.05,15.47 0,10.03 0,4.26 36.43,4.26 47.48,9.79" />
        <polygon points="11.06,15.47 11.06,22.4 36.43,22.4 47.48,27.92 47.48,33.6 11.22,33.6 0,28.08 0,15.47" />
        <polygon points="47.48,46.06 47.48,51.74 11.05,51.74 0,46.22 0,33.6 11.22,33.6 11.22,40.53 36.43,40.53" />
      </g>
    </svg>
  );
}

export default function Footer() {
  return (
    <footer className="bg-bg-dark text-white">
      {/* Main footer content */}
      <div className="container-custom py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
          {/* Logo and description */}
          <div className="lg:col-span-1">
            <div className="mb-4">
              <FooterLogo />
            </div>
            <p className="text-text-muted text-sm mb-4">
              Шкафы-купе и гардеробные системы на заказ. Производство и продажа мебели.
            </p>
            {/* Social links */}
            <div className="flex gap-4">
              {socialLinks.map((social) => (
                <a
                  key={social.label}
                  href={social.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-text-muted hover:text-primary transition-colors"
                  aria-label={social.label}
                >
                  {social.icon}
                </a>
              ))}
            </div>
          </div>

          {/* Link columns */}
          {Object.values(footerLinks).map((section) => (
            <div key={section.title}>
              <h4 className="font-bold text-white mb-4">{section.title}</h4>
              <ul className="space-y-2">
                {section.links.map((link) => (
                  <li key={link.href}>
                    <Link
                      href={link.href}
                      className="text-text-muted hover:text-primary text-sm transition-colors"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        {/* Contact info */}
        <div className="mt-12 pt-8 border-t border-gray-700">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="flex items-start gap-3">
              <svg className="w-5 h-5 text-primary mt-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <div>
                <a href="tel:+78000000000" className="font-medium hover:text-primary">+7 800 000-00-00</a>
                <div className="text-sm text-text-muted">Пн-Вс: 9:00 - 21:00</div>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <svg className="w-5 h-5 text-primary mt-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <div>
                <a href="mailto:best@e-1.ru" className="font-medium hover:text-primary">best@e-1.ru</a>
                <div className="text-sm text-text-muted">Для заказов и вопросов</div>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <svg className="w-5 h-5 text-primary mt-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <div>
                <div className="font-medium">Москва</div>
                <div className="text-sm text-text-muted">МЦ &quot;Мир Мебели&quot;, м. Щелковская, Щёлковское ш., д.100</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Copyright */}
      <div className="bg-black/30 py-4">
        <div className="container-custom flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-text-muted">
          <div>&copy; {new Date().getFullYear()} E-1. Все права защищены.</div>
          <div className="flex gap-6">
            <Link href="/privacy" className="hover:text-primary">
              Политика конфиденциальности
            </Link>
            <Link href="/terms" className="hover:text-primary">
              Пользовательское соглашение
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
