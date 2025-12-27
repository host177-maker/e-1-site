-- Migration: Add is_rejected column to reviews table
-- Run this migration manually or through your deployment process

ALTER TABLE reviews ADD COLUMN IF NOT EXISTS is_rejected BOOLEAN DEFAULT false;

-- Create index for faster filtering
CREATE INDEX IF NOT EXISTS idx_reviews_status ON reviews(is_active, is_rejected);
