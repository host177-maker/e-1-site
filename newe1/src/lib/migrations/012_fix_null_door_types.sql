-- Исправление товаров без типа шкафа
-- После удаления tolkatel, товары с этим типом получили NULL
-- Назначаем им тип "Гардероб"

UPDATE catalog_products
SET door_type_id = (SELECT id FROM catalog_door_types WHERE slug = 'garderob')
WHERE door_type_id IS NULL;
