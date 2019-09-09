ALTER TABLE `llx_bbc_vols`
    ADD `fk_project` INT NULL AFTER `order_id`,
    ADD INDEX `fk_flight_project` (`fk_project`),
    ADD CONSTRAINT `fk_flight_project` FOREIGN KEY (`fk_project`) REFERENCES `llx_projet`(`rowid`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD `entity` TINYINT NOT NULL DEFAULT '1',
    ADD `rowid` INT NOT NULL AFTER `idBBC_vols`,
    ADD `ref` INT NOT NULL COMMENT 'Not used but must be there for the project',
    ADD `fk_soc` INT NULL COMMENT 'Not used but must be there for the project',
    ADD INDEX `balloon_rowid` (`rowid`);

UPDATE llx_bbc_vols SET rowid = idBBC_vols, ref = idBBC_vols WHERE 1=1;

DELIMITER $$
CREATE TRIGGER `fill_flight_rowid` BEFORE INSERT ON `llx_bbc_vols`
FOR EACH ROW BEGIN

    declare next_id int default 0;

    select
        auto_increment into next_id
    from
        information_schema.tables
    where
        table_name = 'llx_bbc_vols'
        and table_schema = database();

    SET NEW.rowid = next_id;
    SET NEW.idBBC_vols = next_id;
    SET NEW.ref = next_id;

END $$