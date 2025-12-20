import Link from 'next/link';

export default function Footer() {
  return (
    <footer className="bg-[#3d3d3d] text-white">
      {/* Main footer content */}
      <div className="container-custom py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-6">
          {/* Column 1 - КАТАЛОГ */}
          <div>
            <h4 className="font-bold text-white text-sm mb-4 uppercase tracking-wide">Каталог</h4>
            <ul className="space-y-2">
              <li><Link href="/catalog/garderobnye" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">ГАРДЕРОБНЫЕ</Link></li>
              <li><Link href="/catalog/vstroennye-shkafi" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">ВСТРОЕННЫЕ ШКАФЫ</Link></li>
              <li><Link href="/stores" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">АДРЕСА САЛОНОВ</Link></li>
              <li><Link href="/buyers" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">ПОКУПАТЕЛЮ</Link></li>
              <li><Link href="/reviews" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">ОТЗЫВЫ</Link></li>
              <li><Link href="/sales" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">АКЦИИ</Link></li>
            </ul>
          </div>

          {/* Column 2 - ДОСТАВКА + ПОКУПКА И ГАРАНТИИ */}
          <div>
            <h4 className="font-bold text-white text-sm mb-4 uppercase tracking-wide">Доставка</h4>
            <ul className="space-y-2 mb-6">
              <li><Link href="/delivery" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Доставка и сборка</Link></li>
              <li><Link href="/order-path" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Путь заказа</Link></li>
              <li><Link href="/production-time" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Сроки изготовления</Link></li>
            </ul>

            <h4 className="font-bold text-white text-sm mb-4 uppercase tracking-wide">Покупка и гарантии</h4>
            <ul className="space-y-2">
              <li><Link href="/purchase-terms" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Условия покупки</Link></li>
              <li><Link href="/warranty" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Гарантия</Link></li>
              <li><Link href="/installment" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Рассрочка</Link></li>
              <li><Link href="/return" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Возврат товара</Link></li>
            </ul>
          </div>

          {/* Column 3 - ПОМОЩЬ В ВЫБОРЕ */}
          <div>
            <h4 className="font-bold text-white text-sm mb-4 uppercase tracking-wide">Помощь в выборе</h4>
            <ul className="space-y-2">
              <li><Link href="/about-wardrobes" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">О шкафах-купе</Link></li>
              <li><Link href="/photo-printing" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Каталог фотопечати</Link></li>
              <li><Link href="/faq" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Вопросы и ответы</Link></li>
              <li><Link href="/tips" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Советы от Е1</Link></li>
              <li><Link href="/portfolio" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Наши работы</Link></li>
              <li><Link href="/brochure" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Брошюра</Link></li>
            </ul>
          </div>

          {/* Column 4 - О КОМПАНИИ */}
          <div>
            <h4 className="font-bold text-white text-sm mb-4 uppercase tracking-wide">О компании</h4>
            <ul className="space-y-2">
              <li><Link href="/production" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Производство</Link></li>
              <li><Link href="/quality" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">О качестве сервиса</Link></li>
              <li><Link href="/vacancies" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Вакансии</Link></li>
              <li><Link href="/partnership" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Партнерство</Link></li>
              <li><Link href="/cities" className="text-gray-300 hover:text-[#62bb46] text-sm transition-colors">Работаем в городах</Link></li>
            </ul>
          </div>

          {/* Column 5 - Contacts */}
          <div>
            <div className="text-gray-300 text-sm mb-1">с 07:00 до 20:00 мск</div>
            <div className="text-gray-400 text-sm mb-2">Горячая линия</div>

            <div className="flex items-center gap-2 mb-1">
              <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <a href="tel:+74957447233" className="text-white font-bold text-lg hover:text-[#62bb46] transition-colors">
                8 (495) 744-72-33
              </a>
              <span className="text-gray-500">•</span>
            </div>
            <a href="tel:+78001001211" className="text-white font-bold text-lg hover:text-[#62bb46] transition-colors block mb-1">
              8-800-100-12-11
            </a>
            <button className="text-[#62bb46] text-xs font-medium hover:underline uppercase tracking-wide mb-4">
              Заказать звонок
            </button>

            <a
              href="/contact-director"
              className="inline-block bg-[#62bb46] text-white font-medium px-6 py-3 rounded hover:bg-[#55a83d] transition-colors text-sm mb-6"
            >
              Связь с директором
            </a>

            {/* Social icons row 1 */}
            <div className="flex gap-2 mb-2">
              <a href="https://wa.me/79384222111" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-gray-300 hover:bg-[#62bb46] hover:text-white transition-colors" title="WhatsApp">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </a>
              <a href="https://ok.ru/e1shkafy" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-gray-300 hover:bg-[#62bb46] hover:text-white transition-colors" title="Одноклассники">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 6.545c1.389 0 2.51 1.121 2.51 2.51s-1.121 2.51-2.51 2.51-2.51-1.121-2.51-2.51 1.121-2.51 2.51-2.51zm3.918 7.243c-.629.619-1.478 1.015-2.418 1.185l2.23 2.224c.395.394.395 1.033 0 1.428-.197.197-.456.295-.714.295s-.517-.098-.714-.295l-2.302-2.296-2.302 2.296c-.197.197-.456.295-.714.295s-.517-.098-.714-.295c-.395-.395-.395-1.034 0-1.428l2.23-2.224c-.94-.17-1.789-.566-2.418-1.185-.404-.398-.408-1.046-.01-1.449.398-.404 1.046-.408 1.449-.01.752.741 1.858 1.043 2.479 1.043s1.727-.302 2.479-1.043c.403-.398 1.051-.394 1.449.01.398.403.394 1.051-.01 1.449z"/>
                </svg>
              </a>
              <a href="https://t.me/+79384222111" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-gray-300 hover:bg-[#62bb46] hover:text-white transition-colors" title="Telegram">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                </svg>
              </a>
            </div>

            {/* Social icons row 2 */}
            <div className="flex gap-2">
              <a href="https://vk.com/e_odin" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-gray-300 hover:bg-[#62bb46] hover:text-white transition-colors" title="ВКонтакте">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.263c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/>
                </svg>
              </a>
              <a href="https://dzen.ru/e1shkafy" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-gray-300 hover:bg-[#62bb46] hover:text-white transition-colors" title="Дзен">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-.857 17.143H8.571v-2.572h2.572v2.572zm4.286-4.286H8.571V10.29h6.858v2.567zm0-4.286H8.571V6.004h6.858V8.57z"/>
                </svg>
              </a>
              <a href="https://www.youtube.com/@e1shkafy" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-gray-300 hover:bg-[#62bb46] hover:text-white transition-colors" title="YouTube">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom section with copyright and payment */}
      <div className="border-t border-gray-600">
        <div className="container-custom py-6">
          <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-4">
            <div>
              <div className="text-gray-300 text-sm mb-1">
                2007–2025 © Мебельная компания Е1 – шкафы купе в Москве
              </div>
              <div className="flex flex-wrap gap-x-2 text-sm">
                <Link href="/privacy-policy" className="text-[#62bb46] hover:underline">
                  Политика защиты и обработки персональных данных
                </Link>
                <span className="text-gray-500">|</span>
                <Link href="/security-policy" className="text-[#62bb46] hover:underline">
                  Политика безопасности
                </Link>
                <span className="text-gray-500">|</span>
                <Link href="/public-offer" className="text-[#62bb46] hover:underline">
                  Публичная оферта
                </Link>
              </div>
            </div>

            {/* Payment icons */}
            <div className="flex items-center gap-3">
              <span className="text-gray-400 text-xs">PayKeeper</span>
              <div className="w-10 h-6 bg-gray-500 rounded flex items-center justify-center">
                <span className="text-white text-[10px] font-bold">MC</span>
              </div>
              <div className="w-10 h-6 bg-gray-500 rounded flex items-center justify-center">
                <span className="text-white text-[10px] font-bold">VISA</span>
              </div>
              <div className="w-10 h-6 bg-gray-500 rounded flex items-center justify-center">
                <span className="text-white text-[10px] font-bold">МИР</span>
              </div>
            </div>
          </div>

          {/* Legal text */}
          <div className="text-gray-500 text-xs leading-relaxed space-y-1">
            <p>Информация, содержащаяся на данном сайте, не является публичной офертой. Копирование любых материалов сайта без согласования запрещено.</p>
            <p>Изображения товаров на сайте могут отличаться от фактического внешнего вида товара (цвет, фактура и дизайн).</p>
            <p>*Онлайн-рассрочка без переплаты» представляет из себя кредитный продукт МКК_0-0-6_СКС_Экспресс от ООО «Хоум Кредит энд Финанс Банк».</p>
            <p>Генеральная лицензия №316 Банка России от 15.03.2012г. В случае оплаты за 6 месяцев не возникает переплаты относительно исходной розничной цены.</p>
            <p>Подробности у продавцов-консультантов.</p>
          </div>
        </div>
      </div>
    </footer>
  );
}
