ALTER TABLE llx_bbc_pilots
    ADD COLUMN last_instructor_training_flight_date DATE,
    ADD COLUMN is_pilot_training TINYINT UNSIGNED NOT NULL DEFAULT 0;