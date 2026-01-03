import { NextResponse } from 'next/server';
import fs from 'fs';
import path from 'path';

interface GeoJSONFeature {
  type: 'Feature';
  id: number;
  geometry: {
    type: 'Point' | 'Polygon';
    coordinates: number[] | number[][][];
  };
  properties: {
    description?: string;
    fill?: string;
    stroke?: string;
    [key: string]: unknown;
  };
}

interface GeoJSONData {
  type: 'FeatureCollection';
  features: GeoJSONFeature[];
}

interface DeliveryPoint {
  id: number;
  name: string;
  coordinates: [number, number]; // [longitude, latitude]
}

export async function GET() {
  try {
    const filePath = path.join(process.cwd(), 'public', 'upload', 'delivery-locations.geojson');
    const fileContent = fs.readFileSync(filePath, 'utf-8');
    const geoData: GeoJSONData = JSON.parse(fileContent);

    // Extract only Point features (delivery hubs)
    const points: DeliveryPoint[] = geoData.features
      .filter(f => f.geometry.type === 'Point')
      .map(f => ({
        id: f.id,
        name: f.properties.description || `Точка ${f.id}`,
        coordinates: f.geometry.coordinates as [number, number],
      }));

    return NextResponse.json({
      success: true,
      points,
    });
  } catch (error) {
    console.error('Error loading delivery points:', error);
    return NextResponse.json({
      success: false,
      error: 'Не удалось загрузить точки доставки',
    }, { status: 500 });
  }
}
