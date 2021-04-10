

ALTER TABLE `llx_bbc_vols`
    ADD `is_night` INT NOT NULL DEFAULT 0,
    ADD `is_static` INT NOT NULL DEFAULT 0,
    ADD `is_exam` INT NOT NULL DEFAULT 0,
    ADD `is_refresh` INT NOT NULL DEFAULT 0 COMMENT 'OPC';

CREATE TABLE `llx_bbc_pilots` (
    user_id TINYINT NOT NULL,
    pilot_licence_number VARCHAR(100),
    training_pilot_licence_number VARCHAR(100),
    last_training_flight_date DATE,
    is_pilot_class_a TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_pilot_class_b TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_pilot_class_c TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_pilot_class_d TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_pilot_gaz TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_medical_owner TINYINT UNSIGNED NOT NULL DEFAULT 0,
    end_medical_date DATE,
    start_medical_date DATE,
    medical_validity_duration TINYINT UNSIGNED NOT NULL DEFAULT 0,
    has_qualif_static TINYINT UNSIGNED NOT NULL DEFAULT 0,
    has_qualif_night TINYINT UNSIGNED NOT NULL DEFAULT 0,
    has_qualif_pro TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_opc_date DATE,
    last_pro_refresh_date DATE,
    has_qualif_instructor TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_instructor_refresh_date DATE,
    has_qualif_examinator TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_examinator_refresh_date DATE,
    has_radio TINYINT UNSIGNED NOT NULL DEFAULT 0,
    radio_licence_number VARCHAR(100),
    radio_licence_date DATE,
    has_training_first_help TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_training_first_help_date DATE,
    certification_number_training_first_help VARCHAR(100),
    has_training_fire TINYINT UNSIGNED NOT NULL DEFAULT 0,
    last_training_fire_date DATE,
    certification_number_training_fire VARCHAR(100),
    PRIMARY KEY (`user_id`)
) ENGINE = InnoDB;
































