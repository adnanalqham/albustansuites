-- =============================================
-- Al Bustan Suites - Hotel Database Schema
-- Version: 2.0 (Clean)
-- =============================================

CREATE DATABASE IF NOT EXISTS `albustan_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `albustan_db`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- =============================================
-- Admins Table
-- =============================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','admin') DEFAULT 'admin',
  `remember_token` VARCHAR(64) NULL,
  `reset_otp` VARCHAR(10) NULL,
  `reset_expires` DATETIME NULL,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin@albustan.com / admin123
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@albustan.com', '$2y$10$92IXUNpkjO0rOQ5by50/7.d3FEWGGPROdGi3sW.0a.K.RUAfaxFpO', 'super_admin');

-- =============================================
-- Users (Hotel Guests) Table
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `nationality` VARCHAR(80) NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Room Categories Table
-- =============================================
CREATE TABLE IF NOT EXISTS `room_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(100) NOT NULL,
  `name_ar` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `room_categories` (`name_en`, `name_ar`, `slug`, `sort_order`) VALUES
('Suites', 'الأجنحة', 'suites', 1),
('Deluxe Rooms', 'غرف ديلوكس', 'deluxe', 2),
('Standard Rooms', 'الغرف العادية', 'standard', 3);

-- =============================================
-- Rooms Table
-- =============================================
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name_en` VARCHAR(150) NOT NULL,
  `name_ar` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description_en` TEXT,
  `description_ar` TEXT,
  `short_desc_en` VARCHAR(255),
  `short_desc_ar` VARCHAR(255),
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'USD',
  `size_sqm` INT NULL,
  `capacity_adults` INT DEFAULT 2,
  `capacity_children` INT DEFAULT 1,
  `floor` VARCHAR(20) NULL,
  `view_type_en` VARCHAR(100) NULL,
  `view_type_ar` VARCHAR(100) NULL,
  `is_available` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `main_image` VARCHAR(255) NULL,
  `images` TEXT,
  `amenities_en` TEXT,
  `amenities_ar` TEXT,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `room_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(1, 'Royal Suite', 'الجناح الملكي', 'royal-suite',
 'Experience ultimate luxury in our Royal Suite, the pinnacle of elegance at Al Bustan. Featuring a private living area, exclusive butler service, and panoramic city views, this suite redefines grandeur.',
 'استمتع بأقصى درجات الفخامة في جناحنا الملكي، ذروة الأناقة في فندق البستان. يضم صالة معيشة خاصة وخدمة كونسيرج حصرية وإطلالات بانورامية على المدينة.',
 'The most prestigious suite at Al Bustan',
 'أرقى جناح في فندق البستان',
 450.00, 'USD', 180, 3, 2, 'Panoramic City View', 'إطلالة بانورامية على المدينة',
 1, 1, 'images/royal-suite.jpg',
 'King Bed,Private Living Room,Private Dining,Butler Service,Jacuzzi,Espresso Machine,Mini Bar,Smart TV,High-Speed WiFi,Air Conditioning,Safe,International Channels',
 'سرير كينج,صالة معيشة خاصة,غرفة طعام خاصة,خدمة مرافق,جاكوزي,ماكينة إسبريسو,ميني بار,تلفزيون ذكي,واي فاي عالي السرعة,تكييف هواء,خزنة,قنوات دولية',
 1);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(1, 'Presidential Suite', 'الجناح الرئاسي', 'presidential-suite',
 'The Presidential Suite offers unparalleled luxury with a separate master bedroom, a grand living room, a private office, and a stunning terrace with city views.',
 'يقدم الجناح الرئاسي فخامة لا مثيل لها مع غرفة نوم رئيسية منفصلة وصالة معيشة فسيحة ومكتب خاص وتراس رائع مطل على المدينة.',
 'An arena of pomp and grandeur',
 'فخامة لا مثيل لها',
 380.00, 'USD', 150, 2, 2, 'City and Garden View', 'إطلالة على المدينة والحديقة',
 1, 1, 'images/presidential-suite.jpg',
 'King Bed,Separate Living Room,Private Office,Private Terrace,Walk-in Wardrobe,Rain Shower,Bath Tub,Espresso Machine,Mini Bar,Smart TV,WiFi,24hr Butler',
 'سرير كينج,صالة معيشة منفصلة,مكتب خاص,تراس خاص,غرفة خلع ملابس,دش مطري,بانيو,ماكينة إسبريسو,ميني بار,تلفزيون ذكي,واي فاي,مرافق 24 ساعة',
 2);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(1, 'Executive Suite', 'الجناح التنفيذي', 'executive-suite',
 'Designed for the discerning business traveler, the Executive Suite combines contemporary elegance with functional workspace and premium amenities.',
 'صمم للمسافر التجاري المميز، يجمع الجناح التنفيذي بين الأناقة المعاصرة ومساحة عمل وظيفية ووسائل راحة متميزة.',
 'Perfect for the business elite',
 'مثالي لنخبة رجال الأعمال',
 280.00, 'USD', 100, 2, 1, 'City View', 'إطلالة على المدينة',
 1, 1, 'images/executive-suite.jpg',
 'King or Twin Beds,Work Desk,Ergonomic Chair,Smart TV,High-Speed WiFi,Mini Bar,Coffee Machine,Rain Shower,Air Conditioning,Safe,Ironing Board',
 'سرير كينج أو توأم,مكتب عمل,كرسي مريح,تلفزيون ذكي,واي فاي عالي السرعة,ميني بار,ماكينة قهوة,دش مطري,تكييف هواء,خزنة,طاولة كي',
 3);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(1, 'Junior Suite', 'الجناح الصغير', 'junior-suite',
 'The Junior Suite is a spacious and elegantly furnished room with a separate seating area, offering comfort and style for guests seeking a touch of luxury.',
 'الجناح الصغير غرفة فسيحة مفروشة بأناقة مع منطقة جلوس منفصلة توفر الراحة والأسلوب للضيوف.',
 'Elegant comfort with a separate seating area',
 'راحة أنيقة مع منطقة جلوس منفصلة',
 180.00, 'USD', 75, 2, 1, 'Garden View', 'إطلالة على الحديقة',
 1, 1, 'images/junior-suite.jpg',
 'King Bed,Separate Seating Area,Mini Bar,Smart TV,WiFi,Air Conditioning,Rain Shower,Safe,Coffee Machine',
 'سرير كينج,منطقة جلوس منفصلة,ميني بار,تلفزيون ذكي,واي فاي,تكييف هواء,دش مطري,خزنة,ماكينة قهوة',
 4);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(2, 'Deluxe Room Front View', 'غرفة ديلوكس إطلالة أمامية', 'deluxe-front',
 'Our Deluxe Front View rooms offer stunning views of the Haddah district from modern, elegantly appointed rooms with premium amenities.',
 'تقدم غرف الديلوكس ذات الإطلالة الأمامية إطلالات رائعة على منطقة حدة من غرف حديثة أنيقة مع وسائل راحة متميزة.',
 'Stunning views of Haddah district',
 'إطلالات رائعة على منطقة حدة',
 130.00, 'USD', 55, 2, 1, 'Front City View', 'إطلالة أمامية على المدينة',
 1, 1, 'images/deluxe-front.jpg',
 'Queen Bed,Smart TV,WiFi,Air Conditioning,Mini Bar,Safe,Coffee Machine,Rain Shower',
 'سرير كوين,تلفزيون ذكي,واي فاي,تكييف هواء,ميني بار,خزنة,ماكينة قهوة,دش مطري',
 5);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(2, 'Deluxe Room Back View', 'غرفة ديلوكس إطلالة خلفية', 'deluxe-back',
 'These comfortable Deluxe rooms feature garden and pool views, creating a serene atmosphere for relaxation after a day of business or leisure.',
 'تتميز غرف الديلوكس المريحة هذه بإطلالات على الحديقة والمسبح مما يخلق جواً هادئاً للاسترخاء.',
 'Serene garden and pool views',
 'إطلالات هادئة على الحديقة والمسبح',
 120.00, 'USD', 50, 2, 1, 'Garden and Pool View', 'إطلالة على الحديقة والمسبح',
 1, 0, 'images/deluxe-back.jpg',
 'Queen Bed,Smart TV,WiFi,Air Conditioning,Mini Bar,Safe,Coffee Machine',
 'سرير كوين,تلفزيون ذكي,واي فاي,تكييف هواء,ميني بار,خزنة,ماكينة قهوة',
 6);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(3, 'Single Room Premium', 'الغرفة الفردية المميزة', 'single-premium',
 'A premium single room with all essential modern amenities for solo travelers seeking quality and comfort at Al Bustan.',
 'غرفة فردية مميزة بجميع وسائل الراحة الحديثة الأساسية للمسافرين المنفردين الساعين إلى الجودة والراحة في البستان.',
 'Premium comfort for the solo traveler',
 'راحة مميزة للمسافر المنفرد',
 90.00, 'USD', 35, 1, 0, 'City View', 'إطلالة على المدينة',
 1, 0, 'images/single-premium.jpg',
 'Single Bed,Smart TV,WiFi,Air Conditioning,Safe,Work Desk,Shower',
 'سرير فردي,تلفزيون ذكي,واي فاي,تكييف هواء,خزنة,مكتب,دش',
 7);

INSERT INTO `rooms` (`category_id`,`name_en`,`name_ar`,`slug`,`description_en`,`description_ar`,`short_desc_en`,`short_desc_ar`,`price_per_night`,`currency`,`size_sqm`,`capacity_adults`,`capacity_children`,`view_type_en`,`view_type_ar`,`is_available`,`is_featured`,`main_image`,`amenities_en`,`amenities_ar`,`sort_order`) VALUES
(3, 'Single Room Economic', 'الغرفة الفردية الاقتصادية', 'single-economic',
 'A well-appointed, budget-friendly room ideal for solo travelers who want value without compromising on cleanliness and essential amenities.',
 'غرفة جيدة التجهيز وبأسعار مناسبة مثالية للمسافرين المنفردين الذين يريدون قيمة مقابل المال.',
 'Value for money for solo travelers',
 'قيمة مقابل المال للمسافرين المنفردين',
 65.00, 'USD', 28, 1, 0, 'Inner View', 'إطلالة داخلية',
 1, 0, 'images/single-economic.jpg',
 'Single Bed,TV,WiFi,Air Conditioning,Shower',
 'سرير فردي,تلفزيون,واي فاي,تكييف هواء,دش',
 8);

-- =============================================
-- Bookings Table
-- =============================================
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_ref` VARCHAR(20) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `room_id` INT NOT NULL,
  `guest_name` VARCHAR(150) NOT NULL,
  `guest_email` VARCHAR(150) NOT NULL,
  `guest_phone` VARCHAR(30) NOT NULL,
  `guest_nationality` VARCHAR(80) NULL,
  `adults` INT DEFAULT 1,
  `children` INT DEFAULT 0,
  `check_in` DATE NOT NULL,
  `check_out` DATE NOT NULL,
  `nights` INT NOT NULL,
  `room_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'USD',
  `payment_method` ENUM('pay_at_hotel','bank_transfer','credit_card') DEFAULT 'pay_at_hotel',
  `status` ENUM('pending','confirmed','checked_in','checked_out','cancelled') DEFAULT 'pending',
  `special_requests` TEXT NULL,
  `admin_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Menu Categories Table
-- =============================================
CREATE TABLE IF NOT EXISTS `menu_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(100) NOT NULL,
  `name_ar` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu_categories` (`name_en`, `name_ar`, `icon`, `sort_order`) VALUES
('Breakfast', 'الإفطار', 'fa-coffee', 1),
('Starters', 'المقبلات', 'fa-leaf', 2),
('Main Course', 'الأطباق الرئيسية', 'fa-utensils', 3),
('Grills and BBQ', 'المشويات', 'fa-fire', 4),
('Salads', 'السلطات', 'fa-seedling', 5),
('Desserts', 'الحلويات', 'fa-ice-cream', 6),
('Hot Drinks', 'المشروبات الساخنة', 'fa-mug-hot', 7),
('Cold Drinks', 'المشروبات الباردة', 'fa-glass-water', 8),
('Fresh Juices', 'العصائر الطازجة', 'fa-blender', 9);

-- =============================================
-- Menu Items Table
-- =============================================
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name_en` VARCHAR(150) NOT NULL,
  `name_ar` VARCHAR(150) NOT NULL,
  `description_en` TEXT NULL,
  `description_ar` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'USD',
  `image` VARCHAR(255) NULL,
  `is_vegetarian` TINYINT(1) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_available` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `menu_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu_items` (`category_id`,`name_en`,`name_ar`,`description_en`,`description_ar`,`price`,`is_vegetarian`,`is_featured`) VALUES
(1,'American Breakfast','فطور أمريكي','Eggs your way, crispy bacon, sausages, toast, grilled tomatoes, and orange juice','بيض على طريقتك، لحم مقدد مقرمش، نقانق، توست، طماطم مشوية، وعصير برتقال',12.00,0,1),
(1,'Continental Breakfast','فطور قاري','Fresh pastries, butter, assorted jams, yogurt, seasonal fruits, and coffee or tea','معجنات طازجة، زبدة، مربيات متنوعة، زبادي، فواكه موسمية، قهوة أو شاي',9.00,1,0),
(1,'Arabic Breakfast','فطور عربي','Hummus, foul medames, labneh, white cheese, olives, honey, butter, and fresh bread','حمص، فول مدمس، لبنة، جبن أبيض، زيتون، عسل، زبدة وخبز طازج',10.00,1,1),
(2,'Hummus','حمص','Freshly made hummus with olive oil, paprika and pine nuts. Served with warm bread','حمص طازج بزيت الزيتون والفلفل الحلو والصنوبر. يقدم مع خبز دافئ',5.00,1,1),
(2,'Stuffed Grape Leaves','ورق عنب محشي','Vine leaves stuffed with rice, parsley, tomatoes and spices, served with lemon','ورق عنب محشو بالأرز والبقدونس والطماطم والتوابل، يقدم مع الليمون',7.00,1,0),
(2,'Spring Rolls','سبرينج رولز','Crispy spring rolls filled with vegetables and served with sweet chili sauce','سبرينج رولز مقرمشة محشوة بالخضروات وتقدم مع صلصة الفلفل الحلو',6.00,1,0),
(3,'Grilled Chicken','دجاج مشوي','Tender grilled chicken breast marinated in herbs, served with rice and vegetables','صدر دجاج مشوي طري متبل بالأعشاب الطازجة، مع الأرز والخضروات',16.00,0,1),
(3,'Beef Steak','ستيك لحم بقري','Premium beef steak cooked to your preference, served with mashed potatoes and sauce','ستيك لحم بقري فاخر على حسب درجة النضج، مع بطاطس مهروسة وصلصة',28.00,0,1),
(3,'Grilled Fish','سمك مشوي','Fresh whole fish grilled with garlic butter and herbs, served with seasoned rice','سمكة طازجة كاملة مشوية بالزبدة والثوم والأعشاب، مع أرز متبل',20.00,0,0),
(3,'Pasta Carbonara','باستا كاربونارا','Al dente pasta with creamy egg sauce, bacon, parmesan cheese and black pepper','باستا طازجة مع صلصة البيض الكريمية، لحم مقدد، جبن بارميزان وفلفل أسود',14.00,0,0),
(6,'Umm Ali','أم علي','Traditional Egyptian bread pudding with nuts, raisins and warm cream','حلوى أم علي المصرية التقليدية مع المكسرات والزبيب والكريمة الدافئة',7.00,1,1),
(6,'Chocolate Lava Cake','كيك اللافا بالشوكولاتة','Warm chocolate cake with a molten center, served with vanilla ice cream','كيك شوكولاتة دافئ بمركز منصهر، يقدم مع آيس كريم الفانيليا',8.00,1,1),
(7,'Arabic Coffee','قهوة عربية','Traditional Arabic coffee with cardamom and saffron','قهوة عربية تقليدية بالهيل والزعفران',3.00,1,1),
(7,'Cappuccino','كابتشينو','Rich espresso with steamed milk and silky foam','إسبريسو غني مع حليب مبخر ورغوة ناعمة',4.00,1,0),
(9,'Mango Juice','عصير مانجو','Fresh mango blended with a hint of lime','مانجو طازجة ممزوجة مع لمسة ليمون',5.00,1,1),
(9,'Mix Fruits','عصير فواكه مشكل','Seasonal fruit blend with pomegranate and berries','مزيج فواكه موسمية مع الرمان والتوت',5.50,1,0);

-- =============================================
-- Offers / Promotions Table
-- =============================================
CREATE TABLE IF NOT EXISTS `offers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title_en` VARCHAR(200) NOT NULL,
  `title_ar` VARCHAR(200) NOT NULL,
  `description_en` TEXT NULL,
  `description_ar` TEXT NULL,
  `discount_type` ENUM('percentage','fixed') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `promo_code` VARCHAR(30) NULL,
  `image` VARCHAR(255) NULL,
  `valid_from` DATE NULL,
  `valid_to` DATE NULL,
  `min_nights` INT DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `offers` (`title_en`,`title_ar`,`description_en`,`description_ar`,`discount_type`,`discount_value`,`promo_code`,`valid_from`,`valid_to`,`min_nights`,`is_active`,`is_featured`) VALUES
('Stay 3 Pay 2','أقم 3 ليالٍ وادفع ليلتين','Book 3 consecutive nights and get the third night absolutely free. Valid for all room categories.','احجز 3 ليالٍ متتالية واحصل على الليلة الثالثة مجاناً. صالح لجميع فئات الغرف.','percentage',33.33,'STAY3PAY2','2026-01-01','2026-12-31',3,1,1),
('Weekend Escape 20 Percent Off','عرض نهاية الأسبوع خصم 20 بالمئة','Enjoy a relaxing weekend getaway with 20% off on Friday and Saturday night stays.','استمتع بعطلة نهاية أسبوع مريحة مع خصم 20% على الإقامة ليالي الجمعة والسبت.','percentage',20.00,'WEEKEND20','2026-01-01','2026-12-31',1,1,1),
('Early Bird Discount','الحجز المبكر','Book your stay 30 days in advance and save 15% on your total booking.','احجز إقامتك قبل 30 يوماً ووفر 15% على إجمالي حجزك.','percentage',15.00,'EARLY15','2026-01-01','2026-12-31',2,1,0),
('Honeymoon Package','باقة شهر العسل','Special honeymoon package including suite upgrade, flowers, and breakfast for two.','باقة شهر عسل مميزة تشمل ترقية للجناح وزهور وإفطار لشخصين.','fixed',50.00,'HONEYMOON','2026-01-01','2026-12-31',2,1,1);

-- =============================================
-- Gallery Table
-- =============================================
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title_en` VARCHAR(150) NULL,
  `title_ar` VARCHAR(150) NULL,
  `image` VARCHAR(255) NOT NULL,
  `category` ENUM('rooms','restaurant','facilities','events','exterior','interior') DEFAULT 'interior',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Meeting Packages Table
-- =============================================
CREATE TABLE IF NOT EXISTS `meetings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_en` VARCHAR(150) NOT NULL,
  `name_ar` VARCHAR(150) NOT NULL,
  `description_en` TEXT NULL,
  `description_ar` TEXT NULL,
  `capacity` INT NOT NULL,
  `area_sqm` INT NULL,
  `price_per_day` DECIMAL(10,2) NULL,
  `setup_types_en` TEXT,
  `setup_types_ar` TEXT,
  `amenities_en` TEXT,
  `amenities_ar` TEXT,
  `image` VARCHAR(255) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `meetings` (`name_en`,`name_ar`,`description_en`,`description_ar`,`capacity`,`area_sqm`,`price_per_day`,`setup_types_en`,`setup_types_ar`,`amenities_en`,`amenities_ar`) VALUES
('Grand Ballroom', 'قاعة البلورة الكبرى',
 'Our Grand Ballroom is the premier venue for large-scale events, conferences, and lavish wedding celebrations with state-of-the-art AV equipment.',
 'قاعة البلورة الكبرى هي المكان المثالي للفعاليات الكبيرة والمؤتمرات والحفلات الزفاف الفاخرة مع معدات صوت وصورة متطورة.',
 500, 800, 2500.00,
 'Theater,Classroom,U-Shape,Banquet,Cocktail',
 'مسرح,فصل دراسي,على شكل حرف U,بنكيت,كوكتيل',
 'HD Projector,Sound System,Microphones,High-Speed WiFi,Stage,LED Lighting,Dedicated Staff,Catering Available',
 'بروجكتور عالي الدقة,نظام صوت,مايكروفونات,واي فاي عالي السرعة,مسرح,إضاءة LED,طاقم مخصص,خدمة تقديم الطعام');

INSERT INTO `meetings` (`name_en`,`name_ar`,`description_en`,`description_ar`,`capacity`,`area_sqm`,`price_per_day`,`setup_types_en`,`setup_types_ar`,`amenities_en`,`amenities_ar`) VALUES
('Executive Conference Room', 'قاعة المؤتمرات التنفيذية',
 'State-of-the-art conference room ideal for corporate meetings, board meetings, and training sessions with full audiovisual capabilities.',
 'قاعة مؤتمرات متطورة مثالية للاجتماعات المؤسسية واجتماعات مجالس الإدارة والتدريب مع إمكانيات صوت وصورة كاملة.',
 50, 120, 800.00,
 'Boardroom,U-Shape,Classroom',
 'غرفة اجتماعات,على شكل حرف U,فصل دراسي',
 'Smart Board,Video Conferencing,High-Speed WiFi,Projector,Whiteboard,Stationery,Coffee Break Service',
 'سبورة ذكية,مؤتمرات فيديو,واي فاي عالي السرعة,بروجكتور,لوح أبيض,أدوات كتابة,خدمة استراحة القهوة');

INSERT INTO `meetings` (`name_en`,`name_ar`,`description_en`,`description_ar`,`capacity`,`area_sqm`,`price_per_day`,`setup_types_en`,`setup_types_ar`,`amenities_en`,`amenities_ar`) VALUES
('VIP Meeting Room', 'غرفة الاجتماعات VIP',
 'An intimate and luxuriously appointed meeting room for executive gatherings, negotiations, and private events.',
 'غرفة اجتماعات مريحة ومجهزة بفخامة للتجمعات التنفيذية والمفاوضات والفعاليات الخاصة.',
 15, 45, 400.00,
 'Boardroom',
 'غرفة اجتماعات',
 'Smart TV,Video Conferencing,Whiteboard,WiFi,Butler Service,Refreshments',
 'تلفزيون ذكي,مؤتمرات فيديو,لوح أبيض,واي فاي,خدمة مرافق,مرطبات');

-- =============================================
-- Messages (Contact Form) Table
-- =============================================
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `subject` VARCHAR(200) NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('general','booking_inquiry','meeting_inquiry','complaint','other') DEFAULT 'general',
  `is_read` TINYINT(1) DEFAULT 0,
  `admin_reply` TEXT NULL,
  `replied_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Testimonials Table
-- =============================================
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `guest_name` VARCHAR(100) NOT NULL,
  `country_en` VARCHAR(80) NULL,
  `country_ar` VARCHAR(80) NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `review_en` TEXT NOT NULL,
  `review_ar` TEXT NULL,
  `avatar` VARCHAR(255) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `testimonials` (`guest_name`,`country_en`,`country_ar`,`rating`,`review_en`,`review_ar`,`is_active`) VALUES
('Ahmed Al-Rashidi','Saudi Arabia','المملكة العربية السعودية',5,'Absolutely magnificent hotel. The service was impeccable and the Royal Suite exceeded all expectations. Will definitely return!','فندق رائع بالمطلق. كانت الخدمة لا تشوبها شائبة والجناح الملكي فاق كل التوقعات. سنعود بالتأكيد!',1),
('Sarah Thompson','United Kingdom','المملكة المتحدة',5,'From the moment we arrived, we were treated like royalty. The staff is incredibly professional and the facilities are world-class.','من لحظة وصولنا، عوملنا كالملوك. الموظفون محترفون للغاية والمرافق على مستوى عالمي.',1),
('Mohammed Al-Kindi','United Arab Emirates','الإمارات العربية المتحدة',5,'The food in the restaurant is absolutely divine. The Arabic breakfast alone is worth the stay. Highly recommended for business travelers.','الطعام في المطعم رائع بالفعل. وجبة الإفطار العربية وحدها تستحق الإقامة. أوصي به بشدة للمسافرين في رحلات العمل.',1),
('Elena Volkov','Russia','روسيا',4,'Beautiful hotel with excellent amenities. The location in Haddah area is very convenient. Staff was very helpful and friendly.','فندق جميل مع وسائل راحة ممتازة. الموقع في منطقة حدة مريح جداً. كان الموظفون مفيدين وودودين جداً.',1);

-- =============================================
-- Settings Table
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT NULL,
  `type` ENUM('text','textarea','image','boolean','number') DEFAULT 'text',
  `label_en` VARCHAR(150) NULL,
  `label_ar` VARCHAR(150) NULL,
  `group` VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`,`value`,`type`,`label_en`,`label_ar`,`group`) VALUES
('hotel_name_en','Al Bustan Luxurious Suites','text','Hotel Name (English)','اسم الفندق (إنجليزي)','general'),
('hotel_name_ar','البستان للأجنحة الفاخرة','text','Hotel Name (Arabic)','اسم الفندق (عربي)','general'),
('hotel_tagline_en','Where Luxury Meets Tradition','text','Tagline (English)','الشعار (إنجليزي)','general'),
('hotel_tagline_ar','حيث تلتقي الفخامة بالأصالة','text','Tagline (Arabic)','الشعار (عربي)','general'),
('hotel_phone','+967 1 433 200','text','Phone Number','رقم الهاتف','contact'),
('hotel_email','info@albustansuites.net','text','Email Address','البريد الإلكتروني','contact'),
('hotel_address_en','Haddah Area, Off Iran Street, Sanaa, Yemen','textarea','Address (English)','العنوان (إنجليزي)','contact'),
('hotel_address_ar','منطقة حدة، شارع إيران، صنعاء، اليمن','textarea','Address (Arabic)','العنوان (عربي)','contact'),
('hotel_lat','15.3694','text','Latitude','خط العرض','contact'),
('hotel_lng','44.1910','text','Longitude','خط الطول','contact'),
('facebook_url','https://facebook.com/albustansuites','text','Facebook URL','رابط فيسبوك','social'),
('instagram_url','https://instagram.com/albustansuites','text','Instagram URL','رابط انستغرام','social'),
('twitter_url','https://twitter.com/albustansuites','text','Twitter URL','رابط تويتر','social'),
('whatsapp_number','+96714332000','text','WhatsApp Number','رقم واتساب','contact'),
('check_in_time','14:00','text','Check-in Time','وقت تسجيل الدخول','policy'),
('check_out_time','12:00','text','Check-out Time','وقت تسجيل المغادرة','policy'),
('currency','USD','text','Default Currency','العملة الافتراضية','policy'),
('currency_symbol','$','text','Currency Symbol','رمز العملة','policy'),
('tax_rate','10','number','Tax Rate (%)','معدل الضريبة (%)','policy'),
('meta_description_en','Al Bustan Luxurious Suites - 5 Star Hotel in Haddah, Sanaa. Book direct for best rates.','textarea','Meta Description (EN)','وصف الميتا (إنجليزي)','seo'),
('meta_description_ar','البستان للأجنحة الفاخرة - فندق 5 نجوم في حدة، صنعاء. احجز مباشرة للحصول على أفضل الأسعار.','textarea','Meta Description (AR)','وصف الميتا (عربي)','seo');

SET FOREIGN_KEY_CHECKS = 1;
