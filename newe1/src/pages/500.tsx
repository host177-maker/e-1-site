export default function Custom500() {
  return (
    <div style={{
      minHeight: '100vh',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      fontFamily: 'system-ui, -apple-system, sans-serif',
      backgroundColor: '#f9f9f9'
    }}>
      <div style={{ textAlign: 'center', padding: '2rem' }}>
        <h1 style={{ fontSize: '4rem', margin: '0', color: '#62bb46' }}>
          500
        </h1>
        <p style={{ fontSize: '1.25rem', color: '#666', margin: '1rem 0' }}>
          Внутренняя ошибка сервера
        </p>
        <a
          href="/"
          style={{
            display: 'inline-block',
            marginTop: '1rem',
            padding: '0.75rem 2rem',
            backgroundColor: '#62bb46',
            color: 'white',
            textDecoration: 'none',
            borderRadius: '4px'
          }}
        >
          На главную
        </a>
      </div>
    </div>
  );
}
