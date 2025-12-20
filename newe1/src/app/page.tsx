import HeroBanner from '@/components/HeroBanner';
import CatalogSection from '@/components/CatalogSection';
import FeaturesSection from '@/components/FeaturesSection';
import ReviewsSection from '@/components/ReviewsSection';
import CTASection from '@/components/CTASection';

export default function Home() {
  return (
    <>
      {/* Hero Banner */}
      <HeroBanner />

      {/* Catalog Categories */}
      <CatalogSection />

      {/* Features / Advantages */}
      <FeaturesSection />

      {/* Reviews */}
      <ReviewsSection />

      {/* CTA Section */}
      <CTASection />
    </>
  );
}
