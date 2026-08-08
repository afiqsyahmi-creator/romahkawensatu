-- =============================================================
--  Migration 002 — Add-on upgrades: popular flag, selection type,
--  scarcity tracking, bundle offers, and booking bundle discounts.
--  Run after schema.sql (or migration_001).
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ──────────────────────────────────────────
--  1. New columns on `addon`
-- ──────────────────────────────────────────
ALTER TABLE addon
  ADD COLUMN selection_type  ENUM('toggle','quantity') NOT NULL DEFAULT 'toggle'
    COMMENT 'toggle = on/off switch; quantity = +/- stepper for multiples',
  ADD COLUMN is_popular      TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'Set TRUE by nightly cron for top-N most-booked add-ons in last 30d',
  ADD COLUMN max_per_booking INT UNSIGNED NOT NULL DEFAULT 1
    COMMENT 'Maximum quantity allowed per booking (only used when selection_type=quantity)',
  ADD COLUMN weekly_capacity INT UNSIGNED DEFAULT NULL
    COMMENT 'NULL = unlimited; else max units available per calendar week',
  ADD COLUMN weekly_booked   INT UNSIGNED NOT NULL DEFAULT 0
    COMMENT 'How many units of this add-on are already booked this week (refreshed by cron)';

-- ──────────────────────────────────────────
--  2. Bundle offers table
-- ──────────────────────────────────────────
CREATE TABLE bundle_offer (
  bundle_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  addon_id_1     INT UNSIGNED NOT NULL,
  addon_id_2     INT UNSIGNED NOT NULL,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00
    COMMENT 'Fixed RM discount applied when both add-ons are selected',
  is_active      TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bundle_a1 FOREIGN KEY (addon_id_1)
    REFERENCES addon(addon_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bundle_a2 FOREIGN KEY (addon_id_2)
    REFERENCES addon(addon_id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_bundle_active (is_active)
) ENGINE=InnoDB;

-- ──────────────────────────────────────────
--  3. Booking bundle discounts (applied discounts per booking)
-- ──────────────────────────────────────────
CREATE TABLE booking_bundle_discount (
  discount_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id     INT UNSIGNED NOT NULL,
  bundle_id      INT UNSIGNED NOT NULL,
  discount_amount DECIMAL(10,2) NOT NULL
    COMMENT 'Snapshot of the discount applied at booking time',
  CONSTRAINT fk_bbd_booking FOREIGN KEY (booking_id)
    REFERENCES booking(booking_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bbd_bundle FOREIGN KEY (bundle_id)
    REFERENCES bundle_offer(bundle_id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_bbd_booking (booking_id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ──────────────────────────────────────────
--  4. Update seed add-on data
-- ──────────────────────────────────────────
UPDATE addon SET
  selection_type  = 'toggle',
  max_per_booking = 1,
  weekly_capacity = 5,
  weekly_booked   = 0
WHERE addon_type = 'photography';

UPDATE addon SET
  selection_type  = 'toggle',
  max_per_booking = 1,
  weekly_capacity = 8,
  weekly_booked   = 0
WHERE addon_type = 'styling';

-- Add an "Extra Hour" add-on for testing quantity stepper
INSERT INTO addon (addon_name, addon_type, price, description, status, selection_type, max_per_booking, weekly_capacity, weekly_booked)
VALUES ('Extra Hour', 'time', 120.00, 'Add an extra hour to your studio session', 'active', 'quantity', 4, 20, 0);

-- Mark Photographer as popular for now (cron will update)
UPDATE addon SET is_popular = 1 WHERE addon_name = 'Photographer';

-- Bundle: Photographer + Makeup Artist = RM100 discount
INSERT INTO bundle_offer (addon_id_1, addon_id_2, discount_amount, is_active)
SELECT a1.addon_id, a2.addon_id, 100.00, 1
FROM addon a1, addon a2
WHERE a1.addon_name = 'Photographer' AND a2.addon_name = 'Makeup Artist';
