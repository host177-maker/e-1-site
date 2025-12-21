import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Админ-панель | E-1.RU',
  description: 'Панель управления сайтом',
  robots: 'noindex, nofollow',
};

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen bg-gray-100">
      {children}
    </div>
  );
}
