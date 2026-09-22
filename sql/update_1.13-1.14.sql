-- Flight types are now fully configurable from the administration page.
-- Every rule that used to be hardcoded on the flight type id becomes a column.
ALTER TABLE `llx_bbc_types`
    MODIFY `nom` VARCHAR(64) NULL,
    ADD COLUMN `is_mission` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `pax_required` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `billing_required` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `is_instruction` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `is_pilot_charged` TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN `points` INT NULL DEFAULT NULL;

-- T1 (sponsor) and T2 (baptism) are the missions of the club: they give points
-- to the pilots, require passengers and are the base of the expense notes.
UPDATE `llx_bbc_types` SET `is_mission` = 1, `pax_required` = 1, `is_pilot_charged` = 0 WHERE `numero` IN (1, 2);

-- T2 is the only type invoiced to a customer.
UPDATE `llx_bbc_types` SET `billing_required` = 1 WHERE `numero` = 2;

-- T6 is the instruction type: the organisator is the instructor.
UPDATE `llx_bbc_types` SET `is_instruction` = 1 WHERE `numero` = 6;

-- T5 (Chambley) was never charged to the pilot.
UPDATE `llx_bbc_types` SET `is_pilot_charged` = 0 WHERE `numero` = 5;

-- Best effort import of the points/amount previously stored in the configuration
-- (BBC_POINTS_BONUS_<numero>). When no value is found the type keeps NULL and the
-- module falls back on the constant and then on the price of the linked service.
-- Only a strictly positive value was taken into account before, an empty or null
-- value meaning "use the price of the service".
UPDATE `llx_bbc_types` AS t
    INNER JOIN `llx_const` AS c ON c.name = CONCAT('BBC_POINTS_BONUS_', t.numero)
    SET t.points = CAST(c.value AS SIGNED)
    WHERE c.value REGEXP '^[0-9]+$' AND CAST(c.value AS SIGNED) > 0;
