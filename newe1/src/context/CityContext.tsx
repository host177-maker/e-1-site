'use client';

import { createContext, useContext, useState, useEffect, ReactNode } from 'react';

interface City {
  name: string;
  region: string;
}

interface CityContextType {
  city: City;
  setCity: (city: City) => void;
  isLoading: boolean;
}

const defaultCity: City = {
  name: 'Москва',
  region: 'Московская область',
};

const CityContext = createContext<CityContextType>({
  city: defaultCity,
  setCity: () => {},
  isLoading: true,
});

export function CityProvider({ children }: { children: ReactNode }) {
  const [city, setCityState] = useState<City>(defaultCity);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    // Check localStorage first
    const savedCity = localStorage.getItem('selectedCity');
    if (savedCity) {
      try {
        const parsed = JSON.parse(savedCity);
        setCityState(parsed);
        setIsLoading(false);
        return;
      } catch {
        // Invalid JSON, continue to geolocation
      }
    }

    // Try to detect city by IP
    detectCityByIP();
  }, []);

  const detectCityByIP = async () => {
    try {
      const response = await fetch('/api/geolocation');
      const data = await response.json();
      if (data.success && data.city) {
        const detectedCity = {
          name: data.city,
          region: data.region || '',
        };
        setCityState(detectedCity);
        localStorage.setItem('selectedCity', JSON.stringify(detectedCity));
      }
    } catch (error) {
      console.error('Failed to detect city:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const setCity = (newCity: City) => {
    setCityState(newCity);
    localStorage.setItem('selectedCity', JSON.stringify(newCity));
  };

  return (
    <CityContext.Provider value={{ city, setCity, isLoading }}>
      {children}
    </CityContext.Provider>
  );
}

export function useCity() {
  return useContext(CityContext);
}
