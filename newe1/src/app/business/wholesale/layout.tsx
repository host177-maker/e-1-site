import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Оптовые продажи",
  description: "Оптовые поставки шкафов и гардеробных от производителя. Маржинальность до 50%, доставка в 500+ городов России.",
};

export default function WholesaleLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
