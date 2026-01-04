import HeroBanner from '@/components/HeroBanner';
import FeaturesSection from '@/components/FeaturesSection';
import TopSalesSection from '@/components/TopSalesSection';
import MeasurerBanner from '@/components/MeasurerBanner';
import ReviewsSection from '@/components/ReviewsSection';
import AboutSection from '@/components/AboutSection';

export default function Home() {
  return (
    <>
      {/* Hero Banner */}
      <HeroBanner />

      {/* Features strip with category cards */}
      <FeaturesSection />

      {/* Top Sales - 3 rows of products */}
      <TopSalesSection />

      {/* Measurer Banner - call to action */}
      <MeasurerBanner />

      {/* Reviews */}
      <ReviewsSection />

      {/* About Company */}
      <AboutSection />
    </>
  );
}
