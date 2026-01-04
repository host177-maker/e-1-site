-- Переименование типа двери "Толкатель" в "Гардероб"
UPDATE catalog_door_types
SET name = 'Гардероб', slug = 'garderob'
WHERE slug = 'tolkatel';
