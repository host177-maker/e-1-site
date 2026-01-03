-- Миграция для таблицы описания услуг
-- Услуги отображаются на всех карточках товаров после описания серии

CREATE TABLE IF NOT EXISTS catalog_services (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Индекс для сортировки активных услуг
CREATE INDEX IF NOT EXISTS idx_catalog_services_active_sort ON catalog_services(is_active, sort_order);
