import type { Metadata } from "next";
import { Open_Sans } from "next/font/google";
import "./globals.css";
import Providers from "@/components/Providers";
import ConditionalLayout from "@/components/ConditionalLayout";

const openSans = Open_Sans({
  subsets: ["latin", "cyrillic"],
  variable: "--font-open-sans",
  display: "swap",
});

export const metadata: Metadata = {
  title: "Е1 | Шкафы и гардеробные",
  description: "Шкафы-купе и гардеробные системы на заказ в Москве. Производство и продажа качественной мебели с доставкой и установкой.",
  keywords: "шкафы-купе, гардеробные, встроенные шкафы, мебель на заказ, Москва",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru" className={openSans.variable}>
      <body className="antialiased flex flex-col min-h-screen">
        <Providers>
          <ConditionalLayout>{children}</ConditionalLayout>
        </Providers>
      </body>
    </html>
  );
}
