'use client';

import { CityProvider } from '@/context/CityContext';
import { ReactNode } from 'react';

export default function Providers({ children }: { children: ReactNode }) {
  return (
    <CityProvider>
      {children}
    </CityProvider>
  );
}
