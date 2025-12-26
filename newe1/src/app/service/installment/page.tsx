'use client';

import { useState } from 'react';
import Link from 'next/link';

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

  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f8f9fa] py-10">
        <div className="container-custom text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543] mb-2">
            Шкаф сейчас — оплата потом.
          </h1>
          <p className="text-2xl md:text-3xl font-bold text-[#62bb46] mb-3">
            Рассрочка 0% до 6 месяцев
          </p>
          <p className="text-gray-600">
            Без переплат • Без скрытых условий • Оформление за 15 минут
          </p>
        </div>
      </div>

      {/* Benefits row */}
      <div className="container-custom py-10">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {/* Benefit 1 */}
          <div className="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
            <div className="flex-shrink-0 w-12 h-12 bg-[#f0fdf4] rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <div className="font-bold text-[#3d4543]">До 6 месяцев</div>
              <div className="text-sm text-gray-500">Платите комфортно, без спешки.</div>
            </div>
          </div>

          {/* Benefit 2 */}
          <div className="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
            <div className="flex-shrink-0 w-12 h-12 bg-[#f0fdf4] rounded-lg flex items-center justify-center">
              <span className="text-[#62bb46] font-bold text-lg">0%</span>
            </div>
            <div>
              <div className="font-bold text-[#3d4543]">0% переплаты</div>
              <div className="text-sm text-gray-500">Никаких процентов и комиссий</div>
            </div>
          </div>

          {/* Benefit 3 */}
          <div className="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
            <div className="flex-shrink-0 w-12 h-12 bg-[#f0fdf4] rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <div className="font-bold text-[#3d4543]">Онлайн-оформление</div>
              <div className="text-sm text-gray-500">Без визита в банк</div>
            </div>
          </div>

          {/* Benefit 4 */}
          <div className="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
            <div className="flex-shrink-0 w-12 h-12 bg-[#f0fdf4] rounded-lg flex items-center justify-center">
              <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div>
              <div className="font-bold text-[#3d4543]">15 минут</div>
              <div className="text-sm text-gray-500">Быстрое решение</div>
            </div>
          </div>
        </div>
      </div>

      {/* How it works section */}
      <div className="bg-[#f8f9fa] py-12">
        <div className="container-custom">
          <h2 className="text-2xl md:text-3xl font-bold text-[#3d4543] mb-10 text-center">
            Как оформить рассрочку?
          </h2>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            {/* Step 1 */}
            <div className="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow">
              <div className="w-full h-40 bg-gradient-to-br from-[#e8f5e3] to-[#d4edda] rounded-xl mb-4 flex items-center justify-center relative overflow-hidden">
                <div className="absolute top-3 left-3 w-8 h-8 bg-[#62bb46] rounded-full flex items-center justify-center text-white font-bold">1</div>
                <svg className="w-20 h-20 text-[#62bb46]/40" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"/>
                  <path d="M7 12h2v5H7zm4-3h2v8h-2zm4 5h2v3h-2z"/>
                </svg>
              </div>
              <div className="flex items-center gap-2 mb-2">
                <span className="w-6 h-6 bg-[#62bb46] rounded-full flex items-center justify-center text-white text-sm font-bold">1</span>
                <h3 className="font-bold text-[#3d4543]">Выберите шкаф</h3>
              </div>
              <p className="text-sm text-gray-600">
                Рассчитайте стоимость на сайте или с менеджером
              </p>
            </div>

            {/* Step 2 */}
            <div className="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow">
              <div className="w-full h-40 bg-gradient-to-br from-[#e8f5e3] to-[#d4edda] rounded-xl mb-4 flex items-center justify-center relative overflow-hidden">
                <div className="absolute top-3 left-3 w-8 h-8 bg-[#62bb46] rounded-full flex items-center justify-center text-white font-bold">2</div>
                <svg className="w-20 h-20 text-[#62bb46]/40" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
              </div>
              <div className="flex items-center gap-2 mb-2">
                <span className="w-6 h-6 bg-[#62bb46] rounded-full flex items-center justify-center text-white text-sm font-bold">2</span>
                <h3 className="font-bold text-[#3d4543]">Оформите заявку</h3>
              </div>
              <p className="text-sm text-gray-600">
                Онлайн, по телефону или в чате
              </p>
            </div>

            {/* Step 3 */}
            <div className="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-shadow">
              <div className="w-full h-40 bg-gradient-to-br from-[#e8f5e3] to-[#d4edda] rounded-xl mb-4 flex items-center justify-center relative overflow-hidden">
                <div className="absolute top-3 left-3 w-8 h-8 bg-[#62bb46] rounded-full flex items-center justify-center text-white font-bold">3</div>
                <svg className="w-20 h-20 text-[#62bb46]/40" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
              </div>
              <div className="flex items-center gap-2 mb-2">
                <span className="w-6 h-6 bg-[#62bb46] rounded-full flex items-center justify-center text-white text-sm font-bold">3</span>
                <h3 className="font-bold text-[#3d4543]">Получите шкаф</h3>
              </div>
              <p className="text-sm text-gray-600">
                После одобрения — производство и доставка
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Form section */}
      <div id="form" className="py-12">
        <div className="container-custom">
          <div className="max-w-xl mx-auto bg-[#f8f9fa] rounded-2xl p-8">
            <h2 className="text-2xl font-bold text-[#3d4543] mb-2 text-center">
              Оформить рассрочку
            </h2>
            <p className="text-gray-600 text-center mb-8">
              Оставьте заявку — менеджер свяжется с вами и поможет оформить рассрочку
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
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none transition-all bg-white"
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
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent outline-none transition-all bg-white"
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

              <button
                type="submit"
                disabled={!agreed || isSubmitting || !formData.name || !formData.phone}
                className="w-full bg-[#62bb46] text-white font-bold py-4 px-6 rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-lg"
              >
                {isSubmitting ? 'Отправка...' : 'Оформить в рассрочку'}
              </button>
            </form>
          </div>
        </div>
      </div>

      {/* Legal info */}
      <div className="container-custom pb-8">
        <div className="text-center text-xs text-gray-500 max-w-3xl mx-auto">
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
