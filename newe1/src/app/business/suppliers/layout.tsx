import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Е1 | Поставщикам',
  description: 'Сотрудничество с поставщиками материалов и логистическими компаниями. Долгосрочное партнёрство, стабильные объёмы.',
};

export default function SuppliersLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
