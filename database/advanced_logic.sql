-- Advanced Database Logic for Bazaar
-- Includes: Views, Stored Procedures, Triggers, and Audit Logging

-- 1. Create Audit Logs Table (For Triggers)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_name` VARCHAR(50) NOT NULL,
    `record_id` INT NOT NULL,
    `action` VARCHAR(20) NOT NULL,
    `old_value` TEXT,
    `new_value` TEXT,
    `user_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. VIEWS
-- Simplifies fetching ads with their category and primary image
CREATE OR REPLACE VIEW `view_active_ads` AS
SELECT 
    a.id,
    a.title,
    a.price,
    a.location,
    a.condition_type,
    a.status,
    a.created_at,
    c.name AS category_name,
    c.slug AS category_slug,
    u.name AS seller_name,
    (SELECT image_path FROM ad_images WHERE ad_id = a.id ORDER BY is_primary DESC LIMIT 1) AS primary_image
FROM ads a
JOIN categories c ON a.category_id = c.id
JOIN users u ON a.user_id = u.id
WHERE a.status = 'active';

-- View for User Statistics
CREATE OR REPLACE VIEW `view_user_stats` AS
SELECT 
    u.id AS user_id,
    u.name,
    COUNT(DISTINCT a.id) AS total_ads,
    COUNT(DISTINCT CASE WHEN a.status = 'sold' THEN a.id END) AS sold_ads,
    COUNT(DISTINCT f.id) AS favorite_ads_count
FROM users u
LEFT JOIN ads a ON u.id = a.user_id
LEFT JOIN favorites f ON u.id = f.user_id
GROUP BY u.id;

-- 3. STORED PROCEDURES

-- Procedure to get full details of an ad
DELIMITER //
CREATE PROCEDURE sp_GetAdDetails(IN ad_id_param INT)
BEGIN
    -- Fetch main ad info
    SELECT 
        a.*, 
        c.name AS category_name, 
        u.name AS seller_name, 
        u.phone AS seller_phone, 
        u.avatar AS seller_avatar,
        u.created_at AS seller_joined
    FROM ads a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ad_id_param;
    
    -- Fetch all images for this ad
    SELECT id, image_path, is_primary FROM ad_images WHERE ad_id = ad_id_param ORDER BY is_primary DESC;
END //
DELIMITER ;

-- Procedure to safely toggle a favorite
DELIMITER //
CREATE PROCEDURE sp_ToggleFavorite(IN user_id_param INT, IN ad_id_param INT)
BEGIN
    DECLARE fav_id INT;
    SELECT id INTO fav_id FROM favorites WHERE user_id = user_id_param AND ad_id = ad_id_param;
    
    IF fav_id IS NULL THEN
        INSERT INTO favorites (user_id, ad_id) VALUES (user_id_param, ad_id_param);
        SELECT 'added' AS action;
    ELSE
        DELETE FROM favorites WHERE id = fav_id;
        SELECT 'removed' AS action;
    END IF;
END //
DELIMITER ;

-- 4. TRIGGERS

-- Trigger to log ad status changes (e.g. active -> sold)
DELIMITER //
CREATE TRIGGER tr_AfterAdStatusUpdate
AFTER UPDATE ON ads
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO audit_logs (table_name, record_id, action, old_value, new_value)
        VALUES ('ads', NEW.id, 'status_change', OLD.status, NEW.status);
    END IF;
END //
DELIMITER ;

-- Trigger to log ad deletions
DELIMITER //
CREATE TRIGGER tr_BeforeAdDelete
BEFORE DELETE ON ads
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, old_value)
    VALUES ('ads', OLD.id, 'delete', OLD.title);
END //
DELIMITER ;
