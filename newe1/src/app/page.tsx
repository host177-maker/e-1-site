import HeroBanner from '@/components/HeroBanner';
import FeaturesSection from '@/components/FeaturesSection';
import ReviewsSection from '@/components/ReviewsSection';
import AboutSection from '@/components/AboutSection';

export default function Home() {
  return (
    <>
      {/* Hero Banner */}
      <HeroBanner />

      {/* Features strip with category cards */}
      <FeaturesSection />

      {/* Reviews */}
      <ReviewsSection />

      {/* About Company */}
      <AboutSection />
    </>
  );
}
