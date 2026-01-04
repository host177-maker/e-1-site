import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: {
    template: '%s | Блог Е1 Мебель',
    default: 'Блог | Советы по выбору шкафов и гардеробных от Е1',
  },
  description: 'Полезные статьи о выборе шкафов-купе, гардеробных и мебели на заказ. Советы по организации пространства, уходу за мебелью и дизайну интерьера от экспертов Е1.',
  openGraph: {
    title: 'Блог | Советы по выбору шкафов и гардеробных от Е1',
    description: 'Полезные статьи о выборе шкафов-купе, гардеробных и мебели на заказ.',
    type: 'website',
  },
};

export default function BlogLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
