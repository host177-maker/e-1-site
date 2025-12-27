import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Админ-панель | Е1',
  description: 'Административная панель мебельной компании Е1',
  robots: 'noindex, nofollow',
};

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
