-- Миграция для добавления рекламных плашек к товарам
-- Добавляет поля discount_percent (скидка %) и promo_badge (текст плашки)

-- Добавляем колонку для процента скидки
ALTER TABLE catalog_products
ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) DEFAULT NULL;

-- Добавляем колонку для текста рекламной плашки
ALTER TABLE catalog_products
ADD COLUMN IF NOT EXISTS promo_badge VARCHAR(100) DEFAULT NULL;

-- Комментарии к колонкам
COMMENT ON COLUMN catalog_products.discount_percent IS 'Дополнительная скидка в процентах';
COMMENT ON COLUMN catalog_products.promo_badge IS 'Текст рекламной плашки';
