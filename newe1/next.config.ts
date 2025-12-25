import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Use standalone output for better compatibility
  output: 'standalone',

  // Disable automatic static optimization for error pages
  // This prevents useContext errors during prerendering
  experimental: {
    // Force App Router error handling
    serverActions: {
      bodySizeLimit: '2mb',
    },
  },
};

export default nextConfig;
