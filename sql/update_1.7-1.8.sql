CREATE TABLE `llx_bbc_flight_damages` (
    `rowid` INT NOT NULL AUTO_INCREMENT ,
    `flight_id` INT NOT NULL ,
    `billed` TINYINT NOT NULL DEFAULT '0' ,
    `amount` DECIMAL NOT NULL ,
    `vat` INT NOT NULL DEFAULT '21' ,

    PRIMARY KEY (`rowid`)
) ENGINE = InnoDB;


ALTER TABLE `llx_bbc_flight_damages` ADD CONSTRAINT `fk_flight` FOREIGN KEY (`flight_id`) REFERENCES `llx_bbc_vols`(`idBBC_vols`) ON DELETE CASCADE ON UPDATE CASCADE;