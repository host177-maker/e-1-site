'use client';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div className="min-h-[50vh] flex items-center justify-center">
      <div className="text-center">
        <h2 className="text-2xl font-bold text-[#3d4543] mb-4">Что-то пошло не так</h2>
        <p className="text-gray-600 mb-6">Произошла ошибка при загрузке страницы</p>
        <button
          onClick={() => reset()}
          className="bg-[#62bb46] text-white px-6 py-2 rounded hover:bg-[#55a83d] transition-colors"
        >
          Попробовать снова
        </button>
      </div>
    </div>
  );
}
