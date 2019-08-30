CREATE TABLE `llx_bbc_vols_orders` (
    `order_id` INT NOT NULL ,
    `flight_id` INT NOT NULL ,
    `nbr_passengers` INT NOT NULL ,
    PRIMARY KEY (`order_id`, `flight_id`)
) ENGINE = InnoDB;

ALTER TABLE `llx_bbc_vols_orders` ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `llx_commande`(`rowid`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `llx_bbc_vols_orders` ADD CONSTRAINT `fk_flight` FOREIGN KEY (`flight_id`) REFERENCES `llx_bbc_vols`(`idBBC_vols`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO
    llx_bbc_vols_orders (order_id, flight_id, nbr_passengers)

    SELECT
        llx_bbc_vols.order_id,
        llx_bbc_vols.idBBC_vols,
        llx_bbc_vols.nbrPax
    FROM
        llx_bbc_vols
    WHERE
        order_id IS NOT NULL;

