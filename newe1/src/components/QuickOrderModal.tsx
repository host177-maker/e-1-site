'use client';

import { useState } from 'react';

// Phone mask formatter
const formatPhone = (value: string): string => {
  const digits = value.replace(/\D/g, '');

  if (digits.length === 0) return '';

  let formatted = '+7';
  if (digits.length > 1) {
    formatted += ' (' + digits.substring(1, 4);
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

interface QuickOrderModalProps {
  isOpen: boolean;
  onClose: () => void;
  productName: string;
  productUrl: string;
  selectedParams: {
    width: number | null;
    height: number | null;
    depth: number | null;
    bodyColor: string | null;
    profileColor: string | null;
    filling: string | null;
  };
}

export default function QuickOrderModal({
  isOpen,
  onClose,
  productName,
  productUrl,
  selectedParams,
}: QuickOrderModalProps) {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    comment: '',
    agreeToPolicy: false,
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');
  const [errorMessage, setErrorMessage] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!formData.name.trim()) {
      setErrorMessage('Введите ФИО');
      return;
    }
    if (!formData.phone.trim()) {
      setErrorMessage('Введите телефон');
      return;
    }
    if (!formData.agreeToPolicy) {
      setErrorMessage('Необходимо согласие на обработку персональных данных');
      return;
    }

    setIsSubmitting(true);
    setErrorMessage('');

    try {
      const response = await fetch('/api/quick-order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...formData,
          productName,
          productUrl,
          selectedParams,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setSubmitStatus('success');
        // Reset form
        setFormData({
          name: '',
          phone: '',
          email: '',
          comment: '',
          agreeToPolicy: false,
        });
      } else {
        setSubmitStatus('error');
        setErrorMessage(data.error || 'Произошла ошибка при отправке заявки');
      }
    } catch {
      setSubmitStatus('error');
      setErrorMessage('Произошла ошибка при отправке заявки');
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
    <div
      className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
      onClick={handleClose}
    >
      <div
        className="bg-white rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="sticky top-0 bg-white border-b p-4 flex items-center justify-between">
          <h2 className="text-lg font-bold text-gray-900">Купить в 1 клик</h2>
          <button
            onClick={handleClose}
            className="p-1 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div className="p-4">
          {submitStatus === 'success' ? (
            <div className="text-center py-8">
              <div className="w-16 h-16 bg-[#62bb46]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Заявка отправлена!</h3>
              <p className="text-gray-600 mb-6">Наш менеджер свяжется с вами в ближайшее время</p>
              <button
                onClick={handleClose}
                className="px-6 py-2 bg-[#62bb46] text-white font-medium rounded-lg hover:bg-[#55a83d] transition-colors"
              >
                Закрыть
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {/* Product info */}
              <div className="bg-gray-50 rounded-lg p-3 text-sm">
                <div className="font-medium text-gray-900 mb-1">{productName}</div>
                <div className="text-gray-600 text-xs">
                  {selectedParams.width && selectedParams.height && selectedParams.depth && (
                    <span>{selectedParams.width}×{selectedParams.height}×{selectedParams.depth} мм</span>
                  )}
                  {selectedParams.bodyColor && <span> • {selectedParams.bodyColor}</span>}
                  {selectedParams.filling && <span> • {selectedParams.filling}</span>}
                </div>
              </div>

              {/* Name field */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  ФИО <span className="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] transition-colors"
                  placeholder="Иванов Иван Иванович"
                />
              </div>

              {/* Phone field */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Телефон <span className="text-red-500">*</span>
                </label>
                <input
                  type="tel"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: formatPhone(e.target.value) })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] transition-colors"
                  placeholder="+7 (999) 123-45-67"
                />
              </div>

              {/* Email field (optional) */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  E-mail <span className="text-gray-400">(необязательно)</span>
                </label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] transition-colors"
                  placeholder="email@example.com"
                />
              </div>

              {/* Comment field (optional) */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Комментарий к заказу <span className="text-gray-400">(необязательно)</span>
                </label>
                <textarea
                  value={formData.comment}
                  onChange={(e) => setFormData({ ...formData, comment: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#62bb46] transition-colors resize-none"
                  rows={3}
                  placeholder="Ваши пожелания..."
                />
              </div>

              {/* Privacy policy checkbox */}
              <div className="flex items-start gap-2">
                <input
                  type="checkbox"
                  id="agreeToPolicy"
                  checked={formData.agreeToPolicy}
                  onChange={(e) => setFormData({ ...formData, agreeToPolicy: e.target.checked })}
                  className="mt-1 w-4 h-4 rounded border-gray-300 text-[#62bb46] focus:ring-[#62bb46]"
                />
                <label htmlFor="agreeToPolicy" className="text-sm text-gray-600">
                  Я согласен на{' '}
                  <a
                    href="/privacy-policy"
                    target="_blank"
                    className="text-[#62bb46] hover:underline"
                  >
                    обработку персональных данных
                  </a>
                </label>
              </div>

              {/* Error message */}
              {errorMessage && (
                <div className="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg p-3">
                  {errorMessage}
                </div>
              )}

              {/* Submit button */}
              <button
                type="submit"
                disabled={isSubmitting}
                className="w-full py-3 bg-[#62bb46] text-white font-bold rounded-lg hover:bg-[#55a83d] transition-colors disabled:opacity-50"
              >
                {isSubmitting ? (
                  <span className="flex items-center justify-center gap-2">
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                    Отправка...
                  </span>
                ) : (
                  'Отправить заявку'
                )}
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
