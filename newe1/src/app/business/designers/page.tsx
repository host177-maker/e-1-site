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
        <div className="bg-[#62bb46] text-white py-12">
          <div className="container-custom text-center">
            <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h1 className="text-2xl md:text-3xl font-bold mb-3">Спасибо за доверие!</h1>
            <p className="text-lg opacity-90 max-w-xl mx-auto">
              Мы свяжемся с вами для активации промокода после проверки анкеты.
            </p>
          </div>
        </div>

        <div className="container-custom py-10">
          <div className="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-6 text-center">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Что дальше?</h2>
            <ul className="text-left space-y-3 mb-6">
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">1</span>
                <span className="text-gray-700 text-sm">Наш менеджер проверит вашу анкету в течение 1-2 рабочих дней</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">2</span>
                <span className="text-gray-700 text-sm">Мы свяжемся с вами для обсуждения деталей сотрудничества</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">3</span>
                <span className="text-gray-700 text-sm">После активации ваш промокод <strong className="text-[#62bb46]">{formData.promo_code}</strong> будет готов к использованию</span>
              </li>
            </ul>
            <a
              href="/business/designers"
              className="inline-block bg-[#62bb46] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-[#55a83d] transition-colors text-sm"
            >
              Вернуться
            </a>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section с изображением */}
      <div className="relative text-white py-16 md:py-24 min-h-[400px] md:min-h-[500px] flex items-center bg-gradient-to-br from-[#3d4543] to-[#2a302e]">
        {/* Background Image */}
        <div className="absolute inset-0 z-0">
          <Image
            src="/images/diz.jpg"
            alt="Сотрудничество с дизайнерами"
            fill
            className="object-cover"
            priority
            onError={(e) => {
              // Hide image if it fails to load
              e.currentTarget.style.display = 'none';
            }}
          />
          {/* Gradient overlay for better text readability */}
          <div className="absolute inset-0 bg-gradient-to-l from-[#2a302e]/90 via-[#3d4543]/70 to-transparent"></div>
        </div>

        <div className="container-custom relative z-10">
          <div className="max-w-2xl ml-auto text-right">
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 drop-shadow-lg">
              Сотрудничество с дизайнерами и архитекторами
            </h1>
            <p className="text-lg md:text-xl opacity-95 mb-8 drop-shadow-md">
              Получайте вознаграждение за каждую сделку с вашими клиентами.
              Присоединяйтесь к программе партнёрства Е1!
            </p>
            <div className="flex flex-wrap gap-3 justify-end">
              <div className="flex items-center gap-2 bg-black/30 backdrop-blur-sm px-4 py-2 rounded-lg text-sm">
                <svg className="w-5 h-5 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <span>Скидка для клиентов</span>
              </div>
              <div className="flex items-center gap-2 bg-black/30 backdrop-blur-sm px-4 py-2 rounded-lg text-sm">
                <svg className="w-5 h-5 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <span>Вознаграждение партнёру</span>
              </div>
              <div className="flex items-center gap-2 bg-black/30 backdrop-blur-sm px-4 py-2 rounded-lg text-sm">
                <svg className="w-5 h-5 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                </svg>
                <span>Персональный промокод</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Что вы получаете */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
            Что вы получаете, сотрудничая с E1
          </h2>
          <p className="text-center text-gray-600 mb-8">Персональные возможности для дизайнеров и архитекторов</p>

          <div className="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Персональный промо-код дизайнера</h3>
                <p className="text-sm text-gray-600">Используйте промо-код для своих клиентов при оформлении заказов в салонах E1 или онлайн на сайте e-1.ru.</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Гибкие каналы продаж</h3>
                <p className="text-sm text-gray-600">Заказы оформляются как в розничной сети E1, так и через сайт — удобно для любых проектов.</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Доступ к конструктору шкафов и гардеробных E1</h3>
                <p className="text-sm text-gray-600">Пользуйтесь фирменным конструктором Е1 для проектирования шкафов и гардеробных с учётом размеров, наполнения и конфигурации.</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Поддержка менеджера по проектам</h3>
                <p className="text-sm text-gray-600">Консультации, сопровождение заказов, помощь в подборе решений под задачи клиента.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Как это работает */}
      <div className="py-12 bg-gray-50">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Промо-код дизайнера — как это работает
          </h2>

          <div className="grid md:grid-cols-4 gap-6 max-w-5xl mx-auto">
            <div className="text-center">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">1</div>
              <h3 className="font-bold text-gray-900 mb-1 text-sm">Регистрация и верификация</h3>
              <p className="text-xs text-gray-600">Вы проходите регистрацию и подтверждаете своё портфолио</p>
            </div>
            <div className="text-center">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">2</div>
              <h3 className="font-bold text-gray-900 mb-1 text-sm">Получение промо-кода</h3>
              <p className="text-xs text-gray-600">После верификации вы получаете уникальный промо-код</p>
            </div>
            <div className="text-center">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">3</div>
              <h3 className="font-bold text-gray-900 mb-1 text-sm">Использование клиентом</h3>
              <p className="text-xs text-gray-600">Клиент указывает код при покупке в салоне или на сайте</p>
            </div>
            <div className="text-center">
              <div className="w-12 h-12 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">4</div>
              <h3 className="font-bold text-gray-900 mb-1 text-sm">Преимущества</h3>
              <p className="text-xs text-gray-600">Клиент получает скидку, а вы — вознаграждение</p>
            </div>
          </div>
        </div>
      </div>

      {/* Формы сотрудничества */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Формы сотрудничества
          </h2>

          <div className="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            <div className="border border-gray-200 rounded-xl p-6">
              <h3 className="font-bold text-gray-900 mb-4 text-lg">Для частных дизайнеров</h3>
              <ul className="space-y-2">
                <li className="flex items-center gap-2 text-sm text-gray-600">
                  <svg className="w-4 h-4 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  Работа по промо-коду
                </li>
                <li className="flex items-center gap-2 text-sm text-gray-600">
                  <svg className="w-4 h-4 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  Доступ к конструктору
                </li>
                <li className="flex items-center gap-2 text-sm text-gray-600">
                  <svg className="w-4 h-4 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  Поддержка по заказам
                </li>
              </ul>
            </div>

            <div className="border border-gray-200 rounded-xl p-6">
              <h3 className="font-bold text-gray-900 mb-4 text-lg">Для дизайн-студий</h3>
              <ul className="space-y-2">
                <li className="flex items-center gap-2 text-sm text-gray-600">
                  <svg className="w-4 h-4 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  Корпоративные условия
                </li>
                <li className="flex items-center gap-2 text-sm text-gray-600">
                  <svg className="w-4 h-4 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  Сопровождение проектов
                </li>
                <li className="flex items-center gap-2 text-sm text-gray-600">
                  <svg className="w-4 h-4 text-[#62bb46]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                  </svg>
                  Работа с несколькими клиентами параллельно
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Registration Form Section */}
      <div className="py-12 bg-gray-50" id="register">
        <div className="container-custom">
          <div className="max-w-xl mx-auto">
            <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
              Регистрация партнёра
            </h2>
            <p className="text-center text-gray-600 text-sm mb-6">
              Заполните форму, и мы свяжемся с вами для обсуждения деталей
            </p>

            {/* Progress Steps */}
            <div className="flex items-center justify-center mb-6">
              <div className="flex items-center">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm ${step >= 1 ? 'bg-[#62bb46] text-white' : 'bg-gray-200 text-gray-500'}`}>
                  1
                </div>
                <div className={`w-16 h-1 ${step >= 2 ? 'bg-[#62bb46]' : 'bg-gray-200'}`}></div>
                <div className={`w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm ${step >= 2 ? 'bg-[#62bb46] text-white' : 'bg-gray-200 text-gray-500'}`}>
                  2
                </div>
              </div>
            </div>

            <div className="bg-white rounded-xl shadow-lg p-6">
              {step === 1 && (
                <div className="space-y-4">
                  <h3 className="text-lg font-bold text-gray-900 mb-3">Шаг 1: Ваши данные</h3>

                  {/* Business Type */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Форма ведения деятельности <span className="text-red-500">*</span>
                    </label>
                    <div className="grid grid-cols-2 gap-2">
                      {(Object.keys(businessTypeLabels) as BusinessType[]).map((type) => (
                        <button
                          key={type}
                          type="button"
                          onClick={() => setFormData({ ...formData, business_type: type })}
                          className={`p-2.5 border-2 rounded-lg text-xs font-medium transition-colors ${
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
                      <p className="mt-1 text-xs text-red-500">{errors.business_type}</p>
                    )}
                  </div>

                  {/* Full Name */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Ваше ФИО <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      value={formData.full_name}
                      onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                      placeholder="Иванов Иван Иванович"
                      className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                        errors.full_name ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.full_name && (
                      <p className="mt-1 text-xs text-red-500">{errors.full_name}</p>
                    )}
                  </div>

                  {/* Phone */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Ваш номер телефона <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="tel"
                      value={formData.phone}
                      onChange={handlePhoneChange}
                      placeholder="+7 (xxx) xxx-xx-xx"
                      className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                        errors.phone ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.phone && (
                      <p className="mt-1 text-xs text-red-500">{errors.phone}</p>
                    )}
                  </div>

                  {/* Email */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      E-mail <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="email"
                      value={formData.email}
                      onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                      placeholder="example@mail.ru"
                      className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                        errors.email ? 'border-red-500' : 'border-gray-300'
                      }`}
                    />
                    {errors.email && (
                      <p className="mt-1 text-xs text-red-500">{errors.email}</p>
                    )}
                  </div>

                  {/* Portfolio Link */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Ссылка на портфолио
                    </label>
                    <input
                      type="url"
                      value={formData.portfolio_link}
                      onChange={(e) => setFormData({ ...formData, portfolio_link: e.target.value })}
                      placeholder="https://..."
                      className="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm"
                    />
                    <p className="mt-1 text-xs text-gray-500">
                      Сайт, Behance, Instagram или другая площадка
                    </p>
                  </div>

                  <button
                    onClick={handleStep1Submit}
                    disabled={isSubmitting}
                    className="w-full bg-[#62bb46] text-white py-3 rounded-lg font-bold hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                  >
                    {isSubmitting ? 'Сохранение...' : 'Перейти к шагу 2'}
                  </button>
                </div>
              )}

              {step === 2 && (
                <div className="space-y-4">
                  <h3 className="text-lg font-bold text-gray-900 mb-3">Шаг 2: Выберите промокод</h3>

                  <div className="bg-[#62bb46]/10 border border-[#62bb46]/30 rounded-lg p-3 mb-4">
                    <p className="text-sm text-gray-700">
                      <strong>Как это работает:</strong> Мы создаём вам персональный промокод.
                      Ваш клиент при оформлении заказа получает <strong>скидку 5%</strong> на мебель
                      (кроме товаров первой цены). А вы получаете <strong>вознаграждение</strong> по
                      завершении сделки.
                    </p>
                  </div>

                  {/* Promo Code */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Ваш персональный промокод <span className="text-red-500">*</span>
                    </label>
                    <div className="flex gap-2">
                      <input
                        type="text"
                        value={formData.promo_code}
                        onChange={(e) => {
                          setFormData({ ...formData, promo_code: e.target.value.toUpperCase() });
                          setPromoCheckResult(null);
                        }}
                        placeholder="DESIGNER2024"
                        className={`flex-1 px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] uppercase text-sm ${
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
                        className="px-4 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors disabled:opacity-50"
                      >
                        {isCheckingPromo ? '...' : 'Проверить'}
                      </button>
                    </div>
                    <p className="mt-1 text-xs text-gray-500">
                      Только буквы английского алфавита и цифры, минимум 3 символа
                    </p>
                    {promoCheckResult && (
                      <p className={`mt-1 text-xs font-medium ${promoCheckResult.available ? 'text-green-600' : 'text-red-500'}`}>
                        {promoCheckResult.available ? '✓ ' : '✗ '}{promoCheckResult.message}
                      </p>
                    )}
                  </div>

                  <div className="flex gap-3">
                    <button
                      onClick={() => setStep(1)}
                      className="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-bold hover:bg-gray-300 transition-colors text-sm"
                    >
                      Назад
                    </button>
                    <button
                      onClick={handleStep2Submit}
                      disabled={isSubmitting || !promoCheckResult?.available}
                      className="flex-1 bg-[#62bb46] text-white py-3 rounded-lg font-bold hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                    >
                      {isSubmitting ? 'Отправка...' : 'Завершить'}
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Почему дизайнеры выбирают E1 */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Почему дизайнеры выбирают E1
          </h2>

          <div className="grid md:grid-cols-3 lg:grid-cols-5 gap-4 max-w-5xl mx-auto">
            <div className="text-center p-4">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 text-sm mb-1">Продуманный ассортимент</h3>
              <p className="text-xs text-gray-600">Шкафы и гардеробные для разных стилей</p>
            </div>

            <div className="text-center p-4">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 text-sm mb-1">Удобный конфигуратор</h3>
              <p className="text-xs text-gray-600">Быстрое проектирование под размеры</p>
            </div>

            <div className="text-center p-4">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 text-sm mb-1">Поставка под ключ</h3>
              <p className="text-xs text-gray-600">Производство, доставка, сборка</p>
            </div>

            <div className="text-center p-4">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 text-sm mb-1">Поддержка проекта</h3>
              <p className="text-xs text-gray-600">От идеи до установки</p>
            </div>

            <div className="text-center p-4">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 text-sm mb-1">Условия для профи</h3>
              <p className="text-xs text-gray-600">Специальная программа</p>
            </div>
          </div>
        </div>
      </div>

      {/* FAQ Section */}
      <div className="py-12 bg-gray-50">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Часто задаваемые вопросы
          </h2>
          <div className="max-w-2xl mx-auto space-y-3">
            <details className="group bg-white rounded-lg shadow-sm">
              <summary className="flex items-center justify-between p-4 cursor-pointer">
                <span className="font-medium text-gray-900 text-sm">Как получить промо-код дизайнера?</span>
                <svg className="w-4 h-4 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-4 pb-4 text-sm text-gray-600">
                Пройдите регистрацию, после верификации вашего портфолио вы получите персональный промо-код.
              </div>
            </details>
            <details className="group bg-white rounded-lg shadow-sm">
              <summary className="flex items-center justify-between p-4 cursor-pointer">
                <span className="font-medium text-gray-900 text-sm">Где можно использовать промо-код?</span>
                <svg className="w-4 h-4 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-4 pb-4 text-sm text-gray-600">
                В фирменных салонах E1 и при оформлении заказов на сайте e-1.ru.
              </div>
            </details>
            <details className="group bg-white rounded-lg shadow-sm">
              <summary className="flex items-center justify-between p-4 cursor-pointer">
                <span className="font-medium text-gray-900 text-sm">Какие преимущества я получаю как дизайнер?</span>
                <svg className="w-4 h-4 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
              </summary>
              <div className="px-4 pb-4 text-sm text-gray-600">
                Доступ к конструктору, специальные условия, сопровождение заказов и удобный инструмент для работы с клиентами.
              </div>
            </details>
          </div>
        </div>
      </div>

      {/* CTA Section - компактный */}
      <div className="py-8 bg-[#3d4543]">
        <div className="container-custom text-center">
          <h2 className="text-lg md:text-xl font-bold text-white mb-2">
            Готовы начать сотрудничество?
          </h2>
          <p className="text-white text-sm mb-4 max-w-lg mx-auto">
            Зарегистрируйтесь и получите персональный промокод для ваших клиентов
          </p>
          <a
            href="#register"
            className="inline-block bg-[#62bb46] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-[#55a83d] transition-colors text-sm"
          >
            Зарегистрироваться
          </a>
        </div>
      </div>
    </div>
  );
}
