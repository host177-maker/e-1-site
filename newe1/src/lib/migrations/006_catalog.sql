-- Миграция для каталога товаров
-- Создаёт таблицы для хранения каталога шкафов-купе

-- Серии товаров (Прайм, Экспресс, Локер и т.д.)
CREATE TABLE IF NOT EXISTS catalog_series (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image1 VARCHAR(500),
    image2 VARCHAR(500),
    image3 VARCHAR(500),
    image4 VARCHAR(500),
    video1 VARCHAR(500),
    video2 VARCHAR(500),
    gallery_folder VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Цвета профилей (Белый, Черный, Бронза, Серебро, Без профиля)
CREATE TABLE IF NOT EXISTS catalog_profile_colors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true
);

-- Цвета корпусов (привязаны к сериям)
CREATE TABLE IF NOT EXISTS catalog_body_colors (
    id SERIAL PRIMARY KEY,
    series_id INTEGER REFERENCES catalog_series(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    image_small VARCHAR(500),
    image_large VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    UNIQUE(series_id, name)
);

-- Наполнение корпуса (полки, штанги и т.д.) - зависит от серии и размеров
CREATE TABLE IF NOT EXISTS catalog_fillings (
    id SERIAL PRIMARY KEY,
    series_id INTEGER REFERENCES catalog_series(id) ON DELETE CASCADE,
    door_count INTEGER NOT NULL,
    height INTEGER NOT NULL,
    width INTEGER NOT NULL,
    depth INTEGER NOT NULL,
    short_name VARCHAR(255),
    description TEXT,
    image_plain VARCHAR(500),
    image_dimensions VARCHAR(500),
    image_filled VARCHAR(500),
    base_article VARCHAR(100),
    extra_article1 VARCHAR(100),
    extra_article2 VARCHAR(100),
    extra_article3 VARCHAR(100),
    extra_article4 VARCHAR(100),
    UNIQUE(series_id, door_count, height, width, depth)
);

-- Типы открывания дверей
CREATE TABLE IF NOT EXISTS catalog_door_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INTEGER DEFAULT 0
);

-- Материалы дверей
CREATE TABLE IF NOT EXISTS catalog_door_materials (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) NOT NULL UNIQUE,
    sort_order INTEGER DEFAULT 0
);

-- Товары (группы артикулов) - карточки сайта
CREATE TABLE IF NOT EXISTS catalog_products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(500) NOT NULL,
    slug VARCHAR(500) NOT NULL UNIQUE,
    series_id INTEGER REFERENCES catalog_series(id) ON DELETE SET NULL,
    door_type_id INTEGER REFERENCES catalog_door_types(id) ON DELETE SET NULL,
    door_count INTEGER,
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Варианты товаров (SKU) - конкретные комбинации параметров
CREATE TABLE IF NOT EXISTS catalog_variants (
    id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL REFERENCES catalog_products(id) ON DELETE CASCADE,
    article VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(500),
    body_color_id INTEGER REFERENCES catalog_body_colors(id) ON DELETE SET NULL,
    profile_color_id INTEGER REFERENCES catalog_profile_colors(id) ON DELETE SET NULL,
    height INTEGER NOT NULL,
    width INTEGER NOT NULL,
    depth INTEGER NOT NULL,
    door_material1 VARCHAR(255),
    door_material2 VARCHAR(255),
    door_material3 VARCHAR(255),
    door_material4 VARCHAR(255),
    door_material5 VARCHAR(255),
    door_material6 VARCHAR(255),
    image_white VARCHAR(500),
    image_interior VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- История импортов каталога
CREATE TABLE IF NOT EXISTS catalog_import_history (
    id SERIAL PRIMARY KEY,
    imported_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    source_url VARCHAR(500),
    products_count INTEGER DEFAULT 0,
    variants_count INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending',
    error_message TEXT,
    imported_by INTEGER REFERENCES admin_users(id)
);

-- Индексы для оптимизации запросов
CREATE INDEX IF NOT EXISTS idx_catalog_variants_product ON catalog_variants(product_id);
CREATE INDEX IF NOT EXISTS idx_catalog_variants_body_color ON catalog_variants(body_color_id);
CREATE INDEX IF NOT EXISTS idx_catalog_variants_profile_color ON catalog_variants(profile_color_id);
CREATE INDEX IF NOT EXISTS idx_catalog_variants_dimensions ON catalog_variants(height, width, depth);
CREATE INDEX IF NOT EXISTS idx_catalog_products_series ON catalog_products(series_id);
CREATE INDEX IF NOT EXISTS idx_catalog_products_slug ON catalog_products(slug);
CREATE INDEX IF NOT EXISTS idx_catalog_body_colors_series ON catalog_body_colors(series_id);
CREATE INDEX IF NOT EXISTS idx_catalog_fillings_series ON catalog_fillings(series_id);

-- Начальные данные для типов открывания
INSERT INTO catalog_door_types (name, slug, sort_order) VALUES
    ('Купе', 'kupe', 1),
    ('Распашной', 'raspashnoy', 2),
    ('Гармошка', 'garmoshka', 3),
    ('Толкатель', 'tolkatel', 4),
    ('Без дверей', 'bez-dverey', 5)
ON CONFLICT (name) DO NOTHING;

-- Начальные данные для цветов профилей
INSERT INTO catalog_profile_colors (name, slug, sort_order) VALUES
    ('Белый профиль', 'belyy-profil', 1),
    ('Черный профиль', 'chernyy-profil', 2),
    ('Бронза профиль', 'bronza-profil', 3),
    ('Серебро профиль', 'serebro-profil', 4),
    ('Без профиля', 'bez-profilya', 5)
ON CONFLICT (name) DO NOTHING;
