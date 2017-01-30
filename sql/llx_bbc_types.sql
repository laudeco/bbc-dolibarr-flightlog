CREATE  TABLE IF NOT EXISTS `llx_bbc_types` (
  `idType` INT NOT NULL AUTO_INCREMENT ,
  `numero` INT NOT NULL,
  `nom` LONGTEXT NULL ,

  PRIMARY KEY (`idType`) )
ENGINE = InnoDB;