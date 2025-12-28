import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Е1 | Продавцам на маркетплейсах",
  description: "Сотрудничество с селлерами Wildberries, Ozon, Яндекс Маркет. Доставка в 40+ регионов, API-интеграции.",
};

export default function MarketplaceLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return <>{children}</>;
}
