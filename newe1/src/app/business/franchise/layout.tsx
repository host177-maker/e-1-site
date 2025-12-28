import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Франчайзинг",
  description: "Франшиза Е1 — готовая модель прибыльного бизнеса в мебельной отрасли. 0% роялти, поддержка на всех этапах.",
};

export default function FranchiseLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
