ALTER TABLE `llx_bbc_vols`
    ADD `fk_project` INT NULL AFTER `order_id`,
    ADD INDEX `fk_flight_project` (`fk_project`),
    ADD CONSTRAINT `fk_flight_project` FOREIGN KEY (`fk_project`) REFERENCES `llx_projet`(`rowid`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD `entity` TINYINT NOT NULL DEFAULT '1',
    ADD `rowid` INT NOT NULL AFTER `idBBC_vols`,
    ADD `ref` INT NOT NULL COMMENT 'Not used but must be there for the project',
    ADD `fk_soc` INT NULL COMMENT 'Not used but must be there for the project',
    ADD INDEX `balloon_rowid` (`rowid`);

UPDATE llx_bbc_vols SET rowid = idBBC_vols, ref = idBBC_vols;