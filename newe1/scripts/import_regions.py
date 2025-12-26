#!/usr/bin/env python3
"""
Script to import regions and cities from Excel/HTML file to PostgreSQL database.
"""

import os
import sys
import re
import psycopg2
from bs4 import BeautifulSoup

# Database connection parameters
DB_CONFIG = {
    'host': os.environ.get('POSTGRES_HOST', '192.168.40.41'),
    'port': int(os.environ.get('POSTGRES_PORT', '5432')),
    'database': os.environ.get('POSTGRES_DB', 'newe1'),
    'user': os.environ.get('POSTGRES_USER', 'newe1'),
    'password': os.environ.get('POSTGRES_PASSWORD', 'newe1pass'),
}

# SQL to create tables
CREATE_TABLES_SQL = """
-- Create regions table
CREATE TABLE IF NOT EXISTS regions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    sort_order INTEGER DEFAULT 500,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create cities table
CREATE TABLE IF NOT EXISTS cities (
    id SERIAL PRIMARY KEY,
    original_id INTEGER,
    name VARCHAR(255) NOT NULL,
    region_id INTEGER REFERENCES regions(id),
    external_code VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 500,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(name, region_id)
);

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_cities_region_id ON cities(region_id);
CREATE INDEX IF NOT EXISTS idx_cities_name ON cities(name);

-- Add region_id column to salons if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name = 'salons' AND column_name = 'region_id') THEN
        ALTER TABLE salons ADD COLUMN region_id INTEGER REFERENCES regions(id);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name = 'salons' AND column_name = 'city_id') THEN
        ALTER TABLE salons ADD COLUMN city_id INTEGER REFERENCES cities(id);
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_salons_region_id ON salons(region_id);
CREATE INDEX IF NOT EXISTS idx_salons_city_id ON salons(city_id);
"""


def connect_db():
    """Connect to PostgreSQL database."""
    return psycopg2.connect(**DB_CONFIG)


def create_tables(conn):
    """Create required tables."""
    with conn.cursor() as cur:
        cur.execute(CREATE_TABLES_SQL)
    conn.commit()
    print("Tables created/verified successfully")


def parse_regions_file(filepath):
    """Parse region.xls (HTML format) file."""
    with open(filepath, 'r', encoding='utf-8') as f:
        html = f.read()

    soup = BeautifulSoup(html, 'html.parser')
    table = soup.find('table')
    rows = table.find_all('tr')

    cities_data = []

    for row in rows[1:]:  # Skip header
        cells = [td.text.strip() for td in row.find_all('td')]
        if len(cells) >= 14:
            city_name = cells[0]
            region = cells[1].strip()
            city_id = cells[13]
            external_code = cells[12]
            active = cells[9].lower() == 'да'

            # Skip if no region specified
            if not region:
                continue

            try:
                city_id = int(city_id)
            except (ValueError, TypeError):
                city_id = None

            cities_data.append({
                'name': city_name,
                'region': region,
                'original_id': city_id,
                'external_code': external_code,
                'is_active': active
            })

    return cities_data


def import_data(conn, cities_data):
    """Import regions and cities to database."""
    # Get unique regions
    regions = sorted(set(c['region'] for c in cities_data if c['region']))

    with conn.cursor() as cur:
        # Insert regions
        for region_name in regions:
            cur.execute("""
                INSERT INTO regions (name)
                VALUES (%s)
                ON CONFLICT (name) DO NOTHING
                RETURNING id
            """, (region_name,))
        conn.commit()

        print(f"Imported {len(regions)} regions")

        # Get region IDs
        cur.execute("SELECT id, name FROM regions")
        region_map = {row[1]: row[0] for row in cur.fetchall()}

        # Insert cities
        inserted = 0
        for city in cities_data:
            if not city['region']:
                continue
            region_id = region_map.get(city['region'])
            if not region_id:
                continue

            cur.execute("""
                INSERT INTO cities (name, region_id, original_id, external_code, is_active)
                VALUES (%s, %s, %s, %s, %s)
                ON CONFLICT (name, region_id) DO UPDATE SET
                    original_id = EXCLUDED.original_id,
                    external_code = EXCLUDED.external_code,
                    is_active = EXCLUDED.is_active
            """, (city['name'], region_id, city['original_id'],
                  city['external_code'], city['is_active']))
            inserted += 1

        conn.commit()
        print(f"Imported {inserted} cities")


def update_salons_references(conn, cities_data):
    """Update salons with region_id, city_id and correct region name (oblast)."""
    # Build city -> oblast mapping from cities_data
    city_to_oblast = {}
    for city in cities_data:
        city_name = city['name'].lower().strip()
        oblast = city['region']
        city_to_oblast[city_name] = oblast

    with conn.cursor() as cur:
        # First, update the region field in salons to be the oblast (not city)
        # The current region field contains city names like "Краснодар"
        # We need to replace them with oblast names like "Краснодарский край"

        # Get all salons with their current region (which is actually a city name)
        cur.execute("SELECT id, city, region FROM salons")
        salons = cur.fetchall()

        region_fixed = 0
        for salon_id, city, current_region in salons:
            # Try to find oblast by city name
            city_lower = city.lower().strip() if city else ''
            region_lower = current_region.lower().strip() if current_region else ''

            # Check if current region is in our city->oblast map (meaning it's a city, not oblast)
            new_oblast = None
            if region_lower in city_to_oblast:
                new_oblast = city_to_oblast[region_lower]
            elif city_lower in city_to_oblast:
                new_oblast = city_to_oblast[city_lower]

            if new_oblast and new_oblast != current_region:
                cur.execute(
                    "UPDATE salons SET region = %s WHERE id = %s",
                    (new_oblast, salon_id)
                )
                region_fixed += 1

        conn.commit()
        print(f"Fixed {region_fixed} salons with correct oblast in region field")

        # Now update city_id based on city name match
        cur.execute("""
            UPDATE salons s
            SET city_id = c.id
            FROM cities c
            WHERE LOWER(TRIM(s.city)) = LOWER(TRIM(c.name))
            AND s.city_id IS NULL
        """)
        city_updated = cur.rowcount

        # Update region_id based on the now-correct region (oblast) name
        cur.execute("""
            UPDATE salons s
            SET region_id = r.id
            FROM regions r
            WHERE LOWER(TRIM(s.region)) = LOWER(TRIM(r.name))
            AND s.region_id IS NULL
        """)
        region_updated = cur.rowcount

        # Fallback: Update region_id from city_id if region_id is still NULL
        cur.execute("""
            UPDATE salons s
            SET region_id = c.region_id
            FROM cities c
            WHERE s.city_id = c.id
            AND s.region_id IS NULL
        """)
        region_updated += cur.rowcount

        # Also update region name from cities if still not matched
        cur.execute("""
            UPDATE salons s
            SET region = r.name
            FROM cities c
            JOIN regions r ON c.region_id = r.id
            WHERE s.city_id = c.id
            AND (s.region IS NULL OR s.region = '' OR s.region_id IS NULL)
        """)

        conn.commit()
        print(f"Updated {region_updated} salons with region_id")
        print(f"Updated {city_updated} salons with city_id")


def verify_data(conn):
    """Verify imported data."""
    with conn.cursor() as cur:
        cur.execute("SELECT COUNT(*) FROM regions")
        region_count = cur.fetchone()[0]

        cur.execute("SELECT COUNT(*) FROM cities")
        city_count = cur.fetchone()[0]

        cur.execute("SELECT COUNT(*) FROM salons WHERE region_id IS NOT NULL")
        salons_with_region = cur.fetchone()[0]

        cur.execute("SELECT COUNT(*) FROM salons WHERE city_id IS NOT NULL")
        salons_with_city = cur.fetchone()[0]

        cur.execute("SELECT COUNT(*) FROM salons")
        total_salons = cur.fetchone()[0]

        print(f"\n=== Verification ===")
        print(f"Total regions: {region_count}")
        print(f"Total cities: {city_count}")
        print(f"Total salons: {total_salons}")
        print(f"Salons with region_id: {salons_with_region}")
        print(f"Salons with city_id: {salons_with_city}")

        # Show regions with salon counts
        print(f"\n=== Regions with salons ===")
        cur.execute("""
            SELECT r.name, COUNT(s.id) as salon_count
            FROM regions r
            LEFT JOIN salons s ON s.region_id = r.id
            GROUP BY r.id, r.name
            HAVING COUNT(s.id) > 0
            ORDER BY salon_count DESC, r.name
            LIMIT 20
        """)
        for row in cur.fetchall():
            print(f"  {row[0]}: {row[1]} salons")

        # Show salons without region match
        print(f"\n=== Unmatched salons (sample) ===")
        cur.execute("""
            SELECT DISTINCT city, region
            FROM salons
            WHERE region_id IS NULL
            LIMIT 15
        """)
        for row in cur.fetchall():
            print(f"  City: {row[0]}, Region: {row[1]}")


def main():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    regions_file = os.path.join(script_dir, '..', 'uploads', 'region.xls')

    if not os.path.exists(regions_file):
        print(f"Error: File not found: {regions_file}")
        sys.exit(1)

    print(f"Connecting to database at {DB_CONFIG['host']}:{DB_CONFIG['port']}/{DB_CONFIG['database']}")

    try:
        conn = connect_db()
        print("Connected to database successfully")

        # Create tables
        create_tables(conn)

        # Parse and import data
        print(f"\nParsing {regions_file}...")
        cities_data = parse_regions_file(regions_file)
        print(f"Found {len(cities_data)} cities with regions")

        # Import data
        print("\nImporting data...")
        import_data(conn, cities_data)

        # Update salon references
        print("\nUpdating salon references...")
        update_salons_references(conn, cities_data)

        # Verify
        verify_data(conn)

        conn.close()
        print("\nImport completed successfully!")

    except psycopg2.Error as e:
        print(f"Database error: {e}")
        sys.exit(1)


if __name__ == '__main__':
    main()
