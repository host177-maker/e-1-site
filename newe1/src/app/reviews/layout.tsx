import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Отзывы",
  description: "Отзывы покупателей о шкафах-купе и гардеробных Е1. Реальные мнения клиентов.",
};

export default function ReviewsLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
