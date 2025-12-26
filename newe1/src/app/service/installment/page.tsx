'use client';

import { useState } from 'react';
import Link from 'next/link';
import Image from 'next/image';

export default function InstallmentPage() {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
  });
  const [agreed, setAgreed] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitResult, setSubmitResult] = useState<{ success: boolean; message: string } | null>(null);

  const formatPhone = (value: string) => {
    const digits = value.replace(/\D/g, '');
    let formatted = '+7 ';
    if (digits.length > 1) {
      formatted += '(' + digits.substring(1, 4);
    }
    if (digits.length >= 4) {
      formatted += ') ' + digits.substring(4, 7);
    }
    if (digits.length >= 7) {
      formatted += '-' + digits.substring(7, 9);
    }
    if (digits.length >= 9) {
      formatted += '-' + digits.substring(9, 11);
    }
    return formatted;
  };

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    if (value === '' || value === '+') {
      setFormData({ ...formData, phone: '' });
      return;
    }
    const formatted = formatPhone(value);
    setFormData({ ...formData, phone: formatted });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!agreed || !formData.name || !formData.phone) return;

    setIsSubmitting(true);
    setSubmitResult(null);

    try {
      const response = await fetch('/api/request-installment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: formData.name,
          phone: formData.phone,
        }),
      });

      const result = await response.json();
      setSubmitResult({
        success: result.success,
        message: result.message,
      });

      if (result.success) {
        setFormData({ name: '', phone: '' });
        setAgreed(false);
      }
    } catch {
      setSubmitResult({
        success: false,
        message: 'Произошла ошибка. Попробуйте позже или позвоните нам.',
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleReset = () => {
    setFormData({ name: '', phone: '' });
    setAgreed(false);
    setSubmitResult(null);
  };

  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-gradient-to-r from-[#62bb46] to-[#4a9c35] py-12">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-2">
            Рассрочка 0-0-6
          </h1>
          <p className="text-white/90 text-lg">
            На любой шкаф от компании «Е1» можно оформить онлайн-рассрочку
          </p>
        </div>
      </div>

      {/* Benefits section */}
      <div className="container-custom py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
          {/* Benefit 1 */}
          <div className="text-center p-6 rounded-xl bg-[#f9fafb] hover:shadow-lg transition-shadow">
            <div className="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-[#62bb46] to-[#4a9c35] rounded-full flex items-center justify-center">
              <span className="text-white text-3xl font-bold">6</span>
            </div>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">Срок рассрочки — 6 мес</h3>
            <p className="text-gray-600 text-sm">Комфортный срок для погашения без спешки</p>
          </div>

          {/* Benefit 2 */}
          <div className="text-center p-6 rounded-xl bg-[#f9fafb] hover:shadow-lg transition-shadow">
            <div className="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-[#62bb46] to-[#4a9c35] rounded-full flex items-center justify-center">
              <span className="text-white text-3xl font-bold">0%</span>
            </div>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">0% — переплата</h3>
            <p className="text-gray-600 text-sm">Никаких скрытых комиссий и процентов</p>
          </div>

          {/* Benefit 3 */}
          <div className="text-center p-6 rounded-xl bg-[#f9fafb] hover:shadow-lg transition-shadow">
            <div className="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-[#62bb46] to-[#4a9c35] rounded-full flex items-center justify-center">
              <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 className="text-xl font-bold text-[#3d4543] mb-2">Выплаты ежемесячно</h3>
            <p className="text-gray-600 text-sm">Равными частями каждый месяц</p>
          </div>
        </div>

        {/* How it works section */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-16">
          <div>
            <h2 className="text-2xl md:text-3xl font-bold text-[#3d4543] mb-8">
              Как оформить рассрочку?
            </h2>

            <div className="space-y-6">
              {/* Step 1 */}
              <div className="flex gap-4">
                <div className="flex-shrink-0 w-10 h-10 bg-[#62bb46] rounded-full flex items-center justify-center text-white font-bold">
                  1
                </div>
                <div>
                  <h3 className="font-bold text-[#3d4543] mb-1">Шаг 1</h3>
                  <p className="text-gray-600 text-sm">
                    Закажите шкаф обычным путём, нажав «купить» на карточке товара. Вам перезвонит менеджер.
                    Во время разговора с ним скажите о намерении получить рассрочку.
                  </p>
                  <p className="text-gray-600 text-sm mt-2">
                    Менеджер вышлет ссылку на заполнение заявки на рассрочку. Любым удобным способом — почта, sms, WhatsApp и т.д.
                  </p>
                </div>
              </div>

              {/* Step 2 */}
              <div className="flex gap-4">
                <div className="flex-shrink-0 w-10 h-10 bg-[#62bb46] rounded-full flex items-center justify-center text-white font-bold">
                  2
                </div>
                <div>
                  <h3 className="font-bold text-[#3d4543] mb-1">Шаг 2</h3>
                  <p className="text-gray-600 text-sm">
                    После заполнения данных, отправьте заявку на рассрочку и дождитесь ответа от банка.
                    Весь процесс займёт не более 15 минут.
                  </p>
                </div>
              </div>

              {/* Step 3 */}
              <div className="flex gap-4">
                <div className="flex-shrink-0 w-10 h-10 bg-[#62bb46] rounded-full flex items-center justify-center text-white font-bold">
                  3
                </div>
                <div>
                  <h3 className="font-bold text-[#3d4543] mb-1">Шаг 3</h3>
                  <p className="text-gray-600 text-sm">
                    После получения одобрения от банка, с Вами свяжется менеджер Е1 и оформит заказ.
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Image */}
          <div className="relative h-[400px] rounded-xl overflow-hidden shadow-xl">
            <Image
              src="/images/wardrobe-preview.jpg"
              alt="Шкаф-купе в рассрочку"
              fill
              className="object-cover"
              onError={(e) => {
                const target = e.target as HTMLImageElement;
                target.style.display = 'none';
              }}
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
            <div className="absolute bottom-4 left-4 right-4 text-white">
              <p className="text-sm opacity-90">Современные шкафы-купе с рассрочкой 0%</p>
            </div>
          </div>
        </div>

        {/* Form section */}
        <div className="max-w-2xl mx-auto">
          <div className="bg-[#f9fafb] rounded-2xl p-8 shadow-sm">
            <h2 className="text-2xl font-bold text-[#3d4543] mb-2 text-center">
              Хотите оформить рассрочку без переплат?
            </h2>
            <p className="text-gray-600 text-center mb-8">
              Свяжемся с Вами, подберём шкаф и оформим все документы
            </p>

            {submitResult && (
              <div className={`mb-6 p-4 rounded-lg ${submitResult.success ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
                {submitResult.message}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                  Ваше имя *
                </label>
                <input
                  type="text"
                  id="name"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none transition-all"
                  placeholder="Введите ваше имя"
                  required
                />
              </div>

              <div>
                <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-1">
                  Телефон *
                </label>
                <input
                  type="tel"
                  id="phone"
                  value={formData.phone}
                  onChange={handlePhoneChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none transition-all"
                  placeholder="+7 (___) ___-__-__"
                  required
                />
              </div>

              <div className="flex items-start gap-3">
                <input
                  type="checkbox"
                  id="agree"
                  checked={agreed}
                  onChange={(e) => setAgreed(e.target.checked)}
                  className="mt-1 w-4 h-4 text-[#62bb46] border-gray-300 rounded focus:ring-[#62bb46]"
                />
                <label htmlFor="agree" className="text-sm text-gray-600">
                  Я согласен на{' '}
                  <Link href="/privacy-policy" className="text-[#62bb46] hover:underline">
                    обработку персональных данных
                  </Link>
                </label>
              </div>

              <div className="flex gap-4 pt-4">
                <button
                  type="submit"
                  disabled={!agreed || isSubmitting || !formData.name || !formData.phone}
                  className="flex-1 bg-[#62bb46] text-white font-bold py-3 px-6 rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {isSubmitting ? 'Отправка...' : 'Отправить'}
                </button>
                <button
                  type="button"
                  onClick={handleReset}
                  className="px-6 py-3 border border-gray-300 text-gray-600 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Сбросить
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Legal info */}
        <div className="mt-12 text-center text-xs text-gray-500 max-w-3xl mx-auto">
          <p>
            *«Онлайн-рассрочка без переплаты» представляет из себя кредитный продукт МКК_0-0-6_СКС_Экспресс
            от ООО «Хоум Кредит энд Финанс Банк». Генеральная лицензия №316 Банка России от 15.03.2012г.
            В случае оплаты за 6 месяцев не возникает переплаты относительно исходной розничной цены.
          </p>
          <p className="mt-2">Подробности у продавцов-консультантов.</p>
        </div>
      </div>
    </div>
  );
}
