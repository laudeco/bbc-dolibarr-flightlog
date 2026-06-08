ALTER TABLE llx_bbc_types
    ADD COLUMN remboursement_km    DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'Taux de remboursement kilométrique (€/km)',
    ADD COLUMN points_pilote       INT            NOT NULL DEFAULT 0 COMMENT 'Points attribués au pilote par vol',
    ADD COLUMN cout_pilote         DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'Coût facturé au pilote par vol (€)',
    ADD COLUMN visible_graphique   TINYINT        NOT NULL DEFAULT 1 COMMENT 'Visible dans les graphiques',
    ADD COLUMN visible_tableau     TINYINT        NOT NULL DEFAULT 1 COMMENT 'Visible dans le tableau récapitulatif',
    ADD COLUMN defraiement         DECIMAL(10, 2) NOT NULL DEFAULT 0 COMMENT 'Montant du défraiement par vol (€)';
