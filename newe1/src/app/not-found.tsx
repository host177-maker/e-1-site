import Link from 'next/link';

export default function NotFound() {
  return (
    <div className="min-h-[50vh] flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-6xl font-bold text-[#62bb46] mb-4">404</h1>
        <h2 className="text-2xl font-bold text-[#3d4543] mb-4">Страница не найдена</h2>
        <p className="text-gray-600 mb-6">К сожалению, запрашиваемая страница не существует</p>
        <Link
          href="/"
          className="inline-block bg-[#62bb46] text-white px-6 py-2 rounded hover:bg-[#55a83d] transition-colors"
        >
          На главную
        </Link>
      </div>
    </div>
  );
}
