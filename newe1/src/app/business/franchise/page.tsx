'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';

interface FormData {
  full_name: string;
  city: string;
  phone: string;
  email: string;
}

export default function FranchisePage() {
  const [formData, setFormData] = useState<FormData>({
    full_name: '',
    city: '',
    phone: '',
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
    if (!formData.city.trim()) {
      newErrors.city = 'Введите город';
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
      const response = await fetch('/api/franchise', {
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
              Наш менеджер свяжется с вами в ближайшее время для обсуждения деталей сотрудничества.
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
                <span className="text-gray-700 text-sm">Проведём онлайн-встречу и расскажем о форматах работы</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">3</span>
                <span className="text-gray-700 text-sm">Поможем выбрать оптимальный формат для вашего города</span>
              </li>
            </ul>
            <a
              href="/business/franchise"
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
        {/* Background Image */}
        <div className="absolute inset-0 z-0">
          <Image
            src="/images/franchise.jpg"
            alt="Франчайзинг Е1"
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
              Е1 Франчайзинг
            </h1>
            <p className="text-xl md:text-2xl font-medium mb-4 opacity-95">
              Готовая модель прибыльного бизнеса в мебельной отрасли
            </p>
            <p className="text-base md:text-lg opacity-90 mb-6">
              Франчайзинг Е1 — это отработанная бизнес-модель с понятной экономикой,
              сильным продуктом и поддержкой производителя на всех этапах.
            </p>
            <p className="text-base md:text-lg opacity-90">
              Мы предлагаем партнёрам не просто бренд, а систему, которая позволяет
              зарабатывать с первого месяца работы и масштабироваться вместе с компанией.
            </p>
          </div>
        </div>
      </div>

      {/* Ключевые показатели */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
            Что вы получаете, открывая франшизу Е1
          </h2>
          <p className="text-center text-gray-600 mb-8">Прозрачные условия и реальные показатели</p>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <div className="bg-[#62bb46]/10 border border-[#62bb46]/30 rounded-xl p-6 text-center">
              <div className="text-4xl font-bold text-[#62bb46] mb-2">0%</div>
              <div className="font-bold text-gray-900 mb-1">Роялти</div>
              <p className="text-sm text-gray-600">Вы зарабатываете без ежемесячных отчислений с оборота</p>
            </div>

            <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
              <div className="text-4xl font-bold text-gray-900 mb-2">~15%</div>
              <div className="font-bold text-gray-900 mb-1">Рентабельность бизнеса</div>
              <p className="text-sm text-gray-600">Прозрачная экономика и контролируемая себестоимость</p>
            </div>

            <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
              <div className="text-4xl font-bold text-gray-900 mb-2">от 50 000 ₽</div>
              <div className="font-bold text-gray-900 mb-1">Чистая прибыль в месяц</div>
              <p className="text-sm text-gray-600">Реальные показатели действующих партнёров</p>
            </div>

            <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
              <div className="text-4xl font-bold text-gray-900 mb-2">от 36</div>
              <div className="font-bold text-gray-900 mb-1">Месяцев окупаемость</div>
              <p className="text-sm text-gray-600">Прогнозируемые сроки возврата инвестиций</p>
            </div>

            <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
              <div className="text-4xl font-bold text-gray-900 mb-2">2%</div>
              <div className="font-bold text-gray-900 mb-1">Маркетинговые отчисления</div>
              <p className="text-sm text-gray-600">Федеральная реклама, digital-поддержка и готовые инструменты продаж</p>
            </div>

            <div className="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
              <div className="text-4xl font-bold text-gray-900 mb-2">4</div>
              <div className="font-bold text-gray-900 mb-1">Формата работы</div>
              <p className="text-sm text-gray-600">От компактных точек продаж до полноформатных салонов</p>
            </div>
          </div>
        </div>
      </div>

      {/* Поддержка от Е1 */}
      <div className="py-12 bg-gray-50">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Поддержка от Е1
          </h2>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Обучение и запуск «под ключ»</h3>
                <p className="text-sm text-gray-600">Полное сопровождение от подписания договора до первой продажи</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">IT-инструменты и онлайн-сервисы</h3>
                <p className="text-sm text-gray-600">CRM, конструктор мебели, система учёта и аналитики</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Помощь в подборе помещения</h3>
                <p className="text-sm text-gray-600">Анализ локации, требования к площади и рекомендации</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Маркетинг и лидогенерация</h3>
                <p className="text-sm text-gray-600">Федеральные рекламные кампании и локальное продвижение</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Сопровождение после открытия</h3>
                <p className="text-sm text-gray-600">Постоянная поддержка, обновления и развитие бизнеса</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Аналитика и отчётность</h3>
                <p className="text-sm text-gray-600">Прозрачные метрики для контроля эффективности</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Форма заявки */}
      <div className="py-12 bg-white" id="apply">
        <div className="container-custom">
          <div className="max-w-xl mx-auto">
            <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
              Стать партнёром
            </h2>
            <p className="text-center text-gray-600 text-sm mb-6">
              Оставьте заявку, и мы расскажем подробнее о форматах и условиях
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
            Начните свой бизнес с Е1
          </h2>
          <p className="text-sm mb-4 max-w-lg mx-auto" style={{ color: '#ffffff' }}>
            Присоединяйтесь к сети партнёров и зарабатывайте вместе с нами
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
