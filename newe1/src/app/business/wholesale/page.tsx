'use client';

import { useState } from 'react';
import Image from 'next/image';

interface FormData {
  full_name: string;
  city: string;
  company_name: string;
  website: string;
  email: string;
}

export default function WholesalePage() {
  const [formData, setFormData] = useState<FormData>({
    full_name: '',
    city: '',
    company_name: '',
    website: '',
    email: '',
  });
  const [errors, setErrors] = useState<Partial<FormData>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);

  const validateForm = (): boolean => {
    const newErrors: Partial<FormData> = {};

    if (!formData.full_name.trim()) {
      newErrors.full_name = 'Введите ФИО';
    }
    if (!formData.city.trim()) {
      newErrors.city = 'Введите город';
    }
    if (!formData.company_name.trim()) {
      newErrors.company_name = 'Введите название компании';
    }
    if (!formData.email.trim()) {
      newErrors.email = 'Введите email';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'Введите корректный email';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;

    setIsSubmitting(true);
    try {
      const response = await fetch('/api/wholesale', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.success) {
        setIsSubmitted(true);
      } else {
        setErrors({ email: data.error || 'Ошибка отправки. Попробуйте позже.' });
      }
    } catch {
      setErrors({ email: 'Ошибка сервера. Попробуйте позже.' });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (isSubmitted) {
    return (
      <div className="min-h-screen bg-gray-50">
        <div className="bg-[#62bb46] text-white py-12">
          <div className="container-custom text-center">
            <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h1 className="text-2xl md:text-3xl font-bold mb-3">Заявка отправлена!</h1>
            <p className="text-lg opacity-90 max-w-xl mx-auto">
              Наш менеджер свяжется с вами в ближайшее время с оптовым прайсом и персональным предложением.
            </p>
          </div>
        </div>

        <div className="container-custom py-10">
          <div className="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-6 text-center">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Что дальше?</h2>
            <ul className="text-left space-y-3 mb-6">
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">1</span>
                <span className="text-gray-700 text-sm">Мы изучим вашу заявку и подготовим оптовый прайс</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">2</span>
                <span className="text-gray-700 text-sm">Свяжемся с вами для обсуждения условий сотрудничества</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">3</span>
                <span className="text-gray-700 text-sm">Предложим персональные условия под ваш бизнес</span>
              </li>
            </ul>
            <a
              href="/business/wholesale"
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
      {/* Hero Section */}
      <div className="relative text-white py-16 md:py-24 min-h-[400px] md:min-h-[500px] flex items-center bg-gradient-to-br from-[#3d4543] to-[#2a302e]">
        <div className="absolute inset-0 z-0">
          <Image
            src="/images/wholesale.jpg"
            alt="Оптовые продажи Е1"
            fill
            className="object-cover"
            priority
            onError={(e) => {
              e.currentTarget.style.display = 'none';
            }}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-[#2a302e]/90 via-[#3d4543]/70 to-transparent"></div>
        </div>

        <div className="container-custom relative z-10">
          <div className="max-w-2xl">
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 drop-shadow-lg" style={{ color: '#ffffff' }}>
              Оптовые продажи
            </h1>
            <p className="text-xl md:text-2xl font-medium mb-4 opacity-95">
              Надёжный поставщик шкафов и гардеробных для вашего бизнеса
            </p>
            <p className="text-base md:text-lg opacity-90">
              Е1 — один из крупнейших производителей корпусной мебели в России.
              Мы выстраиваем долгосрочные партнёрства с розничными сетями, дилерами, застройщиками.
            </p>
          </div>
        </div>
      </div>

      {/* Преимущества */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
            Почему партнёры выбирают Е1
          </h2>
          <p className="text-center text-gray-600 mb-8">Надёжность, качество и выгодные условия</p>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Качественная продукция</h3>
                <p className="text-sm text-gray-600">Менее 1% рекламаций по итогам отгрузок</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Более 5 000 конфигураций</h3>
                <p className="text-sm text-gray-600">Модульные решения и готовые серии под разные ценовые сегменты</p>
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
                <h3 className="font-bold text-gray-900 mb-1">Доставка в 500+ городов</h3>
                <p className="text-sm text-gray-600">Сроки от 3 дней благодаря распределённой логистике</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">20 000+ изделий в месяц</h3>
                <p className="text-sm text-gray-600">Стабильные объёмы и соблюдение сроков производства</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-[#62bb46]/10 rounded-lg border border-[#62bb46]/30">
              <div className="w-10 h-10 bg-[#62bb46] rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Маржинальность до 50%</h3>
                <p className="text-sm text-gray-600">Конкурентные оптовые цены и понятная экономика</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Гибкая система скидок</h3>
                <p className="text-sm text-gray-600">Индивидуальные условия в зависимости от объёма</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-gray-50 rounded-lg md:col-span-2 lg:col-span-1">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Маркетинговая поддержка</h3>
                <p className="text-sm text-gray-600">Каталоги, визуалы, контент, помощь в продажах и онлайн-инструменты</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Форматы сотрудничества */}
      <div className="py-12 bg-gray-50">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Форматы сотрудничества
          </h2>

          <div className="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <div className="bg-white rounded-xl p-6 shadow-sm">
              <div className="w-12 h-12 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">Оптовые поставки</h3>
              <p className="text-sm text-gray-600">Начните сотрудничество от 1 шкафа. Выгодные условия при увеличении объёмов.</p>
            </div>

            <div className="bg-white rounded-xl p-6 shadow-sm">
              <div className="w-12 h-12 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">Для розничных сетей</h3>
              <p className="text-sm text-gray-600">Специальные условия федеральным и региональным сетям.</p>
            </div>

            <div className="bg-white rounded-xl p-6 shadow-sm">
              <div className="w-12 h-12 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">White Label</h3>
              <p className="text-sm text-gray-600">Контрактное производство под вашим брендом.</p>
            </div>

            <div className="bg-white rounded-xl p-6 shadow-sm">
              <div className="w-12 h-12 bg-[#62bb46]/10 rounded-lg flex items-center justify-center mb-4">
                <svg className="w-6 h-6 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">Интернет-магазины и маркетплейсы</h3>
              <p className="text-sm text-gray-600">Поставки для онлайн-продаж с полной поддержкой.</p>
            </div>
          </div>
        </div>
      </div>

      {/* Форма заявки */}
      <div className="py-12 bg-white" id="apply">
        <div className="container-custom">
          <div className="max-w-xl mx-auto">
            <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
              Получить оптовый прайс
            </h2>
            <p className="text-center text-gray-600 text-sm mb-6">
              Оставьте заявку и получите оптовый прайс, условия сотрудничества и персональное предложение под ваш бизнес
            </p>

            <div className="bg-gray-50 rounded-xl shadow-lg p-6">
              <div className="space-y-4">
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

                {/* City */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Город <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formData.city}
                    onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                    placeholder="Москва"
                    className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                      errors.city ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.city && (
                    <p className="mt-1 text-xs text-red-500">{errors.city}</p>
                  )}
                </div>

                {/* Company Name */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Название компании <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formData.company_name}
                    onChange={(e) => setFormData({ ...formData, company_name: e.target.value })}
                    placeholder="ООО «Ваша компания»"
                    className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                      errors.company_name ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.company_name && (
                    <p className="mt-1 text-xs text-red-500">{errors.company_name}</p>
                  )}
                </div>

                {/* Website */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Сайт компании
                  </label>
                  <input
                    type="url"
                    value={formData.website}
                    onChange={(e) => setFormData({ ...formData, website: e.target.value })}
                    placeholder="https://..."
                    className="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm"
                  />
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
                    placeholder="example@company.ru"
                    className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                      errors.email ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.email && (
                    <p className="mt-1 text-xs text-red-500">{errors.email}</p>
                  )}
                </div>

                <button
                  onClick={handleSubmit}
                  disabled={isSubmitting}
                  className="w-full bg-[#62bb46] text-white py-3 rounded-lg font-bold hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                >
                  {isSubmitting ? 'Отправка...' : 'Получить прайс'}
                </button>

                <p className="text-xs text-gray-500 text-center">
                  Нажимая кнопку, вы соглашаетесь с обработкой персональных данных
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="py-8 bg-[#3d4543]">
        <div className="container-custom text-center">
          <h2 className="text-lg md:text-xl font-bold mb-2" style={{ color: '#ffffff' }}>
            Начните сотрудничество с Е1
          </h2>
          <p className="text-sm mb-4 max-w-lg mx-auto" style={{ color: '#ffffff' }}>
            Получите оптовый прайс и персональное предложение для вашего бизнеса
          </p>
          <a
            href="#apply"
            className="inline-block bg-[#62bb46] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-[#55a83d] transition-colors text-sm"
          >
            Оставить заявку
          </a>
        </div>
      </div>
    </div>
  );
}
