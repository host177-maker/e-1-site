'use client';

import { useState } from 'react';
import Image from 'next/image';

type BusinessType = 'individual' | 'self_employed' | 'individual_entrepreneur' | 'llc';

interface FormData {
  business_type: BusinessType | '';
  full_name: string;
  phone: string;
  email: string;
  portfolio_link: string;
  promo_code: string;
}

const businessTypeLabels: Record<BusinessType, string> = {
  individual: 'Физическое лицо',
  self_employed: 'Самозанятый',
  individual_entrepreneur: 'Индивидуальный предприниматель',
  llc: 'ООО',
};

export default function DesignersPage() {
  const [step, setStep] = useState(1);
  const [designerId, setDesignerId] = useState<number | null>(null);
  const [formData, setFormData] = useState<FormData>({
    business_type: '',
    full_name: '',
    phone: '',
    email: '',
    portfolio_link: '',
    promo_code: '',
  });
  const [errors, setErrors] = useState<Partial<FormData>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [promoCheckResult, setPromoCheckResult] = useState<{
    available: boolean;
    message: string;
    normalized?: string;
  } | null>(null);
  const [isCheckingPromo, setIsCheckingPromo] = useState(false);
  const [isCompleted, setIsCompleted] = useState(false);

  const formatPhone = (value: string) => {
    const digits = value.replace(/\D/g, '');
    if (digits.length === 0) return '';
    if (digits.length <= 1) return `+7 (${digits}`;
    if (digits.length <= 4) return `+7 (${digits.slice(1)}`;
    if (digits.length <= 7) return `+7 (${digits.slice(1, 4)}) ${digits.slice(4)}`;
    if (digits.length <= 9) return `+7 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7)}`;
    return `+7 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7, 9)}-${digits.slice(9, 11)}`;
  };

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    let value = e.target.value;
    if (!value.startsWith('+7') && value.length > 0) {
      value = '+7' + value.replace(/^\+?7?/, '');
    }
    const formatted = formatPhone(value);
    setFormData({ ...formData, phone: formatted });
  };

  const validateStep1 = (): boolean => {
    const newErrors: Partial<FormData> = {};

    if (!formData.business_type) {
      newErrors.business_type = 'Выберите форму ведения деятельности';
    }
    if (!formData.full_name.trim()) {
      newErrors.full_name = 'Введите ФИО';
    }
    if (!formData.phone || formData.phone.replace(/\D/g, '').length < 11) {
      newErrors.phone = 'Введите корректный номер телефона';
    }
    if (!formData.email.trim()) {
      newErrors.email = 'Введите email';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Введите корректный email';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleStep1Submit = async () => {
    if (!validateStep1()) return;

    setIsSubmitting(true);
    try {
      const response = await fetch('/api/designers', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          business_type: formData.business_type,
          full_name: formData.full_name,
          phone: formData.phone,
          email: formData.email,
          portfolio_link: formData.portfolio_link,
          step: 1,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setDesignerId(data.data.id);
        setStep(2);
      } else {
        setErrors({ email: data.error });
      }
    } catch {
      setErrors({ email: 'Ошибка сервера. Попробуйте позже.' });
    } finally {
      setIsSubmitting(false);
    }
  };

  const checkPromoCode = async () => {
    if (!formData.promo_code.trim()) {
      setPromoCheckResult({
        available: false,
        message: 'Введите промокод',
      });
      return;
    }

    setIsCheckingPromo(true);
    try {
      const response = await fetch(
        `/api/designers/check-promo?code=${encodeURIComponent(formData.promo_code)}&designer_id=${designerId}`
      );
      const data = await response.json();

      if (data.success) {
        setPromoCheckResult({
          available: data.available,
          message: data.message || data.error,
          normalized: data.normalized,
        });
        if (data.normalized) {
          setFormData({ ...formData, promo_code: data.normalized });
        }
      }
    } catch {
      setPromoCheckResult({
        available: false,
        message: 'Ошибка проверки. Попробуйте позже.',
      });
    } finally {
      setIsCheckingPromo(false);
    }
  };

  const handleStep2Submit = async () => {
    if (!promoCheckResult?.available) {
      setPromoCheckResult({
        available: false,
        message: 'Сначала проверьте доступность промокода',
      });
      return;
    }

    setIsSubmitting(true);
    try {
      const response = await fetch('/api/designers', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          designer_id: designerId,
          promo_code: formData.promo_code,
          step: 2,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setIsCompleted(true);
      } else {
        setPromoCheckResult({
          available: false,
          message: data.error,
        });
      }
    } catch {
      setPromoCheckResult({
        available: false,
        message: 'Ошибка сервера. Попробуйте позже.',
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (isCompleted) {
    return (
      <div className="min-h-screen bg-gray-50">
        {/* Hero */}
        <div className="bg-[#62bb46] text-white py-16">
          <div className="container-custom text-center">
            <div className="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6">
              <svg className="w-10 h-10 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h1 className="text-3xl md:text-4xl font-bold mb-4">Спасибо за доверие!</h1>
            <p className="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">
              Ваша заявка успешно отправлена. Мы свяжемся с вами для активации промокода после проверки анкеты.
            </p>
          </div>
        </div>

        <div className="container-custom py-12">
          <div className="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 text-center">
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Что дальше?</h2>
            <ul className="text-left space-y-4 mb-8">
              <li className="flex items-start gap-3">
                <span className="w-8 h-8 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 font-bold">1</span>
                <span className="text-gray-700">Наш менеджер проверит вашу анкету в течение 1-2 рабочих дней</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-8 h-8 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 font-bold">2</span>
                <span className="text-gray-700">Мы свяжемся с вами для обсуждения деталей сотрудничества</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-8 h-8 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 font-bold">3</span>
                <span className="text-gray-700">После активации ваш промокод <strong className="text-[#62bb46]">{formData.promo_code}</strong> будет готов к использованию</span>
              </li>
            </ul>
            <a
              href="/"
              className="inline-block bg-[#62bb46] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#55a83d] transition-colors"
            >
              Вернуться на главную
            </a>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section */}
      <div className="bg-gradient-to-br from-[#3d4543] to-[#2a302e] text-white py-16 md:py-24">
        <div className="container-custom">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
                Сотрудничество с дизайнерами и архитекторами
              </h1>
              <p className="text-lg md:text-xl opacity-90 mb-8">
                Получайте вознаграждение за каждую сделку с вашими клиентами.
                Присоединяйтесь к программе партнёрства Е1!
              </p>
              <div className="flex flex-wrap gap-4">
                <div className="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-lg">
                  <svg className="w-5 h-5 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  <span>Скидка 5% для клиентов</span>
                </div>
                <div className="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-lg">
                  <svg className="w-5 h-5 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  <span>Вознаграждение партнёру</span>
                </div>
                <div className="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-lg">
                  <svg className="w-5 h-5 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  <span>Персональный промокод</span>
                </div>
              </div>
            </div>
            <div className="hidden lg:block">
              <div className="relative">
                <div className="absolute inset-0 bg-[#62bb46] rounded-2xl transform rotate-3"></div>
                <Image
                  src="/images/catalog/garderobnye.jpg"
                  alt="Дизайнерская мебель"
                  width={600}
                  height={400}
                  className="relative rounded-2xl shadow-2xl object-cover"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Benefits Section */}
      <div className="py-16 bg-white">
        <div className="container-custom">
          <h2 className="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-12">
            Преимущества партнёрства
          </h2>
          <div className="grid md:grid-cols-3 gap-8">
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-[#62bb46]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Вознаграждение</h3>
              <p className="text-gray-600">
                Получайте вознаграждение за каждую завершённую сделку с вашими клиентами
              </p>
            </div>
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-[#62bb46]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Скидка 5%</h3>
              <p className="text-gray-600">
                Ваши клиенты получают скидку 5% на мебель по вашему персональному промокоду
              </p>
            </div>
            <div className="text-center p-6">
              <div className="w-16 h-16 bg-[#62bb46]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Удобство покупки</h3>
              <p className="text-gray-600">
                Покупки возможны в розничной сети салонов или на сайте e-1.ru
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Registration Form Section */}
      <div className="py-16" id="register">
        <div className="container-custom">
          <div className="max-w-2xl mx-auto">
            <h2 className="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-4">
              Регистрация партнёра
            </h2>
            <p className="text-center text-gray-600 mb-8">
              Заполните форму, и мы свяжемся с вами для обсуждения деталей сотрудничества
            </p>

            {/* Progress Steps */}
            <div className="flex items-center justify-center mb-8">
              <div className="flex items-center">
                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${step >= 1 ? 'bg-[#62bb46] text-white' : 'bg-gray-200 text-gray-500'}`}>
                  1
                </div>
                <div className={`w-24 h-1 ${step >= 2 ? 'bg-[#62bb46]' : 'bg-gray-200'}`}></div>
                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${step >= 2 ? 'bg-[#62bb46] text-white' : 'bg-gray-200 text-gray-500'}`}>
                  2
                </div>
              </div>
            </div>

            <div className="bg-white rounded-xl shadow-lg p-8">
              {step === 1 && (
                <div className="space-y-6">
                  <h3 className="text-xl font-bold text-gray-900 mb-4">Шаг 1: Ваши данные</h3>

                  {/* Business Type */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Форма ведения деятельности <span className="text-red-500">*</span>
                    </label>
                    <div className="grid grid-cols-2 gap-3">
                      {(Object.keys(businessTypeLabels) as BusinessType[]).map((type) => (
                        <button
                          key={type}
                          type="button"
                          onClick={() => setFormData({ ...formData, business_type: type })}
                          className={`p-3 border-2 rounded-lg text-sm font-medium transition-colors ${
                            formData.business_type === type
                              ? 'border-[#62bb46] bg-[#62bb46]/10 text-[#62bb46]'
                              : 'border-gray-200 hover:border-gray-300 text-gray-700'
                          }`}
                        >
                          {businessTypeLabels[type]}
                        </button>
                      ))}
                    </div>
                    {errors.business_type && (
                      <p className="mt-1 text-sm text-red-500">{errors.business_type}</p>
                    )}
                  </div>

                  {/* Full Name */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ваше ФИО <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      value={formData.full_name}
                      onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                      placeholder="Иванов Иван Иванович"
                      className={`w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] ${
                        errors.full_name ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.full_name && (
                      <p className="mt-1 text-sm text-red-500">{errors.full_name}</p>
                    )}
                  </div>

                  {/* Phone */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ваш номер телефона <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="tel"
                      value={formData.phone}
                      onChange={handlePhoneChange}
                      placeholder="+7 (xxx) xxx-xx-xx"
                      className={`w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] ${
                        errors.phone ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.phone && (
                      <p className="mt-1 text-sm text-red-500">{errors.phone}</p>
                    )}
                  </div>

                  {/* Email */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      E-mail <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="email"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      placeholder="example@mail.ru"
                      className={`w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] ${
                        errors.email ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.email && (
                      <p className="mt-1 text-sm text-red-500">{errors.email}</p>
                    )}
                  </div>

                  {/* Portfolio Link */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ссылка на портфолио
                    </label>
                    <input
                      type="url"
                      value={formData.portfolio_link}
                      onChange={(e) => setFormData({ ...formData, portfolio_link: e.target.value })}
                      placeholder="https://..."
                      className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46]"
                    />
                    <p className="mt-1 text-xs text-gray-500">
                      Сайт, Behance, Instagram или другая площадка с вашими работами
                    </p>
                  </div>

                  <button
                    onClick={handleStep1Submit}
                    disabled={isSubmitting}
                    className="w-full bg-[#62bb46] text-white py-4 rounded-lg font-bold hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {isSubmitting ? 'Сохранение...' : 'Перейти к шагу 2'}
                  </button>
                </div>
              )}

              {step === 2 && (
                <div className="space-y-6">
                  <h3 className="text-xl font-bold text-gray-900 mb-4">Шаг 2: Выберите промокод</h3>

                  <div className="bg-[#62bb46]/10 border border-[#62bb46]/30 rounded-lg p-4 mb-6">
                    <p className="text-gray-700">
                      <strong>Как это работает:</strong> Мы создаём вам персональный промокод.
                      Ваш клиент при оформлении заказа получает <strong>скидку 5%</strong> на мебель
                      (кроме товаров первой цены). А вы получаете <strong>вознаграждение</strong> по
                      завершении сделки. Детали сотрудничества обсудим при звонке.
                    </p>
                  </div>

                  {/* Promo Code */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ваш персональный промокод <span className="text-red-500">*</span>
                    </label>
                    <div className="flex gap-3">
                      <input
                        type="text"
                        value={formData.promo_code}
                        onChange={(e) => {
                          setFormData({ ...formData, promo_code: e.target.value.toUpperCase() });
                          setPromoCheckResult(null);
                        }}
                        placeholder="DESIGNER2024"
                        className={`flex-1 px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] uppercase ${
                          promoCheckResult
                            ? promoCheckResult.available
                              ? 'border-green-500'
                              : 'border-red-500'
                            : 'border-gray-300'
                        }`}
                      />
                      <button
                        onClick={checkPromoCode}
                        disabled={isCheckingPromo}
                        className="px-6 py-3 bg-gray-800 text-white rounded-lg font-medium hover:bg-gray-700 transition-colors disabled:opacity-50"
                      >
                        {isCheckingPromo ? 'Проверка...' : 'Проверить'}
                      </button>
                    </div>
                    <p className="mt-2 text-xs text-gray-500">
                      Промокод состоит из букв английского алфавита и цифр. Минимум 3 символа.
                    </p>
                    {promoCheckResult && (
                      <p className={`mt-2 text-sm font-medium ${promoCheckResult.available ? 'text-green-600' : 'text-red-500'}`}>
                        {promoCheckResult.available ? '✓ ' : '✗ '}{promoCheckResult.message}
                      </p>
                    )}
                  </div>

                  <div className="flex gap-4">
                    <button
                      onClick={() => setStep(1)}
                      className="flex-1 bg-gray-200 text-gray-700 py-4 rounded-lg font-bold hover:bg-gray-300 transition-colors"
                    >
                      Назад
                    </button>
                    <button
                      onClick={handleStep2Submit}
                      disabled={isSubmitting || !promoCheckResult?.available}
                      className="flex-1 bg-[#62bb46] text-white py-4 rounded-lg font-bold hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {isSubmitting ? 'Отправка...' : 'Завершить регистрацию'}
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* FAQ Section */}
      <div className="py-16 bg-white">
        <div className="container-custom">
          <h2 className="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-12">
            Частые вопросы
          </h2>
          <div className="max-w-3xl mx-auto space-y-4">
            <details className="group bg-gray-50 rounded-lg">
              <summary className="flex items-center justify-between p-6 cursor-pointer">
                <span className="font-medium text-gray-900">Кто может стать партнёром?</span>
                <svg className="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-6 pb-6 text-gray-600">
                Дизайнеры интерьеров, архитекторы, декораторы и другие специалисты, работающие с клиентами
                над обустройством жилых и коммерческих пространств.
              </div>
            </details>
            <details className="group bg-gray-50 rounded-lg">
              <summary className="flex items-center justify-between p-6 cursor-pointer">
                <span className="font-medium text-gray-900">Как рассчитывается вознаграждение?</span>
                <svg className="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-6 pb-6 text-gray-600">
                Детали расчёта вознаграждения обсуждаются индивидуально при заключении партнёрского
                соглашения. Свяжитесь с нами для получения подробной информации.
              </div>
            </details>
            <details className="group bg-gray-50 rounded-lg">
              <summary className="flex items-center justify-between p-6 cursor-pointer">
                <span className="font-medium text-gray-900">Где клиенты могут использовать промокод?</span>
                <svg className="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-6 pb-6 text-gray-600">
                Промокод действует при покупке в любом салоне розничной сети Е1, а также при оформлении
                заказа на сайте e-1.ru.
              </div>
            </details>
            <details className="group bg-gray-50 rounded-lg">
              <summary className="flex items-center justify-between p-6 cursor-pointer">
                <span className="font-medium text-gray-900">На какие товары распространяется скидка?</span>
                <svg className="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-6 pb-6 text-gray-600">
                Скидка 5% распространяется на всю мебель, кроме товаров первой цены (товары с минимальной
                наценкой).
              </div>
            </details>
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="py-16 bg-[#62bb46]">
        <div className="container-custom text-center">
          <h2 className="text-2xl md:text-3xl font-bold text-white mb-4">
            Готовы начать сотрудничество?
          </h2>
          <p className="text-white/90 text-lg mb-8 max-w-2xl mx-auto">
            Заполните форму регистрации и получите персональный промокод для ваших клиентов
          </p>
          <a
            href="#register"
            className="inline-block bg-white text-[#62bb46] px-8 py-4 rounded-lg font-bold hover:bg-gray-100 transition-colors"
          >
            Зарегистрироваться
          </a>
        </div>
      </div>
    </div>
  );
}
