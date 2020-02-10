<?php
/* Copyright (C) 2010-2011	Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2013		Juanjo Menent <jmenent@2byte.es>
 * Copyright (C) 2014       Marcos García <marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>

<!-- BEGIN PHP TEMPLATE -->

<?php

global $user, $db;
global $noMoreLinkedObjectBlockAfter;

$langs = $GLOBALS['langs'];
$linkedObjectBlock = $GLOBALS['linkedObjectBlock'];

$langs->load("bills");

$total = 0;
$ilink = 0;
$var = true;


/**
 * @var int     $key
 * @var \FlightLog\Application\Damage\ViewModel\Damage $damage
 */
foreach ($linkedObjectBlock as $key => $damage):
    $ilink++;
    $var = !$var;
    $trclass = ($var ? 'pair' : 'impair');
?>
    <tr <?php echo $GLOBALS['bc'][$var]; ?> >
        <td><?php echo $langs->trans("Dégâts"); ?></td>
        <td><a href="<?php echo sprintf(DOL_URL_ROOT.'/flightlog/index.php?id=%s&r=%s', $damage->getId(), 'get_one_damage');?>"><?php echo $damage->getId()?></a></td>
        <td align="center"><?php echo $damage->getAuthorName(); ?></td>
        <td align="center"></td>
        <td align="right"><?echo price($damage->getAmount()) ?>€</td>
        <td align="right"><span class="fa fa-<?php echo $damage->isInvoiced() ? 'check' : 'times'; ?>"></span></td>
        <td align="right"><a
                    href="<?php echo $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=dellink&dellinkid=' . $key; ?>"><?php echo img_delete($langs->transnoentitiesnoconv("RemoveLink")); ?></a>
        </td>
    </tr>
<?php endforeach; ?>

<!-- END PHP TEMPLATE -->