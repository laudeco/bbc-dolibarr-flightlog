CREATE TABLE `llx_bbc_flights_orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `order_id` INT NOT NULL ,
  `flight_id` INT NOT NULL ,
  PRIMARY KEY (`id`)
);

ALTER TABLE `llx_bbc_flights_orders` ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `llx_commande`(`rowid`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `llx_bbc_flights_orders` ADD CONSTRAINT `fk_flight` FOREIGN KEY (`flight_id`) REFERENCES `llx_bbc_vols`(`idBBC_vols`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO llx_bbc_flights_orders (flight_id, order_id) SELECT idBBC_vols, order_id FROM llx_bbc_vols WHERE order_id IS NOT NULL;

ALTER TABLE `llx_bbc_vols` CHANGE `order_id` `order_id` INT(11) NULL DEFAULT NULL COMMENT 'DEPRECATED';