'use client';

import { useState, useCallback } from 'react';
import { useCart } from '@/context/CartContext';
import { useCity } from '@/context/CityContext';
import Link from 'next/link';
import Image from 'next/image';
import DeliveryOptions from '@/components/DeliveryOptions';

const PLACEHOLDER_IMAGE = '/images/placeholder-product.svg';

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

interface DeliveryData {
  type: 'delivery' | 'pickup';
  address?: string;
  liftType: 'none' | 'stairs' | 'elevator';
  floor?: number;
  deliveryCost: number;
  liftCost: number;
}

export default function CartPage() {
  const { items, removeFromCart, updateQuantity, toggleAssembly, clearCart, totalPrice, totalWithAssembly } = useCart();
  const { city } = useCity();

  // Order step: 'cart' -> 'delivery' -> 'checkout'
  const [orderStep, setOrderStep] = useState<'cart' | 'delivery' | 'checkout'>('cart');

  // Delivery data
  const [deliveryData, setDeliveryData] = useState<DeliveryData>({
    type: 'delivery',
    liftType: 'none',
    deliveryCost: 0,
    liftCost: 0,
  });

  // Checkout form state
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    promoCode: '',
    comment: '',
    agreeToPrivacy: false,
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');
  const [errorMessage, setErrorMessage] = useState('');

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target;

    if (name === 'phone') {
      // Apply phone mask
      const formatted = formatPhone(value);
      setFormData(prev => ({ ...prev, phone: formatted }));
      return;
    }

    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (e.target as HTMLInputElement).checked : value,
    }));
  };

  // Handle delivery options change
  const handleDeliveryChange = useCallback((data: DeliveryData) => {
    setDeliveryData(data);
  }, []);

  // Calculate total with delivery
  const totalWithDelivery = totalWithAssembly + deliveryData.deliveryCost + deliveryData.liftCost;

  const handleSubmitOrder = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!formData.name || !formData.phone || !formData.agreeToPrivacy) {
      setErrorMessage('Заполните обязательные поля');
      return;
    }

    setIsSubmitting(true);
    setErrorMessage('');

    try {
      const response = await fetch('/api/cart-order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          customerName: formData.name,
          customerPhone: formData.phone,
          customerEmail: formData.email,
          promoCode: formData.promoCode,
          comment: formData.comment,
          city: city.name,
          delivery: {
            type: deliveryData.type,
            address: deliveryData.address,
            liftType: deliveryData.liftType,
            floor: deliveryData.floor,
            deliveryCost: deliveryData.deliveryCost,
            liftCost: deliveryData.liftCost,
          },
          items: items.map(item => ({
            name: item.name,
            slug: item.slug,
            price: item.price,
            quantity: item.quantity,
            width: item.width,
            height: item.height,
            depth: item.depth,
            bodyColor: item.bodyColor,
            profileColor: item.profileColor,
            filling: item.filling,
            includeAssembly: item.includeAssembly,
            assemblyPrice: item.assemblyPrice,
          })),
          totalPrice: totalWithDelivery,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setSubmitStatus('success');
        clearCart();
      } else {
        setSubmitStatus('error');
        setErrorMessage(data.error || 'Произошла ошибка');
      }
    } catch {
      setSubmitStatus('error');
      setErrorMessage('Ошибка соединения');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (submitStatus === 'success') {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="bg-white rounded-xl shadow-sm p-8 max-w-md text-center">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Заказ оформлен!</h2>
          <p className="text-gray-600 mb-6">Мы свяжемся с вами в ближайшее время</p>
          <Link
            href="/catalog"
            className="inline-block px-6 py-3 bg-[#62bb46] text-white font-medium rounded-lg hover:bg-[#55a83d] transition-colors"
          >
            Продолжить покупки
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 py-6">
        <div className="max-w-[1348px] mx-auto px-4">
          <h1 className="text-2xl md:text-3xl font-bold text-gray-900">Корзина</h1>
        </div>
      </div>

      <div className="max-w-[1348px] mx-auto px-4 py-8">
        {items.length === 0 ? (
          <div className="bg-white rounded-xl shadow-sm p-12 text-center">
            <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 className="text-xl font-medium text-gray-900 mb-2">Корзина пуста</h3>
            <p className="text-gray-500 mb-6">Добавьте товары из каталога</p>
            <Link
              href="/catalog"
              className="inline-block px-6 py-3 bg-[#62bb46] text-white font-medium rounded-lg hover:bg-[#55a83d] transition-colors"
            >
              Перейти в каталог
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Cart items */}
            <div className="lg:col-span-2 space-y-4">
              {/* Clear button */}
              <div className="flex justify-end">
                <button
                  onClick={clearCart}
                  className="text-sm text-red-500 hover:text-red-600 transition-colors"
                >
                  Очистить корзину
                </button>
              </div>

              {items.map((item) => (
                <div key={item.id} className="bg-white rounded-xl shadow-sm p-4">
                  <div className="flex gap-4">
                    {/* Image */}
                    <Link href={`/product/${item.slug}`} className="flex-shrink-0">
                      <div className="w-24 h-24 relative">
                        <Image
                          src={item.image || PLACEHOLDER_IMAGE}
                          alt={item.name}
                          fill
                          className="object-contain"
                          onError={(e) => {
                            const target = e.target as HTMLImageElement;
                            target.src = PLACEHOLDER_IMAGE;
                          }}
                        />
                      </div>
                    </Link>

                    {/* Info */}
                    <div className="flex-1 min-w-0">
                      <Link href={`/product/${item.slug}`}>
                        <h3 className="text-sm font-normal text-gray-900 hover:text-[#62bb46] transition-colors line-clamp-2 mb-2">
                          {item.name}
                        </h3>
                      </Link>

                      {/* Params */}
                      {(item.width || item.height || item.depth) && (
                        <p className="text-xs text-gray-500 mb-1">
                          Размер: {item.width}×{item.height}×{item.depth} мм
                        </p>
                      )}
                      {item.bodyColor && (
                        <p className="text-xs text-gray-500 mb-1">
                          Цвет корпуса: {item.bodyColor}
                        </p>
                      )}
                      {item.filling && (
                        <p className="text-xs text-gray-500 mb-1">
                          Наполнение: {item.filling}
                        </p>
                      )}

                      {/* Assembly toggle */}
                      <label className="flex items-center gap-2 mt-2 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={item.includeAssembly}
                          onChange={() => toggleAssembly(item.id)}
                          className="w-4 h-4 text-[#62bb46] rounded border-gray-300 focus:ring-[#62bb46]"
                        />
                        <span className="text-xs text-gray-600">
                          Сборка (+{item.assemblyPrice.toLocaleString('ru-RU')} ₽)
                        </span>
                      </label>
                    </div>

                    {/* Quantity and price */}
                    <div className="flex flex-col items-end justify-between">
                      {/* Remove button */}
                      <button
                        onClick={() => removeFromCart(item.id)}
                        className="p-1 text-gray-400 hover:text-red-500 transition-colors"
                        title="Удалить"
                      >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>

                      {/* Quantity controls */}
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity - 1)}
                          className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:border-[#62bb46] transition-colors"
                        >
                          -
                        </button>
                        <span className="w-8 text-center">{item.quantity}</span>
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity + 1)}
                          className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:border-[#62bb46] transition-colors"
                        >
                          +
                        </button>
                      </div>

                      {/* Price */}
                      <div className="text-right">
                        <div className="text-lg font-bold text-gray-900">
                          {((item.price + (item.includeAssembly ? item.assemblyPrice : 0)) * item.quantity).toLocaleString('ru-RU')} ₽
                        </div>
                        {item.oldPrice && (
                          <div className="text-sm text-gray-400 line-through">
                            {(item.oldPrice * item.quantity).toLocaleString('ru-RU')} ₽
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Order summary */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-xl shadow-sm p-6 sticky top-4">
                {/* Step indicator */}
                <div className="flex items-center justify-between mb-6">
                  <div className="flex items-center gap-2">
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${orderStep === 'cart' ? 'bg-[#62bb46] text-white' : 'bg-gray-100 text-gray-500'}`}>
                      1
                    </div>
                    <span className={`text-sm ${orderStep === 'cart' ? 'text-gray-900 font-medium' : 'text-gray-500'}`}>Корзина</span>
                  </div>
                  <div className="flex-1 h-px bg-gray-200 mx-2" />
                  <div className="flex items-center gap-2">
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${orderStep === 'delivery' ? 'bg-[#62bb46] text-white' : 'bg-gray-100 text-gray-500'}`}>
                      2
                    </div>
                    <span className={`text-sm ${orderStep === 'delivery' ? 'text-gray-900 font-medium' : 'text-gray-500'}`}>Доставка</span>
                  </div>
                  <div className="flex-1 h-px bg-gray-200 mx-2" />
                  <div className="flex items-center gap-2">
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${orderStep === 'checkout' ? 'bg-[#62bb46] text-white' : 'bg-gray-100 text-gray-500'}`}>
                      3
                    </div>
                    <span className={`text-sm ${orderStep === 'checkout' ? 'text-gray-900 font-medium' : 'text-gray-500'}`}>Данные</span>
                  </div>
                </div>

                {/* Step 1: Cart summary */}
                {orderStep === 'cart' && (
                  <>
                    <h3 className="text-lg font-bold text-gray-900 mb-4">Итого</h3>

                    <div className="space-y-2 mb-4">
                      <div className="flex justify-between text-gray-600">
                        <span>Товары ({items.reduce((sum, i) => sum + i.quantity, 0)} шт.)</span>
                        <span>{totalPrice.toLocaleString('ru-RU')} ₽</span>
                      </div>
                      {totalWithAssembly > totalPrice && (
                        <div className="flex justify-between text-gray-600">
                          <span>Сборка</span>
                          <span>{(totalWithAssembly - totalPrice).toLocaleString('ru-RU')} ₽</span>
                        </div>
                      )}
                    </div>

                    <div className="border-t border-gray-200 pt-4 mb-6">
                      <div className="flex justify-between text-xl font-bold text-gray-900">
                        <span>К оплате</span>
                        <span>{totalWithAssembly.toLocaleString('ru-RU')} ₽</span>
                      </div>
                    </div>

                    <button
                      onClick={() => setOrderStep('delivery')}
                      className="w-full py-3 bg-[#62bb46] text-white font-bold rounded-xl hover:bg-[#55a83d] transition-colors"
                    >
                      Оформить заказ
                    </button>
                  </>
                )}

                {/* Step 2: Delivery options */}
                {orderStep === 'delivery' && (
                  <>
                    <h3 className="text-lg font-bold text-gray-900 mb-4">Доставка</h3>

                    <div className="mb-4 p-3 bg-gray-50 rounded-lg">
                      <div className="text-sm text-gray-500">Ваш город:</div>
                      <div className="font-medium text-gray-900">{city.name}</div>
                    </div>

                    <DeliveryOptions
                      cityName={city.name}
                      onDeliveryChange={handleDeliveryChange}
                    />

                    <div className="border-t border-gray-200 mt-6 pt-4 space-y-2">
                      <div className="flex justify-between text-gray-600">
                        <span>Товары и сборка</span>
                        <span>{totalWithAssembly.toLocaleString('ru-RU')} ₽</span>
                      </div>
                      {(deliveryData.deliveryCost > 0 || deliveryData.liftCost > 0) && (
                        <div className="flex justify-between text-gray-600">
                          <span>Доставка и подъём</span>
                          <span>{(deliveryData.deliveryCost + deliveryData.liftCost).toLocaleString('ru-RU')} ₽</span>
                        </div>
                      )}
                      <div className="flex justify-between text-xl font-bold text-gray-900 pt-2">
                        <span>Итого</span>
                        <span>{totalWithDelivery.toLocaleString('ru-RU')} ₽</span>
                      </div>
                    </div>

                    <div className="flex gap-2 mt-6">
                      <button
                        type="button"
                        onClick={() => setOrderStep('cart')}
                        className="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
                      >
                        Назад
                      </button>
                      <button
                        type="button"
                        onClick={() => setOrderStep('checkout')}
                        className="flex-1 py-3 bg-[#62bb46] text-white font-bold rounded-xl hover:bg-[#55a83d] transition-colors"
                      >
                        Далее
                      </button>
                    </div>
                  </>
                )}

                {/* Step 3: Checkout form */}
                {orderStep === 'checkout' && (
                  <form onSubmit={handleSubmitOrder}>
                    <h3 className="text-lg font-bold text-gray-900 mb-4">Контактные данные</h3>

                    <div className="space-y-4">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          ФИО <span className="text-red-500">*</span>
                        </label>
                        <input
                          type="text"
                          name="name"
                          value={formData.name}
                          onChange={handleInputChange}
                          required
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent"
                          placeholder="Иванов Иван Иванович"
                        />
                      </div>

                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Телефон <span className="text-red-500">*</span>
                        </label>
                        <input
                          type="tel"
                          name="phone"
                          value={formData.phone}
                          onChange={handleInputChange}
                          required
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent"
                          placeholder="+7 (999) 123-45-67"
                        />
                      </div>

                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          E-mail
                        </label>
                        <input
                          type="email"
                          name="email"
                          value={formData.email}
                          onChange={handleInputChange}
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent"
                          placeholder="email@example.com"
                        />
                      </div>

                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Промокод
                        </label>
                        <input
                          type="text"
                          name="promoCode"
                          value={formData.promoCode}
                          onChange={(e) => setFormData(prev => ({ ...prev, promoCode: e.target.value.toUpperCase() }))}
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent"
                          placeholder="Введите промокод"
                        />
                      </div>

                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Комментарий к заказу
                        </label>
                        <textarea
                          name="comment"
                          value={formData.comment}
                          onChange={handleInputChange}
                          rows={3}
                          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#62bb46] focus:border-transparent resize-none"
                          placeholder="Дополнительные пожелания..."
                        />
                      </div>

                      <label className="flex items-start gap-2 cursor-pointer">
                        <input
                          type="checkbox"
                          name="agreeToPrivacy"
                          checked={formData.agreeToPrivacy}
                          onChange={handleInputChange}
                          required
                          className="w-4 h-4 mt-0.5 text-[#62bb46] rounded border-gray-300 focus:ring-[#62bb46]"
                        />
                        <span className="text-xs text-gray-600">
                          Я согласен на{' '}
                          <Link href="/privacy-policy" className="text-[#62bb46] hover:underline">
                            обработку персональных данных
                          </Link>
                        </span>
                      </label>
                    </div>

                    {errorMessage && (
                      <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                        {errorMessage}
                      </div>
                    )}

                    {/* Order summary */}
                    <div className="border-t border-gray-200 mt-4 pt-4 space-y-2">
                      <div className="flex justify-between text-sm text-gray-600">
                        <span>Товары</span>
                        <span>{totalPrice.toLocaleString('ru-RU')} ₽</span>
                      </div>
                      {totalWithAssembly > totalPrice && (
                        <div className="flex justify-between text-sm text-gray-600">
                          <span>Сборка</span>
                          <span>{(totalWithAssembly - totalPrice).toLocaleString('ru-RU')} ₽</span>
                        </div>
                      )}
                      <div className="flex justify-between text-sm text-gray-600">
                        <span>{deliveryData.type === 'pickup' ? 'Самовывоз' : 'Доставка'}</span>
                        <span>{deliveryData.deliveryCost.toLocaleString('ru-RU')} ₽</span>
                      </div>
                      {deliveryData.liftCost > 0 && (
                        <div className="flex justify-between text-sm text-gray-600">
                          <span>Подъём на этаж</span>
                          <span>{deliveryData.liftCost.toLocaleString('ru-RU')} ₽</span>
                        </div>
                      )}
                      <div className="flex justify-between text-xl font-bold text-gray-900 pt-2">
                        <span>К оплате</span>
                        <span>{totalWithDelivery.toLocaleString('ru-RU')} ₽</span>
                      </div>
                    </div>

                    <div className="flex gap-2 mt-4">
                      <button
                        type="button"
                        onClick={() => setOrderStep('delivery')}
                        className="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
                      >
                        Назад
                      </button>
                      <button
                        type="submit"
                        disabled={isSubmitting}
                        className="flex-1 py-3 bg-[#62bb46] text-white font-bold rounded-xl hover:bg-[#55a83d] transition-colors disabled:opacity-50"
                      >
                        {isSubmitting ? 'Отправка...' : 'Подтвердить'}
                      </button>
                    </div>
                  </form>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
