'use client';

import { useState } from 'react';
import Link from 'next/link';
import DirectorContactModal from './DirectorContactModal';

export default function Footer() {
  const [isDirectorModalOpen, setIsDirectorModalOpen] = useState(false);

  return (
    <>
    <DirectorContactModal isOpen={isDirectorModalOpen} onClose={() => setIsDirectorModalOpen(false)} />
    <footer className="bg-[#3d3d3d] text-white mt-8 pt-16">
      {/* Main footer content */}
      <div className="container-custom pb-10">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-6">
          {/* Column 1 - ПОКУПАТЕЛЮ */}
          <div>
            <h4 className="font-medium text-[#9ca3af] text-sm mb-4 uppercase tracking-wide">Покупателю</h4>
            <ul className="space-y-2">
              <li><Link href="/service/purchase-terms" className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors">Условия покупки</Link></li>
              <li><Link href="/service/installment" className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors">Рассрочка</Link></li>
              <li><Link href="/service/instructions" className="text-[#d1d5db] hover:text-[#62bb46] text-sm transition-colors">Инструкции к мебели</Link></li>
            </ul>
          </div>

          {/* Column 5 - Contacts */}
          <div>
            <div className="text-[#d1d5db] text-sm mb-3">с 07:00 до 20:00 мск</div>

            <a href="tel:+78001001211" className="text-white font-bold text-xl hover:text-[#62bb46] transition-colors block mb-1">
              8-800-100-12-11
            </a>
            <button className="text-[#62bb46] text-xs font-medium hover:underline uppercase tracking-wide mb-5">
              Заказать звонок
            </button>

            <button
              onClick={() => setIsDirectorModalOpen(true)}
              className="block w-full text-center bg-[#62bb46] text-white font-medium px-5 py-2.5 rounded hover:bg-[#55a83d] transition-colors text-sm mb-5 cursor-pointer"
            >
              Связь с директором
            </button>

            {/* Messengers */}
            <div className="flex gap-2 mb-3">
              <a href="https://wa.me/79384222111" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="WhatsApp">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </a>
              <a href="https://max.ru/79384222111" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="Max">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1.5 14.5c-2.49 0-4.5-2.01-4.5-4.5s2.01-4.5 4.5-4.5c1.23 0 2.34.5 3.15 1.3l-1.27 1.27C11.86 9.54 11.21 9.25 10.5 9.25c-1.52 0-2.75 1.23-2.75 2.75s1.23 2.75 2.75 2.75c1.5 0 2.55-1.08 2.7-2.5H10.5v-1.75h4.5c.04.26.07.52.07.8 0 2.65-1.77 4.2-4.57 4.2z"/>
                </svg>
              </a>
              <a href="https://t.me/+79384222111" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="Telegram">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                </svg>
              </a>
            </div>

            {/* Social networks */}
            <div className="flex gap-2">
              <a href="https://vk.com/e_odin" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="ВКонтакте">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4 8.684 4 8.263c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/>
                </svg>
              </a>
              <a href="https://rutube.ru/channel/e1shkafy" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="Rutube">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12.76 2H6.86C5.28 2 4 3.28 4 4.86v14.28C4 20.72 5.28 22 6.86 22h10.28c1.58 0 2.86-1.28 2.86-2.86V9.24L12.76 2zM16 18H8v-2h8v2zm0-4H8v-2h8v2zm-2-4V4l6 6h-6z"/>
                </svg>
              </a>
              <a href="https://ok.ru/e1shkafy" target="_blank" rel="noopener noreferrer" className="w-10 h-10 bg-[#4d4d4d] rounded flex items-center justify-center text-[#d1d5db] hover:bg-[#62bb46] hover:text-white transition-colors" title="Одноклассники">
                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm4.2 10.2c-.5.5-1.1.8-1.8 1l1.4 1.4c.4.4.4 1 0 1.4-.2.2-.5.3-.7.3s-.5-.1-.7-.3L12 17.6l-2.4 2.4c-.2.2-.5.3-.7.3s-.5-.1-.7-.3c-.4-.4-.4-1 0-1.4l1.4-1.4c-.7-.2-1.3-.5-1.8-1-.4-.4-.4-1 0-1.4.4-.4 1-.4 1.4 0 .9.9 2.5.9 3.4 0 .9-.9 2.5-.9 3.4 0 .4.4.4 1 0 1.4z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>

      {/* Bottom section */}
      <div className="border-t border-[#555555]">
        <div className="container-custom pt-14 pb-8">
          <div className="mb-5">
            <div className="text-[#d1d5db] text-sm mb-2">
              2007–2025 © Мебельная компания Е1 – шкафы купе в Москве
            </div>
            <div className="flex flex-wrap gap-x-1 text-sm">
              <Link href="/privacy-policy" className="text-[#62bb46] hover:underline">
                Политика защиты и обработки персональных данных
              </Link>
              <span className="text-[#6b7280] px-6">|</span>
              <Link href="/security-policy" className="text-[#62bb46] hover:underline">
                Политика безопасности
              </Link>
              <span className="text-[#6b7280] px-6">|</span>
              <Link href="/public-offer" className="text-[#62bb46] hover:underline">
                Публичная оферта
              </Link>
            </div>
          </div>

          {/* Legal text */}
          <div className="text-[#9ca3af] text-xs leading-relaxed space-y-1">
            <p>Информация, содержащаяся на данном сайте, не является публичной офертой. Копирование любых материалов сайта без согласования запрещено.</p>
            <p>Изображения товаров на сайте могут отличаться от фактического внешнего вида товара (цвет, фактура и дизайн).</p>
            <p>*Онлайн-рассрочка без переплаты» представляет из себя кредитный продукт МКК_0-0-6_СКС_Экспресс от ООО «Хоум Кредит энд Финанс Банк».</p>
            <p>Генеральная лицензия №316 Банка России от 15.03.2012г. В случае оплаты за 6 месяцев не возникает переплаты относительно исходной розничной цены.</p>
            <p>Подробности у продавцов-консультантов.</p>
            <p className="mt-2">This site is protected by reCAPTCHA and the Google <Link href="https://policies.google.com/privacy" className="text-[#62bb46] hover:underline">Privacy Policy</Link> and <Link href="https://policies.google.com/terms" className="text-[#62bb46] hover:underline">Terms of Service</Link> apply.</p>
          </div>
        </div>
      </div>
    </footer>
    </>
  );
}
