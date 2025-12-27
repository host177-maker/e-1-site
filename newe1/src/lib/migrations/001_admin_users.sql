-- Migration: Create admin_users table
-- Run this migration manually or through your deployment process

CREATE TABLE IF NOT EXISTS admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    last_login TIMESTAMP WITH TIME ZONE,
    created_by INTEGER REFERENCES admin_users(id)
);

-- Create index on username for faster lookups
CREATE INDEX IF NOT EXISTS idx_admin_users_username ON admin_users(username);

-- Add comment describing the table
COMMENT ON TABLE admin_users IS 'Admin panel users for managing reviews and other admin functions';
