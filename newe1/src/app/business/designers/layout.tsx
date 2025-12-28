import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Дизайнерам и архитекторам",
  description: "Программа сотрудничества для дизайнеров и архитекторов. Получайте вознаграждение за каждую сделку с вашими клиентами.",
};

export default function DesignersLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
