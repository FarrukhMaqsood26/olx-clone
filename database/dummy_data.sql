USE bazaar;

INSERT IGNORE INTO users (id, name, email, password) VALUES (999, 'Test Seller', 'seller@bazaar.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO ads (id, user_id, category_id, title, description, price, condition_type, location, status, created_at) VALUES 
(101, 999, 1, 'iPhone 14 Pro Max 256GB - Deep Purple', 'Brand new condition, PTA approved, 100% battery health. Comes with original box and cable.', 320000.00, 'used', 'DHA Phase 5, Lahore', 'active', NOW()),
(102, 999, 2, 'Honda Civic Oriel 1.8 i-VTEC 2020', 'First owner car, totally bumper to bumper genuine. 45,000 km driven. Token taxes paid up to date.', 6500000.00, 'used', 'F-11, Islamabad', 'active', NOW() - INTERVAL 1 DAY),
(103, 999, 4, 'Sony PlayStation 5 Disc Edition + 2 Controllers', 'Sparingly used PS5. Comes with two dualsense controllers and 3 physical games.', 145000.00, 'used', 'Clifton, Karachi', 'active', NOW() - INTERVAL 2 DAY),
(104, 999, 1, 'Samsung Galaxy S23 Ultra 512GB', 'Factory unlocked. Scratchless condition. Only 2 months used.', 280000.00, 'used', 'Bahria Town, Rawalpindi', 'active', NOW() - INTERVAL 3 DAY),
(105, 999, 2, 'Toyota Corolla Altis Grande 1.8 CVT-i 2022', 'White color. 100% original paint. Very carefully driven.', 7200000.00, 'used', 'Gulberg III, Lahore', 'active', NOW() - INTERVAL 4 DAY),
(106, 999, 4, 'Apple MacBook Air M2 13-inch 2022', 'Space grey, 8GB RAM, 256GB SSD. Battery cycle count is only 20.', 265000.00, 'used', 'G-10, Islamabad', 'active', NOW() - INTERVAL 5 DAY),
(107, 999, 3, '10 Marla Plot for Sale in DHA Phase 8', 'Prime location, corner file, all dues clear. A great investment opportunity.', 18000000.00, 'new', 'DHA Phase 8, Lahore', 'active', NOW() - INTERVAL 6 DAY),
(108, 999, 4, 'LG 65" OLED 4K Smart TV', 'Incredible picture quality. Perfect for gaming and movies. Selling due to upgrading.', 450000.00, 'used', 'DHA Phase 2, Karachi', 'active', NOW() - INTERVAL 7 DAY)
ON DUPLICATE KEY UPDATE id=id;

INSERT IGNORE INTO ad_images (ad_id, image_path, is_primary) VALUES
(101, 'https://images.unsplash.com/photo-1544228818-490b6d419a4e?auto=format&fit=crop&w=500&q=60', 1),
(102, 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=500&q=60', 1),
(103, 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=500&q=60', 1),
(104, 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=500&q=60', 1),
(105, 'https://images.unsplash.com/photo-1609521263047-f8f205293f24?auto=format&fit=crop&w=500&q=60', 1),
(106, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=500&q=60', 1),
(107, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=500&q=60', 1),
(108, 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=500&q=60', 1);
