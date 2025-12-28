'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';

interface FormData {
  full_name: string;
  phone: string;
  company_name: string;
  email: string;
}

export default function MarketplacePage() {
  const [formData, setFormData] = useState<FormData>({
    full_name: '',
    phone: '',
    company_name: '',
    email: '',
  });
  const [errors, setErrors] = useState<Partial<FormData>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [agreeToPrivacy, setAgreeToPrivacy] = useState(false);

  useEffect(() => {
    if (isSubmitted) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }, [isSubmitted]);

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

  const validateForm = (): boolean => {
    const newErrors: Partial<FormData> = {};

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

  const handleSubmit = async () => {
    if (!validateForm()) return;

    setIsSubmitting(true);
    try {
      const response = await fetch('/api/marketplace', {
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
              Наш менеджер свяжется с вами для обсуждения условий сотрудничества.
            </p>
          </div>
        </div>

        <div className="container-custom py-10">
          <div className="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-6 text-center">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Что дальше?</h2>
            <ul className="text-left space-y-3 mb-6">
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">1</span>
                <span className="text-gray-700 text-sm">Мы изучим вашу заявку и подготовим предложение</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">2</span>
                <span className="text-gray-700 text-sm">Свяжемся с вами для обсуждения условий работы</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">3</span>
                <span className="text-gray-700 text-sm">Подключим вас к системе и поможем с первыми заказами</span>
              </li>
            </ul>
            <a
              href="/business/marketplace"
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
            src="/images/marketplace.jpg"
            alt="Продавцам на маркетплейсах"
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
              Продавцам на маркетплейсах
            </h1>
            <p className="text-xl md:text-2xl font-medium mb-4 opacity-95">
              Каждый занимается тем, что умеет лучше всего
            </p>
            <p className="text-base md:text-lg opacity-90 mb-4">
              Мы доставляем шкафы по 40 регионам РФ, а вы умело управляете бюджетами
              и кабинетом на маркетплейсах. Продавая нашу мебель — просто сообщаете, куда доставить.
            </p>
            <p className="text-base md:text-lg opacity-90">
              Работаем по схемам <strong>DBS</strong> и <strong>Real FBS</strong> собственной службой доставки.
            </p>
          </div>
        </div>
      </div>

      {/* Как это работает */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
            Как это работает
          </h2>
          <p className="text-center text-gray-600 mb-8">Простая и прозрачная схема сотрудничества</p>

          <div className="grid md:grid-cols-4 gap-6 max-w-5xl mx-auto">
            <div className="text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">1</div>
              <h3 className="font-bold text-gray-900 mb-2">Вы продаёте</h3>
              <p className="text-sm text-gray-600">Размещаете товары на Wildberries, Ozon, Яндекс Маркет и других площадках</p>
            </div>
            <div className="text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">2</div>
              <h3 className="font-bold text-gray-900 mb-2">Передаёте заказ</h3>
              <p className="text-sm text-gray-600">Сообщаете нам данные заказа через API или личный кабинет</p>
            </div>
            <div className="text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">3</div>
              <h3 className="font-bold text-gray-900 mb-2">Мы связываемся с клиентом</h3>
              <p className="text-sm text-gray-600">Обсуждаем, когда ему удобно получить товар</p>
            </div>
            <div className="text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-xl">4</div>
              <h3 className="font-bold text-gray-900 mb-2">Доставляем с наших складов</h3>
              <p className="text-sm text-gray-600">Отправляем заказ напрямую покупателю с одного из 15 складов в 40+ регионов</p>
            </div>
          </div>
        </div>
      </div>

      {/* Преимущества */}
      <div className="py-12 bg-gray-50">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Почему селлеры выбирают Е1
          </h2>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Доставка в 40+ регионов</h3>
                <p className="text-sm text-gray-600">Широкая география поставок по всей России</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">API и IT-интеграции</h3>
                <p className="text-sm text-gray-600">Автоматизация передачи заказов и отслеживания статусов</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Качественная продукция</h3>
                <p className="text-sm text-gray-600">Минимум возвратов и рекламаций</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Готовый контент</h3>
                <p className="text-sm text-gray-600">Фото, описания и характеристики для карточек товаров</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Выгодные цены</h3>
                <p className="text-sm text-gray-600">Специальные условия для селлеров</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Поддержка менеджера</h3>
                <p className="text-sm text-gray-600">Персональный менеджер для решения любых вопросов</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Аналитика продаж</h3>
                <p className="text-sm text-gray-600">Знаем, какие позиции выгоднее продавать, и делимся аналитикой с партнёрами</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Совместные акции</h3>
                <p className="text-sm text-gray-600">Проводим совместные маркетинговые акции для увеличения продаж</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Форма заявки */}
      <div className="py-12 bg-gray-50" id="apply">
        <div className="container-custom">
          <div className="max-w-xl mx-auto">
            <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
              Хочу сотрудничать
            </h2>
            <p className="text-center text-gray-600 text-sm mb-6">
              Оставьте заявку, и мы расскажем подробнее об условиях работы
            </p>

            <div className="bg-white rounded-xl shadow-lg p-6">
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

                {/* Phone */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Телефон <span className="text-red-500">*</span>
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

                {/* Company Name */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Название компании
                  </label>
                  <input
                    type="text"
                    value={formData.company_name}
                    onChange={(e) => setFormData({ ...formData, company_name: e.target.value })}
                    placeholder="ООО или ИП (необязательно)"
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
                    placeholder="example@mail.ru"
                    className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                      errors.email ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.email && (
                    <p className="mt-1 text-xs text-red-500">{errors.email}</p>
                  )}
                </div>

                <label className="flex items-start gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={agreeToPrivacy}
                    onChange={(e) => setAgreeToPrivacy(e.target.checked)}
                    className="mt-0.5 w-4 h-4 text-[#62bb46] border-gray-300 rounded focus:ring-[#62bb46]"
                  />
                  <span className="text-xs text-gray-600">
                    Я согласен на{' '}
                    <a href="/privacy-policy" target="_blank" className="text-[#62bb46] underline hover:no-underline">
                      обработку персональных данных
                    </a>
                  </span>
                </label>

                <button
                  onClick={handleSubmit}
                  disabled={isSubmitting || !agreeToPrivacy}
                  className="w-full bg-[#62bb46] text-white py-3 rounded-lg font-bold hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                >
                  {isSubmitting ? 'Отправка...' : 'Отправить заявку'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="py-8 bg-[#3d4543]">
        <div className="container-custom text-center">
          <h2 className="text-lg md:text-xl font-bold mb-2" style={{ color: '#ffffff' }}>
            Готовы начать продавать с Е1?
          </h2>
          <p className="text-sm mb-4 max-w-lg mx-auto" style={{ color: '#ffffff' }}>
            Присоединяйтесь к нашей партнёрской программе для селлеров
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
