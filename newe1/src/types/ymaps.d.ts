/* eslint-disable @typescript-eslint/no-explicit-any */
declare global {
  interface Window {
    ymaps: YMaps;
    initYandexMap?: () => void;
  }
}

interface YMaps {
  ready: (callback: () => void) => void;
  Map: new (
    container: string,
    options: {
      center: number[];
      zoom: number;
      controls?: string[];
    },
    state?: {
      searchControlProvider?: string;
    }
  ) => YMap;
  Placemark: new (
    coords: number[],
    properties: { hintContent?: string; balloonContent?: string },
    options?: { preset?: string }
  ) => YPlacemark;
  geoQuery: (geoJson: any) => YGeoQuery;
}

interface YMap {
  controls: {
    get: (name: string) => {
      options: {
        set: (options: Record<string, any>) => void;
      };
    };
  };
  geoObjects: {
    add: (obj: any) => void;
  };
}

interface YPlacemark {
  geometry: any;
  properties: any;
  options: any;
}

interface YGeoQuery {
  addToMap: (map: YMap) => YGeoQuery;
  each: (callback: (obj: YGeoObject) => void) => void;
}

interface YGeoObject {
  options: {
    set: (options: Record<string, any>) => void;
  };
  properties: {
    get: (name: string) => any;
  };
}

export {};
