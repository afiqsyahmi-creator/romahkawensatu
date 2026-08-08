-- Migration 001: Add pax, event_type, and notes to booking table
ALTER TABLE booking
  ADD COLUMN pax INT UNSIGNED DEFAULT NULL AFTER end_time,
  ADD COLUMN event_type VARCHAR(100) DEFAULT NULL AFTER pax,
  ADD COLUMN notes TEXT DEFAULT NULL AFTER event_type;
