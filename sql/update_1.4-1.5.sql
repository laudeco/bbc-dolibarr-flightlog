-- normalized data
UPDATE
	llx_bbc_vols
SET
  llx_bbc_vols.BBC_ballons_idBBC_ballons = NULL
WHERE
  llx_bbc_vols.BBC_ballons_idBBC_ballons <= 0;


UPDATE
	llx_bbc_vols
SET
  llx_bbc_vols.fk_organisateur = NULL
WHERE
  llx_bbc_vols.fk_organisateur <= 0;

UPDATE
	llx_bbc_vols
SET
  llx_bbc_vols.fk_receiver = NULL
WHERE
  llx_bbc_vols.fk_receiver <= 0;

UPDATE
	llx_bbc_vols
SET
  llx_bbc_vols.order_id = NULL
WHERE
  llx_bbc_vols.order_id <= 0;

-- Foreign keys
ALTER TABLE `llx_bbc_vols`
  CHANGE `is_facture` `is_facture` TINYINT(1) UNSIGNED NULL DEFAULT '0',
  ADD INDEX(`is_facture`),
  ADD CONSTRAINT `fk_flight_pilot` FOREIGN KEY (`fk_pilot`) REFERENCES `llx_user`(`rowid`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk_flight_organisator` FOREIGN KEY (`fk_organisateur`) REFERENCES `llx_user`(`rowid`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk_flight_receiver` FOREIGN KEY (`fk_receiver`) REFERENCES `llx_user`(`rowid`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk_flight_order` FOREIGN KEY (`order_id`) REFERENCES `llx_commande`(`rowid`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk_flight_type` FOREIGN KEY (`fk_type`) REFERENCES `llx_bbc_types`(`idType`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk_flight_balloon` FOREIGN KEY (`BBC_ballons_idBBC_ballons`) REFERENCES `llx_bbc_ballons`(`rowid`) ON DELETE SET NULL ON UPDATE SET NULL;
