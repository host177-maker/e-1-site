import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Акции",
  description: "Акции и специальные предложения на шкафы-купе и гардеробные Е1.",
};

export default function SalesLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
