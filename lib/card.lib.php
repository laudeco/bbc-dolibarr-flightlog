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

    dol_include_once('/flightLog/class/card/Tab.php');
    dol_include_once('/flightLog/class/card/TabCollection.php');

    $tabUrls = [
        DOL_URL_ROOT . '/flightLog/card.php?id=' . $flight->idBBC_vols,
        ($user->rights->flightLog->vol->financial || $user->id == $flight->fk_pilot) ? DOL_URL_ROOT . '/flightLog/card_tab_financial.php?id=' . $flight->idBBC_vols : '',
        DOL_URL_ROOT . '/flightLog/card_tab_comments.php?id=' . $flight->idBBC_vols,
        DOL_URL_ROOT . '/flightLog/card_tab_follow.php?id=' . $flight->idBBC_vols,
    ];

    $tabNames = [
        'general',
        'financial',
        'comments',
        'follow',
    ];

    $tabTitle = [
        'Vol',
        'Finances',
        'Remarques',
        'Suivis',
    ];

    $tabCollection = new TabCollection();

    $countUrls = count($tabUrls);
    for ($i = 0; $i < $countUrls; $i++) {
        if (empty($tabUrls[$i])) {
            continue;
        }

        $tab = new Tab($tabTitle[$i], $tabNames[$i], $tabUrls[$i]);
        $tabCollection->addTab($tab);
    }

    $head = $tabCollection->toArray();
    $tabCount = count($tabCollection);

    complete_head_from_modules($conf, $langs, $flight, $head, $tabCount, 'bbcFlight');

    return $head;
}