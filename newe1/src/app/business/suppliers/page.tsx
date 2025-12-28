'use client';

import { useState, useRef, useEffect } from 'react';
import Image from 'next/image';

interface FormData {
  topic: string;
  full_name: string;
  phone: string;
  company_name: string;
  proposal: string;
}

export default function SuppliersPage() {
  const [formData, setFormData] = useState<FormData>({
    topic: 'materials',
    full_name: '',
    phone: '',
    company_name: '',
    proposal: '',
  });
  const [errors, setErrors] = useState<Partial<FormData>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [agreeToPrivacy, setAgreeToPrivacy] = useState(false);
  const [file, setFile] = useState<File | null>(null);
  const [fileError, setFileError] = useState('');
  const fileInputRef = useRef<HTMLInputElement>(null);

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

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0];
    setFileError('');

    if (selectedFile) {
      // Check file size (10MB max)
      if (selectedFile.size > 10 * 1024 * 1024) {
        setFileError('Файл слишком большой. Максимальный размер — 10 МБ');
        setFile(null);
        return;
      }
      setFile(selectedFile);
    }
  };

  const removeFile = () => {
    setFile(null);
    setFileError('');
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Partial<FormData> = {};

    if (!formData.full_name.trim()) {
      newErrors.full_name = 'Введите ФИО';
    }
    if (!formData.phone || formData.phone.replace(/\D/g, '').length < 11) {
      newErrors.phone = 'Введите корректный номер телефона';
    }
    if (!formData.company_name.trim()) {
      newErrors.company_name = 'Введите название компании';
    }
    if (!formData.proposal.trim()) {
      newErrors.proposal = 'Опишите ваше предложение';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;

    setIsSubmitting(true);
    try {
      const submitData = new FormData();
      submitData.append('topic', formData.topic);
      submitData.append('full_name', formData.full_name);
      submitData.append('phone', formData.phone);
      submitData.append('company_name', formData.company_name);
      submitData.append('proposal', formData.proposal);
      if (file) {
        submitData.append('file', file);
      }

      const response = await fetch('/api/suppliers', {
        method: 'POST',
        body: submitData,
      });

      const data = await response.json();

      if (data.success) {
        setIsSubmitted(true);
      } else {
        setErrors({ proposal: data.error || 'Ошибка отправки. Попробуйте позже.' });
      }
    } catch {
      setErrors({ proposal: 'Ошибка сервера. Попробуйте позже.' });
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
              Наш отдел закупок рассмотрит ваше предложение и свяжется с вами.
            </p>
          </div>
        </div>

        <div className="container-custom py-10">
          <div className="max-w-xl mx-auto bg-white rounded-xl shadow-lg p-6 text-center">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Что дальше?</h2>
            <ul className="text-left space-y-3 mb-6">
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">1</span>
                <span className="text-gray-700 text-sm">Мы изучим ваше предложение</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">2</span>
                <span className="text-gray-700 text-sm">Свяжемся с вами для обсуждения условий</span>
              </li>
              <li className="flex items-start gap-3">
                <span className="w-7 h-7 bg-[#62bb46] text-white rounded-full flex items-center justify-center shrink-0 text-sm font-bold">3</span>
                <span className="text-gray-700 text-sm">При положительном решении заключим договор о сотрудничестве</span>
              </li>
            </ul>
            <a
              href="/business/suppliers"
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
            src="/images/suppliers.jpg"
            alt="Поставщикам"
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
              Поставщикам
            </h1>
            <p className="text-xl md:text-2xl font-medium mb-4 opacity-95">
              Мы рады долгосрочному сотрудничеству
            </p>
            <p className="text-base md:text-lg opacity-90">
              Партнёрство, соблюдение договорённостей, предсказуемые объёмы —
              основа нашего взаимодействия с поставщиками.
            </p>
          </div>
        </div>
      </div>

      {/* Кого ищем */}
      <div className="py-12 bg-white">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-2">
            Мы открыты к сотрудничеству
          </h2>
          <p className="text-center text-gray-600 mb-8">Ищем надёжных партнёров для развития</p>

          <div className="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div className="bg-gray-50 rounded-xl p-6 text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">Поставщики материалов</h3>
              <p className="text-sm text-gray-600">ЛДСП, фурнитура, комплектующие для производства мебели</p>
            </div>

            <div className="bg-gray-50 rounded-xl p-6 text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">Региональная логистика</h3>
              <p className="text-sm text-gray-600">Транспортные компании для межрегиональных перевозок</p>
            </div>

            <div className="bg-gray-50 rounded-xl p-6 text-center">
              <div className="w-14 h-14 bg-[#62bb46] text-white rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <h3 className="font-bold text-gray-900 mb-2">Доставка «последней мили»</h3>
              <p className="text-sm text-gray-600">Курьерские службы для доставки до клиента</p>
            </div>
          </div>
        </div>
      </div>

      {/* Преимущества работы с нами */}
      <div className="py-12 bg-gray-50">
        <div className="container-custom">
          <h2 className="text-xl md:text-2xl font-bold text-center text-gray-900 mb-8">
            Почему с нами выгодно работать
          </h2>

          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Надёжность</h3>
                <p className="text-sm text-gray-600">Соблюдаем все договорённости</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Долгосрочность</h3>
                <p className="text-sm text-gray-600">Строим партнёрство на годы</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Стабильные объёмы</h3>
                <p className="text-sm text-gray-600">Предсказуемые заказы</p>
              </div>
            </div>

            <div className="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
              <div className="w-10 h-10 bg-[#62bb46]/10 rounded-lg flex items-center justify-center shrink-0">
                <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <h3 className="font-bold text-gray-900 mb-1">Своевременная оплата</h3>
                <p className="text-sm text-gray-600">Платим вовремя</p>
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
              Оставить заявку
            </h2>
            <p className="text-center text-gray-600 text-sm mb-6">
              Расскажите о вашем предложении, и мы свяжемся с вами
            </p>

            <div className="bg-gray-50 rounded-xl shadow-lg p-6">
              <div className="space-y-4">
                {/* Topic */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Тема заявки <span className="text-red-500">*</span>
                  </label>
                  <select
                    value={formData.topic}
                    onChange={(e) => setFormData({ ...formData, topic: e.target.value })}
                    className="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm bg-white"
                  >
                    <option value="materials">Поставка материалов</option>
                    <option value="logistics">Логистика</option>
                  </select>
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
                    Название компании <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={formData.company_name}
                    onChange={(e) => setFormData({ ...formData, company_name: e.target.value })}
                    placeholder="ООО или ИП"
                    className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm ${
                      errors.company_name ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.company_name && (
                    <p className="mt-1 text-xs text-red-500">{errors.company_name}</p>
                  )}
                </div>

                {/* Proposal */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Напишите ваше предложение <span className="text-red-500">*</span>
                  </label>
                  <textarea
                    value={formData.proposal}
                    onChange={(e) => setFormData({ ...formData, proposal: e.target.value })}
                    placeholder="Опишите ваше предложение, условия сотрудничества..."
                    rows={4}
                    className={`w-full px-3 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#62bb46] text-sm resize-none ${
                      errors.proposal ? 'border-red-500' : 'border-gray-300'
                    }`}
                  />
                  {errors.proposal && (
                    <p className="mt-1 text-xs text-red-500">{errors.proposal}</p>
                  )}
                </div>

                {/* File Upload */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Приложите коммерческое предложение
                  </label>
                  <div className="relative">
                    <input
                      type="file"
                      ref={fileInputRef}
                      onChange={handleFileChange}
                      accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                      className="hidden"
                      id="file-upload"
                    />
                    {!file ? (
                      <label
                        htmlFor="file-upload"
                        className="flex items-center justify-center gap-2 w-full px-3 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-[#62bb46] transition-colors"
                      >
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span className="text-sm text-gray-500">Выберите файл (не более 10 МБ)</span>
                      </label>
                    ) : (
                      <div className="flex items-center gap-2 px-3 py-2.5 bg-gray-100 rounded-lg">
                        <svg className="w-5 h-5 text-[#62bb46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span className="text-sm text-gray-700 flex-1 truncate">{file.name}</span>
                        <button
                          type="button"
                          onClick={removeFile}
                          className="text-gray-400 hover:text-red-500 transition-colors"
                        >
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    )}
                  </div>
                  {fileError && (
                    <p className="mt-1 text-xs text-red-500">{fileError}</p>
                  )}
                  <p className="mt-1 text-xs text-gray-400">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG</p>
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
            Станьте нашим партнёром
          </h2>
          <p className="text-sm mb-4 max-w-lg mx-auto" style={{ color: '#ffffff' }}>
            Мы ценим надёжность и долгосрочное сотрудничество
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
