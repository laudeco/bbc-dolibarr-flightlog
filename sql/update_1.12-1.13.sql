ALTER TABLE llx_bbc_flight_damages
    CHANGE flight_id flight_id INT DEFAULT NULL,
    ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `llx_bbc_flight_damages` SET created_at = (SELECT CAST(f.date AS DATETIME) from llx_bbc_vols as f WHERE f.idBBC_vols = flight_id AND f.date IS NOT NULL) WHERE flight_id > 0;
