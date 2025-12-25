import { NextPageContext } from 'next';

interface ErrorProps {
  statusCode?: number;
}

function Error({ statusCode }: ErrorProps) {
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
          {statusCode || 'Ошибка'}
        </h1>
        <p style={{ fontSize: '1.25rem', color: '#666', margin: '1rem 0' }}>
          {statusCode === 404
            ? 'Страница не найдена'
            : statusCode === 500
            ? 'Внутренняя ошибка сервера'
            : 'Произошла ошибка'}
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

Error.getInitialProps = ({ res, err }: NextPageContext) => {
  const statusCode = res ? res.statusCode : err ? err.statusCode : 404;
  return { statusCode };
};

export default Error;
