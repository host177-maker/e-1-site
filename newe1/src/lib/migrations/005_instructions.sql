-- Migration: Create instructions table
-- Run this migration manually or through your deployment process

CREATE TABLE IF NOT EXISTS instructions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    pdf_path VARCHAR(500) NOT NULL,
    video_url VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create index for active instructions
CREATE INDEX IF NOT EXISTS idx_instructions_active ON instructions(is_active);
CREATE INDEX IF NOT EXISTS idx_instructions_sort ON instructions(sort_order);
