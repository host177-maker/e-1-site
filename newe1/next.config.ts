import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Use standalone output for better compatibility
  output: 'standalone',

  // Allow external images from Yandex Cloud Storage
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'storage.yandexcloud.net',
        pathname: '/**',
      },
    ],
  },

  // Configure server actions
  experimental: {
    serverActions: {
      bodySizeLimit: '2mb',
    },
  },

  // Redirects for backward compatibility with old site
  async redirects() {
    return [
      {
        source: '/client/purchase-warranty',
        destination: '/service/purchase-terms',
        permanent: true,
      },
      {
        source: '/client/purchase-warranty/',
        destination: '/service/purchase-terms',
        permanent: true,
      },
    ];
  },
};

export default nextConfig;
