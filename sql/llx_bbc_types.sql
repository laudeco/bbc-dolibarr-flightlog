CREATE  TABLE IF NOT EXISTS `llx_bbc_types` (
  `idType` INT NOT NULL AUTO_INCREMENT ,
  `numero` INT NOT NULL,
  `nom` VARCHAR(64) NULL ,
  `active` TINYINT DEFAULT 1,
  `fkService` INT NULL,
  `is_mission` TINYINT(1) NOT NULL DEFAULT 0,
  `pax_required` TINYINT(1) NOT NULL DEFAULT 0,
  `billing_required` TINYINT(1) NOT NULL DEFAULT 0,
  `is_instruction` TINYINT(1) NOT NULL DEFAULT 0,
  `is_pilot_charged` TINYINT(1) NOT NULL DEFAULT 1,
  `points` INT NULL DEFAULT NULL,

  PRIMARY KEY (`idType`) )
ENGINE = InnoDB;
