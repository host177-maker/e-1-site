import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Доставка",
  description: "Доставка мебели по Москве и России. Условия и сроки доставки шкафов-купе и гардеробных.",
};

export default function DeliveryLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
