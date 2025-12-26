'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';

interface MeasurementModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function MeasurementModal({ isOpen, onClose }: MeasurementModalProps) {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    message: '',
  });
  const [agreedToPrivacy, setAgreedToPrivacy] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');
  const [errorMessage, setErrorMessage] = useState('');

  // Close on escape key
  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };

    if (isOpen) {
      document.addEventListener('keydown', handleEscape);
      document.body.style.overflow = 'hidden';
    }

    return () => {
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = '';
    };
  }, [isOpen, onClose]);

  const formatPhone = (value: string) => {
    const digits = value.replace(/\D/g, '');

    if (digits.length === 0) return '';

    let formatted = '+7';
    if (digits.length > 1) {
      const rest = digits.startsWith('7') || digits.startsWith('8') ? digits.slice(1) : digits;
      if (rest.length > 0) formatted += ' (' + rest.slice(0, 3);
      if (rest.length > 3) formatted += ') ' + rest.slice(3, 6);
      if (rest.length > 6) formatted += '-' + rest.slice(6, 8);
      if (rest.length > 8) formatted += '-' + rest.slice(8, 10);
    }

    return formatted;
  };

  const handlePhoneChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const formatted = formatPhone(e.target.value);
    setFormData({ ...formData, phone: formatted });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!agreedToPrivacy) return;

    setIsSubmitting(true);
    setSubmitStatus('idle');
    setErrorMessage('');

    try {
      const response = await fetch('/api/request-measurement', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: formData.name,
          phone: formData.phone,
          message: formData.message || undefined,
        }),
      });

      const result = await response.json();

      if (result.success) {
        setSubmitStatus('success');
        setFormData({ name: '', phone: '', message: '' });
        setAgreedToPrivacy(false);
      } else {
        setSubmitStatus('error');
        setErrorMessage(result.message || 'Произошла ошибка при отправке заявки');
      }
    } catch {
      setSubmitStatus('error');
      setErrorMessage('Не удалось отправить заявку. Пожалуйста, попробуйте позже.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleClose = () => {
    setSubmitStatus('idle');
    setErrorMessage('');
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
      {/* Overlay */}
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onClick={handleClose}
      />

      {/* Modal */}
      <div className="relative bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        {/* Close button */}
        <button
          onClick={handleClose}
          className="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors z-10"
          aria-label="Закрыть"
        >
          <svg className="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <div className="p-6 pt-8">
          {/* Header */}
          <div className="text-center mb-6">
            <div className="w-16 h-16 bg-[#62bb46]/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h2 className="text-2xl font-bold text-[#3d4543]">Вызвать замерщика</h2>
            <p className="text-gray-500 mt-2">
              Оставьте заявку и наш специалист свяжется с вами в ближайшее время
            </p>
          </div>

          {submitStatus === 'success' ? (
            <div className="text-center py-8">
              <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-[#3d4543] mb-2">Заявка отправлена!</h3>
              <p className="text-gray-500 mb-6">
                Мы свяжемся с вами в ближайшее время для уточнения деталей
              </p>
              <button
                onClick={handleClose}
                className="px-8 py-3 bg-[#62bb46] text-white font-bold rounded-lg hover:bg-[#55a83d] transition-colors"
              >
                Отлично!
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {/* Name field */}
              <div>
                <label htmlFor="modal-name" className="block text-sm font-medium text-[#3d4543] mb-2">
                  Имя <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  id="modal-name"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] focus:ring-1 focus:ring-[#62bb46] transition-colors"
                  placeholder="Введите ваше имя"
                  required
                />
              </div>

              {/* Phone field */}
              <div>
                <label htmlFor="modal-phone" className="block text-sm font-medium text-[#3d4543] mb-2">
                  Телефон <span className="text-red-500">*</span>
                </label>
                <input
                  type="tel"
                  id="modal-phone"
                  value={formData.phone}
                  onChange={handlePhoneChange}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] focus:ring-1 focus:ring-[#62bb46] transition-colors"
                  placeholder="+7 (___) ___-__-__"
                  required
                />
              </div>

              {/* Message field */}
              <div>
                <label htmlFor="modal-message" className="block text-sm font-medium text-[#3d4543] mb-2">
                  Сообщение <span className="text-gray-400 font-normal">(необязательно)</span>
                </label>
                <textarea
                  id="modal-message"
                  value={formData.message}
                  onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                  className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] focus:ring-1 focus:ring-[#62bb46] transition-colors resize-none"
                  placeholder="Дополнительная информация..."
                  rows={3}
                />
              </div>

              {/* Privacy checkbox */}
              <div className="flex items-start gap-3">
                <input
                  type="checkbox"
                  id="modal-privacy"
                  checked={agreedToPrivacy}
                  onChange={(e) => setAgreedToPrivacy(e.target.checked)}
                  className="mt-1 w-5 h-5 text-[#62bb46] border-gray-300 rounded focus:ring-[#62bb46] cursor-pointer"
                />
                <label htmlFor="modal-privacy" className="text-sm text-gray-600 cursor-pointer">
                  Я согласен на{' '}
                  <Link
                    href="/privacy-policy"
                    className="text-[#62bb46] hover:underline"
                    target="_blank"
                  >
                    обработку персональных данных
                  </Link>
                </label>
              </div>

              {/* Error message */}
              {submitStatus === 'error' && (
                <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                  <p className="text-red-600 text-sm">{errorMessage}</p>
                </div>
              )}

              {/* Buttons */}
              <div className="flex gap-3 pt-2">
                <button
                  type="button"
                  onClick={handleClose}
                  className="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Отмена
                </button>
                <button
                  type="submit"
                  disabled={!agreedToPrivacy || isSubmitting}
                  className="flex-1 px-4 py-3 bg-[#62bb46] text-white font-bold rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
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
                    'Отправить заявку'
                  )}
                </button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
