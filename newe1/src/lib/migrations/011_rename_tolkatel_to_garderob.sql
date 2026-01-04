-- Удаление старого типа "Толкатель" (заменён на "Гардероб")
-- Гардероб уже существует в базе, поэтому просто удаляем tolkatel
DELETE FROM catalog_door_types WHERE slug = 'tolkatel';
