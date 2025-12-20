'use client';

import { useState } from 'react';

export default function CTASection() {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    // Simulate form submission
    await new Promise((resolve) => setTimeout(resolve, 1000));

    setIsSubmitting(false);
    setIsSuccess(true);
    setFormData({ name: '', phone: '' });

    // Reset success message after 3 seconds
    setTimeout(() => setIsSuccess(false), 3000);
  };

  return (
    <section className="py-16 md:py-20 bg-gradient-to-r from-primary to-primary-dark text-white">
      <div className="container-custom">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          {/* Text content */}
          <div>
            <h2 className="text-3xl md:text-4xl font-bold mb-6 text-white">
              Закажите бесплатный замер
            </h2>
            <p className="text-lg text-white/80 mb-8">
              Наш специалист приедет к вам в удобное время, произведёт точные замеры и поможет подобрать оптимальное решение для вашего пространства.
            </p>

            <ul className="space-y-4">
              {[
                'Бесплатный выезд замерщика',
                'Консультация по выбору материалов',
                '3D визуализация проекта',
                'Расчёт стоимости на месте',
              ].map((item, index) => (
                <li key={index} className="flex items-center gap-3">
                  <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                  <span>{item}</span>
                </li>
              ))}
            </ul>
          </div>

          {/* Form */}
          <div className="bg-white rounded-2xl p-8 text-text-dark">
            <h3 className="text-2xl font-bold mb-6">Оставьте заявку</h3>

            {isSuccess ? (
              <div className="text-center py-8">
                <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <svg className="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <h4 className="text-xl font-bold mb-2">Заявка отправлена!</h4>
                <p className="text-text-medium">Мы свяжемся с вами в ближайшее время</p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label htmlFor="name" className="block text-sm font-medium mb-2">
                    Ваше имя
                  </label>
                  <input
                    type="text"
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full px-4 py-3 border border-border rounded-lg focus:outline-none focus:border-primary"
                    placeholder="Введите имя"
                    required
                  />
                </div>

                <div>
                  <label htmlFor="phone" className="block text-sm font-medium mb-2">
                    Телефон
                  </label>
                  <input
                    type="tel"
                    id="phone"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className="w-full px-4 py-3 border border-border rounded-lg focus:outline-none focus:border-primary"
                    placeholder="+7 (___) ___-__-__"
                    required
                  />
                </div>

                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                  {isSubmitting ? (
                    <>
                      <svg className="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Отправка...
                    </>
                  ) : (
                    'Заказать замер'
                  )}
                </button>

                <p className="text-xs text-text-muted text-center">
                  Нажимая кнопку, вы соглашаетесь с{' '}
                  <a href="/privacy" className="text-primary hover:underline">
                    политикой конфиденциальности
                  </a>
                </p>
              </form>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
