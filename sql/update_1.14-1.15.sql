-- The kilometer allowance and the lump sum of a mission become configurable per
-- flight type. NULL keeps the value configured for the whole module
-- (BBC_FLIGHT_LOG_TAUX_REMB_KM and BBC_FLIGHT_LOG_UNIT_PRICE_MISSION).
ALTER TABLE `llx_bbc_types`
    ADD COLUMN `km_allowance` DECIMAL(10,4) NULL DEFAULT NULL,
    ADD COLUMN `mission_allowance` DECIMAL(10,4) NULL DEFAULT NULL;
