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

  // Exclude pg from client-side bundling
  serverExternalPackages: ['pg', 'pg-connection-string', 'pg-pool'],

  // Webpack configuration to handle Node.js modules
  webpack: (config, { isServer }) => {
    if (!isServer) {
      // Don't bundle Node.js modules on the client
      config.resolve.fallback = {
        ...config.resolve.fallback,
        fs: false,
        net: false,
        tls: false,
        dns: false,
        'pg-native': false,
      };
      // Externalize pg packages for non-server builds
      config.externals = [
        ...(Array.isArray(config.externals) ? config.externals : []),
        'pg',
        'pg-connection-string',
        'pg-pool',
      ];
    }
    return config;
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
