import HeroBanner from '@/components/HeroBanner';
import FeaturesSection from '@/components/FeaturesSection';
import CatalogSection from '@/components/CatalogSection';
import ReviewsSection from '@/components/ReviewsSection';
import MeasurementCTA from '@/components/MeasurementCTA';

export default function Home() {
  return (
    <>
      {/* Hero Banner */}
      <HeroBanner />

      {/* CTA - Вызвать замерщика */}
      <MeasurementCTA />

      {/* Features strip with green icons */}
      <FeaturesSection />

      {/* Catalog Categories */}
      <CatalogSection />

      {/* Reviews */}
      <ReviewsSection />
    </>
  );
}
