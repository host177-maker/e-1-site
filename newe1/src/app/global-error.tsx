'use client';

export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  // Log error for debugging
  console.error('Global error:', error);

  return (
    <html lang="ru">
      <body>
        <div style={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontFamily: 'system-ui, sans-serif'
        }}>
          <div style={{ textAlign: 'center' }}>
            <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#3d4543', marginBottom: '1rem' }}>
              Что-то пошло не так
            </h2>
            <p style={{ color: '#666', marginBottom: '1.5rem' }}>
              Произошла критическая ошибка
            </p>
            <button
              onClick={() => reset()}
              style={{
                backgroundColor: '#62bb46',
                color: 'white',
                padding: '0.5rem 1.5rem',
                border: 'none',
                borderRadius: '4px',
                cursor: 'pointer'
              }}
            >
              Попробовать снова
            </button>
          </div>
        </div>
      </body>
    </html>
  );
}
