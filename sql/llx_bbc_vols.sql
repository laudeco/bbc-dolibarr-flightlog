
-- -----------------------------------------------------
-- Table `BBC_vols`
-- -----------------------------------------------------

CREATE  TABLE IF NOT EXISTS `llx_bbc_vols` (
  `idBBC_vols` INT NOT NULL AUTO_INCREMENT ,
  `date` DATE NULL ,
  `lieuD` VARCHAR(45) NULL ,
  `lieuA` VARCHAR(45) NULL ,
  `heureD` TIME NULL ,
  `heureA` TIME NULL ,
  `BBC_ballons_idBBC_ballons` INT NOT NULL ,
  `nbrPax` VARCHAR(45) NULL ,
  `remarque` LONGTEXT NULL ,
  `incidents` LONGTEXT NULL ,
  `fk_type` INT NOT NULL ,
  `fk_pilot` INT NOT NULL ,
  `fk_organisateur` INT NOT NULL ,
  `is_facture` INT NOT NULL DEFAULT 0, -- 0 : non encod� -- 1: encode
  `kilometers` INT NULL,
  `cost` INT NULL,
  `fk_receiver` INT NULL,
  `passenger_names` VARCHAR(255) DEFAULT '',
  PRIMARY KEY (`idBBC_vols`) )
ENGINE = InnoDB;