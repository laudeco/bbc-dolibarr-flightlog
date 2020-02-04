<?php

/**
 * Prepare tabs for flight card
 *
 * @param Bbcvols $flight
 *
 * @return array
 */
function prepareFlightTabs(Bbcvols $flight)
{
    global $user, $langs, $conf;

    dol_include_once('/flightlog/flightlog.inc.php');

    $tabUrls = [
        DOL_URL_ROOT . '/flightlog/card.php?id=' . $flight->idBBC_vols,
        ($user->rights->flightlog->vol->financial || $user->id == $flight->fk_pilot) ? DOL_URL_ROOT . '/flightlog/card_tab_financial.php?id=' . $flight->idBBC_vols : '',
        ($user->rights->flightlog->vol->financial || $user->id == $flight->fk_pilot) ? DOL_URL_ROOT . '/flightlog/card_tab_damage.php?id=' . $flight->idBBC_vols : '',
        DOL_URL_ROOT . '/flightlog/card_tab_comments.php?id=' . $flight->idBBC_vols,
        DOL_URL_ROOT . '/flightlog/card_tab_follow.php?id=' . $flight->idBBC_vols,
    ];

    $tabNames = [
        'general',
        'financial',
        'damage',
        'comments',
        'follow',
    ];

    $tabTitle = [
        'Vol',
        'Finances',
        'Dommages',
        'Remarques',
        'Suivis',
    ];

    $tabCollection = new TabCollection();

    $countUrls = count($tabUrls);
    for ($i = 0; $i < $countUrls; $i++) {
        if (empty($tabUrls[$i])) {
            continue;
        }

        $tabCollection = $tabCollection->addTab(new Tab($tabTitle[$i], $tabNames[$i], $tabUrls[$i]));
    }

    $head = $tabCollection->toArray();
    $tabCount = count($tabCollection);

    complete_head_from_modules($conf, $langs, $flight, $head, $tabCount, 'bbcFlight');

    return $head;
}