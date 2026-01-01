-- Добавляем описание и изображения для цветов профиля
ALTER TABLE catalog_profile_colors
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS image_small VARCHAR(500),
ADD COLUMN IF NOT EXISTS image_large VARCHAR(500);
