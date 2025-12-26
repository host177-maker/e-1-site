'use client';

import { useEffect, useState, useRef } from 'react';
import Image from 'next/image';

interface Review {
  id: number;
  name: string;
  review_text: string;
  company_response: string | null;
  rating: number | null;
  photos: string[] | null;
  created_at: string;
}

interface ReviewsData {
  data: Review[];
  pagination: {
    total: number;
    limit: number;
    offset: number;
    hasMore: boolean;
  };
  stats: {
    averageRating: number | null;
    totalRated: number;
  };
}

function StarRating({ rating, size = 'md' }: { rating: number; size?: 'sm' | 'md' | 'lg' }) {
  const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
  };

  return (
    <div className="flex gap-0.5">
      {[1, 2, 3, 4, 5].map((star) => (
        <svg
          key={star}
          className={`${sizeClasses[size]} ${star <= rating ? 'text-yellow-400' : 'text-gray-300'}`}
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      ))}
    </div>
  );
}

function StarRatingInput({ rating, onChange }: { rating: number; onChange: (rating: number) => void }) {
  const [hoverRating, setHoverRating] = useState(0);

  return (
    <div className="flex gap-1">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          onClick={() => onChange(star)}
          onMouseEnter={() => setHoverRating(star)}
          onMouseLeave={() => setHoverRating(0)}
          className="focus:outline-none transition-transform hover:scale-110"
        >
          <svg
            className={`w-8 h-8 ${
              star <= (hoverRating || rating) ? 'text-yellow-400' : 'text-gray-300'
            } transition-colors`}
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </button>
      ))}
    </div>
  );
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function ReviewForm({ onSuccess, onClose }: { onSuccess: () => void; onClose: () => void }) {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    order_number: '',
    review_text: '',
    rating: 0,
  });
  const [photos, setPhotos] = useState<File[]>([]);
  const [photosPreviews, setPhotosPreviews] = useState<string[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handlePhotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    if (photos.length + files.length > 5) {
      setMessage({ type: 'error', text: 'Можно загрузить не более 5 фотографий' });
      return;
    }

    const newPhotos = [...photos, ...files].slice(0, 5);
    setPhotos(newPhotos);

    // Create previews
    const previews = newPhotos.map((file) => URL.createObjectURL(file));
    setPhotosPreviews(previews);
  };

  const removePhoto = (index: number) => {
    const newPhotos = photos.filter((_, i) => i !== index);
    setPhotos(newPhotos);

    URL.revokeObjectURL(photosPreviews[index]);
    const newPreviews = photosPreviews.filter((_, i) => i !== index);
    setPhotosPreviews(newPreviews);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setMessage(null);

    if (formData.rating === 0) {
      setMessage({ type: 'error', text: 'Пожалуйста, поставьте оценку' });
      return;
    }

    if (!formData.order_number.trim()) {
      setMessage({ type: 'error', text: 'Пожалуйста, укажите номер заказа' });
      return;
    }

    setIsSubmitting(true);

    try {
      let uploadedPaths: string[] = [];

      // Upload photos if any
      if (photos.length > 0) {
        const formDataUpload = new FormData();
        photos.forEach((photo) => formDataUpload.append('photos', photo));

        const uploadRes = await fetch('/api/reviews/upload', {
          method: 'POST',
          body: formDataUpload,
        });

        const uploadData = await uploadRes.json();
        if (!uploadData.success) {
          throw new Error(uploadData.message);
        }
        uploadedPaths = uploadData.paths;
      }

      // Submit review
      const res = await fetch('/api/reviews', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          ...formData,
          photos: uploadedPaths,
        }),
      });

      const data = await res.json();

      if (data.success) {
        setMessage({ type: 'success', text: data.message });
        setFormData({ name: '', phone: '', order_number: '', review_text: '', rating: 0 });
        setPhotos([]);
        setPhotosPreviews([]);
        onSuccess();
        // Close form after successful submission
        setTimeout(() => {
          onClose();
        }, 2000);
      } else {
        throw new Error(data.message);
      }
    } catch (error) {
      setMessage({
        type: 'error',
        text: error instanceof Error ? error.message : 'Произошла ошибка',
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="bg-[#f5f5f5] rounded-xl p-6 md:p-8">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-2xl font-bold">Оставить отзыв</h2>
        <button
          onClick={onClose}
          className="text-gray-500 hover:text-gray-700 transition-colors"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      {message && (
        <div
          className={`p-4 rounded-lg mb-6 ${
            message.type === 'success'
              ? 'bg-green-100 text-green-800 border border-green-200'
              : 'bg-red-100 text-red-800 border border-red-200'
          }`}
        >
          {message.text}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              ФИО <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              required
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
              placeholder="Иванов Иван Иванович"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Телефон <span className="text-red-500">*</span>
            </label>
            <input
              type="tel"
              required
              value={formData.phone}
              onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
              placeholder="+7 (999) 999-99-99"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Номер заказа <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            required
            value={formData.order_number}
            onChange={(e) => setFormData({ ...formData, order_number: e.target.value })}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent"
            placeholder="Введите номер заказа"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Ваша оценка <span className="text-red-500">*</span>
          </label>
          <StarRatingInput rating={formData.rating} onChange={(rating) => setFormData({ ...formData, rating })} />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Ваш отзыв <span className="text-red-500">*</span>
          </label>
          <textarea
            required
            rows={4}
            value={formData.review_text}
            onChange={(e) => setFormData({ ...formData, review_text: e.target.value })}
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7cb342] focus:border-transparent resize-none"
            placeholder="Расскажите о вашем опыте..."
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Фотографии (до 5 шт.)
          </label>

          <div className="flex flex-wrap gap-3 mb-3">
            {photosPreviews.map((preview, index) => (
              <div key={index} className="relative w-20 h-20">
                <Image
                  src={preview}
                  alt={`Preview ${index + 1}`}
                  fill
                  className="object-cover rounded-lg"
                />
                <button
                  type="button"
                  onClick={() => removePhoto(index)}
                  className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-sm hover:bg-red-600"
                >
                  &times;
                </button>
              </div>
            ))}

            {photos.length < 5 && (
              <button
                type="button"
                onClick={() => fileInputRef.current?.click()}
                className="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-400 hover:border-[#7cb342] hover:text-[#7cb342] transition-colors"
              >
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
              </button>
            )}
          </div>

          <input
            ref={fileInputRef}
            type="file"
            accept="image/jpeg,image/png,image/webp"
            multiple
            onChange={handlePhotoChange}
            className="hidden"
          />

          <p className="text-sm text-gray-500">JPG, PNG или WebP, до 5 МБ каждый</p>
        </div>

        <div className="flex gap-4">
          <button
            type="submit"
            disabled={isSubmitting}
            className="flex-1 md:flex-none px-8 py-3 bg-[#7cb342] text-white font-medium rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isSubmitting ? 'Отправка...' : 'Отправить отзыв'}
          </button>
          <button
            type="button"
            onClick={onClose}
            className="px-8 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
          >
            Отмена
          </button>
        </div>
      </form>
    </div>
  );
}

function ReviewCard({ review }: { review: Review }) {
  const [showFullText, setShowFullText] = useState(false);
  const [lightboxPhoto, setLightboxPhoto] = useState<string | null>(null);

  const isLongText = review.review_text.length > 300;
  const displayText = showFullText ? review.review_text : review.review_text.slice(0, 300);

  return (
    <div className="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between mb-4">
        <div>
          <div className="font-bold text-lg text-[#3d4543]">{review.name}</div>
          <div className="text-gray-500 text-sm">{formatDate(review.created_at)}</div>
        </div>
        {review.rating && <StarRating rating={review.rating} />}
      </div>

      <p className="text-gray-700 mb-4">
        {displayText}
        {isLongText && !showFullText && '...'}
        {isLongText && (
          <button
            onClick={() => setShowFullText(!showFullText)}
            className="ml-2 text-[#7cb342] hover:underline"
          >
            {showFullText ? 'Свернуть' : 'Читать полностью'}
          </button>
        )}
      </p>

      {/* Photos */}
      {review.photos && review.photos.length > 0 && (
        <div className="flex flex-wrap gap-2 mb-4">
          {review.photos.map((photo, index) => (
            <button
              key={index}
              onClick={() => setLightboxPhoto(photo)}
              className="relative w-16 h-16 rounded-lg overflow-hidden hover:opacity-80 transition-opacity"
            >
              <Image src={photo} alt={`Фото ${index + 1}`} fill className="object-cover" />
            </button>
          ))}
        </div>
      )}

      {/* Company response */}
      {review.company_response && (
        <div className="mt-4 pt-4 border-t border-gray-100">
          <div className="flex items-center gap-2 mb-2">
            <div className="w-8 h-8 bg-[#7cb342] rounded-full flex items-center justify-center">
              <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fillRule="evenodd"
                  d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                  clipRule="evenodd"
                />
              </svg>
            </div>
            <span className="font-medium text-[#3d4543]">Ответ компании</span>
          </div>
          <p className="text-gray-600 text-sm pl-10">{review.company_response}</p>
        </div>
      )}

      {/* Lightbox */}
      {lightboxPhoto && (
        <div
          className="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4"
          onClick={() => setLightboxPhoto(null)}
        >
          <div className="relative max-w-4xl max-h-[90vh]">
            <Image
              src={lightboxPhoto}
              alt="Увеличенное фото"
              width={800}
              height={600}
              className="object-contain max-h-[90vh]"
            />
            <button
              onClick={() => setLightboxPhoto(null)}
              className="absolute top-4 right-4 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/40 transition-colors"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default function ReviewsPage() {
  const [reviewsData, setReviewsData] = useState<ReviewsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [showForm, setShowForm] = useState(false);

  const fetchReviews = async (offset = 0, append = false) => {
    try {
      const res = await fetch(`/api/reviews?limit=10&offset=${offset}`);
      const data = await res.json();

      if (data.success) {
        if (append && reviewsData) {
          setReviewsData({
            ...data,
            data: [...reviewsData.data, ...data.data],
          });
        } else {
          setReviewsData(data);
        }
      }
    } catch (error) {
      console.error('Error fetching reviews:', error);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };

  useEffect(() => {
    fetchReviews();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const loadMore = () => {
    if (!reviewsData || loadingMore) return;
    setLoadingMore(true);
    fetchReviews(reviewsData.data.length, true);
  };

  return (
    <div className="bg-white">
      {/* Hero section */}
      <div className="bg-[#f5f5f5] py-8">
        <div className="container-custom">
          <h1 className="text-3xl md:text-4xl font-bold text-[#3d4543]">Отзывы клиентов</h1>
        </div>
      </div>

      <div className="container-custom py-8">
        {/* Review Button / Form */}
        <div className="mb-12">
          {showForm ? (
            <ReviewForm
              onSuccess={() => fetchReviews()}
              onClose={() => setShowForm(false)}
            />
          ) : (
            <div className="bg-[#f5f5f5] rounded-xl p-8 text-center">
              <h3 className="text-2xl font-bold mb-4">Поделитесь своим мнением</h3>
              <p className="text-gray-600 mb-6 max-w-xl mx-auto">
                Мы ценим мнение каждого клиента. Расскажите о своём опыте сотрудничества с нами.
              </p>
              <button
                onClick={() => setShowForm(true)}
                className="px-8 py-3 bg-[#7cb342] text-white font-medium rounded-lg hover:bg-[#689f38] transition-colors"
              >
                Оставить отзыв
              </button>
            </div>
          )}
        </div>

        {/* Reviews List */}
        <div className="space-y-6">
          <h2 className="text-2xl font-bold text-[#3d4543]">
            Все отзывы
            {reviewsData && (
              <span className="text-gray-400 font-normal ml-2">({reviewsData.pagination.total})</span>
            )}
          </h2>

          {loading ? (
            <div className="flex justify-center py-12">
              <div className="w-8 h-8 border-4 border-[#7cb342] border-t-transparent rounded-full animate-spin" />
            </div>
          ) : reviewsData && reviewsData.data.length > 0 ? (
            <>
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {reviewsData.data.map((review) => (
                  <ReviewCard key={review.id} review={review} />
                ))}
              </div>

              {reviewsData.pagination.hasMore && (
                <div className="text-center pt-6">
                  <button
                    onClick={loadMore}
                    disabled={loadingMore}
                    className="px-8 py-3 bg-[#7cb342] text-white font-medium rounded-lg hover:bg-[#689f38] transition-colors disabled:opacity-50"
                  >
                    {loadingMore ? 'Загрузка...' : 'Показать ещё'}
                  </button>
                </div>
              )}
            </>
          ) : (
            <div className="text-center py-12">
              <p className="text-gray-500 text-lg">Отзывов пока нет. Будьте первым!</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
