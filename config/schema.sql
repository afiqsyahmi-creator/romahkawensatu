-- =============================================================
--  romahkawensatu — Studio Rental & Booking System
--  MySQL 8.x  ·  InnoDB  ·  utf8mb4
-- -------------------------------------------------------------
--  Auth model: ADMIN is the only login (email + password_hash).
--  Customers do NOT log in — they are captured at booking time.
--
--  Changes from the original draft:
--   • Removed PACKAGE table  -> a "package" in the UI is a STUDIO,
--     priced hourly (hourly_rate x hours). Re-add only if you ever
--     want fixed bundles (then give it a studio_id FK).
--   • Removed customer password -> only admin authenticates.
--   • Added BOOKING_ADDON junction -> attaches the photographer
--     (and any future add-on) to a booking, with a price snapshot.
--   • Fixed GALLERY -> now stores studio_id + image_path + caption.
--   • Money is DECIMAL(10,2); images/receipts store PATHS, not files.
--   • Passwords store a bcrypt HASH, never plaintext.
--   • booking has an index on (studio_id, booking_date) for the
--     overlap check that runs on every "Confirm booking".
-- =============================================================

CREATE DATABASE IF NOT EXISTS romahkawensatu
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE romahkawensatu;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payment;
DROP TABLE IF EXISTS booking_addon;
DROP TABLE IF EXISTS booking;
DROP TABLE IF EXISTS gallery;
DROP TABLE IF EXISTS addon;
DROP TABLE IF EXISTS studio;
DROP TABLE IF EXISTS customer;
DROP TABLE IF EXISTS admin;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
--  ADMIN  — the only authenticated user
-- =============================================================
CREATE TABLE admin (
  admin_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,            -- bcrypt/argon2 hash ONLY
  full_name     VARCHAR(120) NOT NULL,
  phone_number  VARCHAR(20),
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================
--  CUSTOMER  — captured at booking time, no login
-- =============================================================
CREATE TABLE customer (
  customer_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(120) NOT NULL,
  phone_number  VARCHAR(20)  NOT NULL,
  email         VARCHAR(150),
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_customer_email (email),
  INDEX idx_customer_phone (phone_number)
) ENGINE=InnoDB;

-- =============================================================
--  STUDIO  — the 7 themed sets (shown as "packages" in the UI)
-- =============================================================
CREATE TABLE studio (
  studio_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  studio_name VARCHAR(120)  NOT NULL,
  capacity    INT UNSIGNED,
  description VARCHAR(500),
  hourly_rate DECIMAL(10,2) NOT NULL,
  image       VARCHAR(255),                       -- cover image PATH/URL
  status      ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================
--  ADDON  — catalogue of extras (e.g. photographer)
-- =============================================================
CREATE TABLE addon (
  addon_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  addon_name  VARCHAR(120)  NOT NULL,
  addon_type  VARCHAR(50),
  price       DECIMAL(10,2) NOT NULL,
  description VARCHAR(500),
  status      ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

-- =============================================================
--  GALLERY  — multiple photos per studio
-- =============================================================
CREATE TABLE gallery (
  gallery_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  studio_id  INT UNSIGNED  NOT NULL,
  image_path VARCHAR(255)  NOT NULL,              -- PATH/URL, not the file
  caption    VARCHAR(200),
  sort_order INT UNSIGNED  NOT NULL DEFAULT 0,
  CONSTRAINT fk_gallery_studio FOREIGN KEY (studio_id)
    REFERENCES studio(studio_id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_gallery_studio (studio_id)
) ENGINE=InnoDB;

-- =============================================================
--  BOOKING  — one row per reservation
--  total_price is a SNAPSHOT of what was charged (keep it even
--  if hourly_rate changes later).
-- =============================================================
CREATE TABLE booking (
  booking_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id    INT UNSIGNED  NOT NULL,
  studio_id      INT UNSIGNED  NOT NULL,
  booking_date   DATE          NOT NULL,
  start_time     TIME          NOT NULL,
  end_time       TIME          NOT NULL,
  total_price    DECIMAL(10,2) NOT NULL,
  booking_status ENUM('pending','confirmed','completed','cancelled')
                 NOT NULL DEFAULT 'pending',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_customer FOREIGN KEY (customer_id)
    REFERENCES customer(customer_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_booking_studio FOREIGN KEY (studio_id)
    REFERENCES studio(studio_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_booking_time CHECK (end_time > start_time),
  INDEX idx_booking_slot (studio_id, booking_date),   -- overlap check
  INDEX idx_booking_customer (customer_id),
  INDEX idx_booking_status (booking_status)
) ENGINE=InnoDB;

-- =============================================================
--  BOOKING_ADDON  — junction: which add-ons a booking includes
-- =============================================================
CREATE TABLE booking_addon (
  booking_id       INT UNSIGNED  NOT NULL,
  addon_id         INT UNSIGNED  NOT NULL,
  quantity         INT UNSIGNED  NOT NULL DEFAULT 1,
  price_at_booking DECIMAL(10,2) NOT NULL,            -- snapshot
  PRIMARY KEY (booking_id, addon_id),
  CONSTRAINT fk_ba_booking FOREIGN KEY (booking_id)
    REFERENCES booking(booking_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_ba_addon FOREIGN KEY (addon_id)
    REFERENCES addon(addon_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =============================================================
--  PAYMENT
--  Allowed payment_method values:
--   Online Banking (FPX) | Credit / Debit Card | Touch 'n Go eWallet
--   | GrabPay | Boost | DuitNow QR
--  NEVER store card numbers/CVV — only the method + gateway reference.
-- =============================================================
CREATE TABLE payment (
  payment_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id        INT UNSIGNED  NOT NULL,
  payment_date      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  amount            DECIMAL(10,2) NOT NULL,
  payment_method    VARCHAR(50)   NOT NULL,
  gateway_reference VARCHAR(100),                     -- txn id from gateway
  receipt_path      VARCHAR(255),                     -- uploaded file PATH
  payment_status    ENUM('pending','paid','failed','refunded')
                    NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_payment_booking FOREIGN KEY (booking_id)
    REFERENCES booking(booking_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_payment_booking (booking_id)
) ENGINE=InnoDB;

-- =============================================================
--  SEED DATA
-- =============================================================

-- Admin login — email: admin@romahkawensatu.com  password: RomahAdmin@123
INSERT INTO admin (username, email, password_hash, full_name, phone_number) VALUES
('admin', 'admin@romahkawensatu.com',
 '$2b$12$axjj.MIx1kg8S6Z9sD7pk.WcsfwaPxyK//QhGkTi7w/oArHQAw9pu',
 'Studio Administrator', '012-3456789');

-- Studios (rates exactly as your pricing)
INSERT INTO studio (studio_name, capacity, description, hourly_rate, image, status) VALUES
('Harry Potter Library', 8, 'Antique library, warm wood, tall shelves', 150.00, 'images/library.jpg',  'active'),
('Rattan Studio',        6, 'Woven rattan, natural light, airy',        150.00, 'images/rattan.jpg',   'active'),
('Firepit Studio',       6, 'Cozy hearth, rustic textures',             150.00, 'images/firepit.jpg',  'active'),
('Retro Cafe Studio',    8, 'Vintage diner, retro signage',             180.00, 'images/retrocafe.jpg','active'),
('Bohemian Studio',      6, 'Eclectic textiles, plants, earthy tones',  100.00, 'images/bohemian.jpg', 'active'),
('Barber Studio',        4, 'Classic barbershop, leather & chrome',     150.00, 'images/barber.jpg',   'active'),
('Muji Studio',          6, 'Minimal, neutral, clean lines',            150.00, 'images/muji.jpg',     'active');

-- Add-ons
INSERT INTO addon (addon_name, addon_type, price, description, status) VALUES
('Photographer', 'photography', 800.00, 'Professional photographer, 2 hours included', 'active'),
('Makeup Artist','styling',     350.00, 'On-site makeup artist per session',           'active');

-- Gallery (PATHS only — drop real files in /images and point here)
INSERT INTO gallery (studio_id, image_path, caption, sort_order) VALUES
(1, 'images/library-1.jpg', 'Reading nook',        1),
(1, 'images/library-2.jpg', 'Tall shelves',        2),
(2, 'images/rattan-1.jpg',  'Daylight corner',     1),
(3, 'images/firepit-1.jpg', 'Hearth setup',        1),
(4, 'images/retrocafe-1.jpg','Counter & stools',   1),
(5, 'images/bohemian-1.jpg','Textiles & plants',   1),
(6, 'images/barber-1.jpg',  'Barber chair',        1),
(7, 'images/muji-1.jpg',    'Minimal set',         1);

-- Sample customers
INSERT INTO customer (customer_name, phone_number, email) VALUES
('Aisyah Rahman', '011-2233445', 'aisyah@example.com'),
('Lim Wei Jie',   '012-9988776', 'weijie@example.com'),
('Nurul Huda',    '013-5566778', 'nurul@example.com');

-- Sample bookings (dates relative to today so the calendar always has data)
-- B1: Library, +2d, 10:00-13:00 (3h x150) = 450, confirmed
INSERT INTO booking (customer_id, studio_id, booking_date, start_time, end_time, total_price, booking_status) VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '13:00:00',  450.00, 'confirmed');
-- B2: Retro Cafe, +2d, 11:00-14:00 (3h x180) = 540, confirmed
INSERT INTO booking (customer_id, studio_id, booking_date, start_time, end_time, total_price, booking_status) VALUES
(2, 4, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '14:00:00',  540.00, 'confirmed');
-- B3: Bohemian, +5d, 14:00-18:00 (4h x100=400) + photographer 800 = 1200, confirmed
INSERT INTO booking (customer_id, studio_id, booking_date, start_time, end_time, total_price, booking_status) VALUES
(3, 5, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00:00', '18:00:00', 1200.00, 'confirmed');
-- B4: Rattan, +3d, 10:00-12:00 (2h x150) = 300, pending
INSERT INTO booking (customer_id, studio_id, booking_date, start_time, end_time, total_price, booking_status) VALUES
(1, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00',  300.00, 'pending');
-- B5: Firepit, -3d (past), 10:00-12:00 (2h x150) = 300, completed
INSERT INTO booking (customer_id, studio_id, booking_date, start_time, end_time, total_price, booking_status) VALUES
(2, 3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '10:00:00', '12:00:00',  300.00, 'completed');
-- B6: Barber, +7d, 13:00-16:00 (3h x150) = 450, cancelled
INSERT INTO booking (customer_id, studio_id, booking_date, start_time, end_time, total_price, booking_status) VALUES
(3, 6, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '13:00:00', '16:00:00',  450.00, 'cancelled');

-- B3 includes the photographer add-on
INSERT INTO booking_addon (booking_id, addon_id, quantity, price_at_booking) VALUES
(3, 1, 1, 800.00);

-- Payments
INSERT INTO payment (booking_id, amount, payment_method, gateway_reference, payment_status) VALUES
(1,  450.00, 'Online Banking (FPX)', 'FPX-1001', 'paid'),
(2,  540.00, 'Credit / Debit Card',  'CARD-1002','paid'),
(3, 1200.00, 'Touch ''n Go eWallet',  'TNG-1003', 'paid'),
(4,  300.00, 'DuitNow QR',            NULL,       'pending'),
(5,  300.00, 'Boost',                 'BST-1005', 'paid'),
(6,  450.00, 'GrabPay',               'GRB-1006', 'refunded');

-- =============================================================
--  HELPER VIEW — full booking list for the admin dashboard
-- =============================================================
CREATE OR REPLACE VIEW v_booking_details AS
SELECT b.booking_id, b.booking_date, b.start_time, b.end_time,
       b.total_price, b.booking_status,
       c.customer_name, c.phone_number, c.email,
       s.studio_name, s.hourly_rate,
       p.payment_status, p.payment_method
FROM booking b
JOIN customer c ON c.customer_id = b.customer_id
JOIN studio   s ON s.studio_id   = b.studio_id
LEFT JOIN payment p ON p.booking_id = b.booking_id;

-- =============================================================
--  REFERENCE QUERIES (for the backend — not executed here)
-- -------------------------------------------------------------
--  Overlap check (run before confirming; :params from the form).
--  If this returns any row, the slot clashes -> show the popup.
--
--    SELECT booking_id FROM booking
--    WHERE studio_id = :studio_id
--      AND booking_date = :date
--      AND booking_status IN ('pending','confirmed')
--      AND start_time < :new_end
--      AND :new_start < end_time;
--
--  Calendar for a clicked date (only confirmed slots block others):
--    SELECT s.studio_name, b.start_time, b.end_time
--    FROM booking b JOIN studio s ON s.studio_id = b.studio_id
--    WHERE b.booking_date = :date AND b.booking_status = 'confirmed'
--    ORDER BY s.studio_name, b.start_time;
--
--  Dashboard tiles:
--    -- Revenue (paid only)
--    SELECT COALESCE(SUM(amount),0) FROM payment WHERE payment_status='paid';
--    -- Bookings by status
--    SELECT booking_status, COUNT(*) FROM booking GROUP BY booking_status;
--    -- Today's bookings
--    SELECT * FROM v_booking_details WHERE booking_date = CURDATE();
-- =============================================================
