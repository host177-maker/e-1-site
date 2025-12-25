import type { Metadata } from "next";
import "./globals.css";
import Header from "@/components/Header";
import Footer from "@/components/Footer";

export const metadata: Metadata = {
  title: "E-1.RU | Шкафы и гардеробные",
  description: "Шкафы-купе и гардеробные системы на заказ в Москве. Производство и продажа качественной мебели с доставкой и установкой.",
  keywords: "шкафы-купе, гардеробные, встроенные шкафы, мебель на заказ, Москва",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru">
      <body className="antialiased flex flex-col min-h-screen">
        <Header />
        <main className="flex-grow">
          {children}
        </main>
        <Footer />
      </body>
    </html>
  );
}
