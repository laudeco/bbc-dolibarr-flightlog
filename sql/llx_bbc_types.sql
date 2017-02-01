CREATE  TABLE IF NOT EXISTS `llx_bbc_types` (
  `idType` INT NOT NULL AUTO_INCREMENT ,
  `numero` INT NOT NULL,
  `nom` VARCHAR(20) NULL ,
  `active` TINYINT DEFAULT 1,

  PRIMARY KEY (`idType`) )
ENGINE = InnoDB;