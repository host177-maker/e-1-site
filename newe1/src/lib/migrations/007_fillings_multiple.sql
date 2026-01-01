-- Migration: Allow multiple fillings for same size configuration
-- Each filling can have different short_name (filling type)

-- Drop the old unique constraint
ALTER TABLE catalog_fillings DROP CONSTRAINT IF EXISTS catalog_fillings_series_id_door_count_height_width_depth_key;

-- Add new unique constraint including short_name to differentiate filling types
-- Using COALESCE to handle NULL short_name values
CREATE UNIQUE INDEX IF NOT EXISTS catalog_fillings_unique_idx
ON catalog_fillings (series_id, door_count, height, width, depth, COALESCE(short_name, ''));
