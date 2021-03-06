CREATE TABLE `llx_bbc_flight_damages` (
    `rowid` INT NOT NULL AUTO_INCREMENT ,
    `flight_id` INT NOT NULL ,
    `billed` TINYINT NOT NULL DEFAULT '0' ,
    `amount` DECIMAL(8,2) NOT NULL ,
    `vat` INT NOT NULL DEFAULT '21' ,
    `author_id` INT NOT NULL,
    PRIMARY KEY (`rowid`),
    INDEX(`flight_id`)
) ENGINE = InnoDB;


ALTER TABLE `llx_bbc_flight_damages`
    ADD FOREIGN KEY (`flight_id`) REFERENCES `llx_bbc_vols`(`idBBC_vols`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD FOREIGN KEY (`author_id`) REFERENCES `llx_user`(`rowid`) ON DELETE CASCADE ON UPDATE CASCADE;