import HeroBanner from '@/components/HeroBanner';
import FeaturesSection from '@/components/FeaturesSection';
import CatalogSection from '@/components/CatalogSection';
import ReviewsSection from '@/components/ReviewsSection';
import CTASection from '@/components/CTASection';

export default function Home() {
  return (
    <>
      {/* Hero Banner */}
      <HeroBanner />

      {/* Features strip with green icons */}
      <FeaturesSection />

      {/* Catalog Categories */}
      <CatalogSection />

      {/* Reviews */}
      <ReviewsSection />

      {/* CTA Section */}
      <CTASection />
    </>
  );
}
