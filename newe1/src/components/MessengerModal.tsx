'use client';

import Image from 'next/image';

interface MessengerModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function MessengerModal({ isOpen, onClose }: MessengerModalProps) {
  if (!isOpen) return null;

  const messengers = [
    {
      name: 'Max',
      href: 'https://max.ru/79384222111',
      image: '/images/Max.jpg',
    },
    {
      name: 'WhatsApp',
      href: 'https://wa.me/79384222111',
      image: '/images/Whatsup.jpg',
    },
    {
      name: 'Telegram',
      href: 'https://t.me/+79384222111',
      image: '/images/Telegram.jpg',
    },
  ];

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
      {/* Overlay */}
      <div
        className="absolute inset-0 bg-black/50 backdrop-blur-sm"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 animate-in fade-in zoom-in duration-200">
        {/* Close button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors"
          aria-label="Закрыть"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        {/* Title */}
        <h2 className="text-2xl md:text-3xl font-bold text-center text-[#3d4543] mb-8">
          Мы на связи в мессенджерах
        </h2>

        {/* Messenger options */}
        <div className="bg-gray-50 rounded-2xl p-6">
          <div className="flex justify-center gap-6 md:gap-10">
            {messengers.map((messenger) => (
              <a
                key={messenger.name}
                href={messenger.href}
                target="_blank"
                rel="noopener noreferrer"
                className="flex flex-col items-center gap-3 group"
              >
                <div className="w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden shadow-lg group-hover:scale-110 transition-transform duration-200">
                  <Image
                    src={messenger.image}
                    alt={messenger.name}
                    width={96}
                    height={96}
                    className="w-full h-full object-cover"
                  />
                </div>
                <span className="text-sm md:text-base font-semibold text-[#3d4543]">
                  {messenger.name}
                </span>
              </a>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
