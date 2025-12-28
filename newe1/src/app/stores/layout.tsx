import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Салоны",
  description: "Салоны мебели Е1. Адреса, контакты и режим работы магазинов.",
};

export default function StoresLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
