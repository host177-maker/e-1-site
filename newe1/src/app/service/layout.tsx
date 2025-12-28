import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Сервис",
  description: "Сервис и услуги мебельной компании Е1.",
};

export default function ServiceLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
