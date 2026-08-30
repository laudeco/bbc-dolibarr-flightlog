CREATE TABLE IF NOT EXISTS llx_bbc_balloon_type_product (
    fk_bbc_flight_type INT NOT NULL,
    fk_bbc_balloon INT NOT NULL,
    fk_product INT NOT NULL,
    PRIMARY KEY (fk_bbc_flight_type, fk_bbc_balloon, fk_product),
    UNIQUE INDEX fk_balloon_type (fk_bbc_flight_type, fk_bbc_balloon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
