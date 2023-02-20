<?php

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user, $conf;

dol_include_once('/commande/class/commande.class.php');
dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/flightlog.inc.php");


// Load translation files required by the page
$langs->load("mymodule@flightlog");

$validator = new FlightValidator($langs, $db, $conf->global->BBC_FLIGHT_TYPE_CUSTOMER, $user->id);
$createFlightHandler = new CreateFlightCommandHandler($db, $conf, $user, $langs, $validator);

if (!$user->rights->flightlog->vol->add) {
    accessforbidden();
}

/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */
$msg = '';
if (GETPOST("action") == 'add') {
    if (!$_POST["cancel"]) {
        $isGroupedFlight = (int) GETPOST('grouped_flight', 'int', 2) === 1;
        $orderIds = GETPOST('order_id', 'array', 2);
        $orderPassengersCount = GETPOST('order_passengers_count', 'array', 2);

        try {
            $volCommand = new CreateFlightCommand();

            $volCommand
                ->setDate(new DateTimeImmutable($_POST['flight_date']))
                ->setLieuD($_POST['lieuD'])
                ->setLieuA($_POST['lieuA'])
                ->setHeureD(new DateTimeImmutable($_POST['flight_date'].' '.$_POST['heureD']))
                ->setHeureA(new DateTimeImmutable($_POST['flight_date'].' '.$_POST['heureA']))
                ->setBBCBallonsIdBBCBallons($_POST['ballon'])
                ->setNbrPax($_POST['nbrPax'])
                ->setRemarque($_POST['comm'])
                ->setIncidents($_POST['inci'])
                ->setFkType($_POST['type'])
                ->setFkPilot($_POST['pilot'])
                ->setFkOrganisateur($_POST['orga'])
                ->setKilometers($_POST['kilometers'])
                ->setCost($_POST['cost'])
                ->setFkReceiver($_POST['fk_receiver'])
                ->setJustifKilometers($_POST['justif_kilometers'])
                ->setPassengerNames($_POST['passenger_names'])
                ->setGroupedFlight($isGroupedFlight)
                ->setOrderIds($orderPassengersCount);

            $vol = $createFlightHandler->handle($volCommand);

            include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
            $interface = new Interfaces($db);
            $triggerResult = $interface->run_triggers('BBC_FLIGHT_LOG_ADD_FLIGHT', $vol, $user, $langs, $conf);

            $msg = '<div class="ok">L\'ajout du vol du : ' . $_POST["reday"] . '/' . $_POST["remonth"] . '/' . $_POST["reyear"] . ' s\'est correctement effectue ! </div>';
            Header("Location: card.php?id=" . $vol->id);
        }catch (\Exception $e){
            $msg = '<div class="error">Erreur lors de l\'ajout du vol : ' . ($vol->error?:$e->getMessage()) . '! </div>';
        }

    }
}


/* * *************************************************
 * PAGE
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Carnet de vol', '');

$html = new Form($db);
$commande = new Commande($db);
$orders = $commande->liste_array(2);
$datec = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);
$takeOffPlaces = (new \FlightLog\Infrastructure\Flight\Query\Repository\TakeOffQueryRepository($db))->__invoke($user->id);
$mostUsedBalloon = (new \FlightLog\Infrastructure\Flight\Query\Repository\BalloonQueryRepository($db))->query([
    'pilot' => $user->id,
]);

if ($msg) {
    print $msg;
}

?>

<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">

<section class="bbc-style">

    <?php if(!empty($takeOffPlaces)): ?>
        <datalist id="take_off_places_id">
            <?php foreach ($takeOffPlaces as $takeOffPlace): ?>
                <option value="<?php echo $takeOffPlace->getPlace(); ?>"></option>
            <?php endforeach; ?>
        </datalist>
    <?php endif; ?>

    <div class="errors error-messages">
        <?php
        foreach ($validator->getErrors() as $errorMessage) {
            print sprintf('<div class="error"><span>%s</span></div>', $errorMessage);
        }
        ?>
    </div>
    <form class="flight-form js-form" name='add' action="addFlight.php" method="post">
        <input type="hidden" name="action" value="add"/>
        <input type="hidden" name="user_id" value="<?php echo $user->id; ?>"/>
        <input type="hidden" name="token" value="<?php echo newToken();?>"/>

        <!-- Date et heures -->
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Date & heures'); ?></h1>

            <div>
                <div class="form-group">
                    <label class="fieldrequired"> Type du vol</label>

                    <div class="inline-radio">
                        <?php foreach (fetchBbcFlightTypes() as $flightType) : ?>
                            <label class="">
                                <input type="radio" class="js-flight-type" name="type" value="<?php echo $flightType->id ?>" <?php echo $flightType->numero == $_POST['type'] ? 'checked' : '' ?>>
                                <span class="text-bold"><?php echo "T" . $flightType->numero ?></span>
                                <span class="font-italic hide-sm"><?php echo $flightType->nom; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                </div>

                <div class="form-group">
                    <label class="fieldrequired"> Date du vol</label>
                    <input
                        type="date"
                        name="flight_date"
                        value="<?php print (isset($_POST['flight_date']) && !empty($_POST['flight_date'])) ? $_POST['flight_date'] :  (new DateTimeImmutable())->format('Y-m-d')?>"
                        max="<?php print (new DateTimeImmutable())->format('Y-m-d')?>"
                        min="<?php print (new DateTimeImmutable())->sub(new DateInterval('P3M'))->format('Y-m-d')?>"
                    />
                </div>

                <div class="form-group">
                    <label class="fieldrequired">Heure de d&#233;part</label>

                    <input type="time"
                           name="heureD"
                           class="flat <?php echo($validator->hasError('heureD') ? 'error' : '') ?>"
                           value="<?php echo $_POST['heureD'] ?>"/>

                </div>

                <div class="form-group ">
                    <label class="fieldrequired">Heure d'arriv&#233;e</label>
                    <input type="time"
                           name="heureA"
                           class="flat <?php echo($validator->hasError('heureA') ? 'error' : '') ?>"
                           value="<?php echo $_POST['heureA'] ?>"/>
                </div>


            </div>
        </section>

        <!-- Pilote et Ballon -->
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Vol') ?></h1>
            <div >
                <div class="form-group">
                    <label class="fieldrequired"> Pilote</label>
                    <?php print $html->select_dolusers($_POST["pilot"] ? $_POST["pilot"] : $user->id, 'pilot', 0, null, 0, '', '', 0,0,0,'',0,'','', true); ?>
                </div>

                <div class="form-group ">
                    <label class="fieldrequired">
                        <span class="js-organisator-field">Organisateur</span>
                        <span class="js-instructor-field">Instructeur</span>
                    </label>
                    <?php
                        //organisateur
                        print $html->select_dolusers($_POST["orga"] ? $_POST["orga"] : $user->id, 'orga', 0, null, 0, '', '', 0,0,0,'',0,'','', true);
                    ?>
                </div>

                <div class="form-group">
                    <label class="fieldrequired">Lieu de d&#233;part </label>
                    <input type="text" name="lieuD" list="take_off_places_id" class="flat" value="<?php print  $_POST['lieuD'] ?>"/>
                </div>

                <div class="form-group ">
                    <label class="fieldrequired">Lieu d'arriv&#233;e </label>
                    <input type="text" name="lieuA" class="flat" value="<?php print  $_POST['lieuA'] ?>"/>
                </div>

                <div class="form-group">
                    <label class="fieldrequired">Ballon</label>
                    <?php select_balloons($_POST['ballon']?: ($mostUsedBalloon ? $mostUsedBalloon->getId() : ''), 'ballon', 0, false, true); ?>
                </div>

                <div class="form-group">
                    <label>Il y'avait-il plusieurs ballons ?</label>
                    <input type="checkbox" value="1" name="grouped_flight"/> - Oui
                </div>
            </div>
        </section>

        <!-- Movements -->
        <section class="form-section js-expensable-field">
            <h1 class="form-section-title"><?php echo $langs->trans('Déplacements') ?></h1>
            <div >
                <!-- number of kilometers done for the flight -->
                <div class="form-group">
                    <label class="fieldrequired">Nombre de kilometres effectués pour le vol</label>
                    <input type="number" name="kilometers" class="flat <?php echo($validator->hasError('kilometers') ? 'error' : '') ?>" value="<?php echo $_POST['kilometers'] ?>"/>
                </div>

                <!-- Justif Kilometers -->
                <div class="form-group">

                    <label class="fieldrequired">Justificatif des KM </label>
                    <textarea name="justif_kilometers" rows="2" cols="60" class="flat <?php echo($validator->hasError('justif_kilometers') ? 'error' : '') ?>"><?php echo $_POST['justif_kilometers'] ?></textarea>
                </div>
            </div>
        </section>

        <!-- Passagers -->
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Passagers') ?></h1>
            <div >
                <div class="form-group">
                    <label class="fieldrequired"><?php echo $langs->trans('Nombre de passagers'); ?></label>
                    <input type="number"
                           name="nbrPax"
                           min="0"
                           max="5"
                           class="flat <?php echo $validator->hasError('nbrPax') ? 'error' : '' ?>"
                           value="<?php echo $_POST['nbrPax']?: 0 ?>"/>
                </div>

                <!-- passenger names -->
                <div class="form-group">
                    <label class="fieldrequired"><?php echo $langs->trans('Noms des passagers'); ?><br/>(Séparé par des ; )</label>
                    <textarea name="passenger_names" cols="60" rows="2" class="flat <?php echo $validator->hasError('passenger_names') ? 'error' : '' ?>"><?php echo $_POST['passenger_names'] ?></textarea>
                </div>
            </div>
        </section>

        <!-- billing information -->
        <section class="form-section js-billable-field">
            <h1 class="form-section-title"><?php echo $langs->trans('Facturation') ?></h1>

            <div>
                <p class="text-muted">
                    Le bloc sur la facturation permet de savoir où retrouver l'argent du vol. Sur des commandes, au près d'un membre, ... <br/>
                    Il est donc normal de devoir réencoder le nombre de passagers.
                </p>

                <!-- Order -->
                <div id="list_order" class="js-base-form js-billable-field form-group">
                    <!-- BASE form -->
                    <table class="bill style-default">
                        <!-- Cash -->
                        <tr>
                            <th colspan="2">A. Cash <small>(ou virement)</small></th>
                        </tr>

                        <tr>
                            <td colspan="2">
                                A compléter si de l'argent a été perçu par un membre du Belgian Balloon Club.
                            </td>
                        </tr>

                        <tr>
                            <td class="js-receiver" data-user-id="<?php echo $user->id;?>">
                                <label class=""><?php echo $langs->trans('Membre ayant perçu l\'argent')?>?</label>
                                <?php print $html->select_dolusers(
                                        $_POST["fk_receiver"] ? $_POST["fk_receiver"] : -1,
                                    'fk_receiver', true, null, 0, '', '', 0,0,0,'',0,'','', true); ?>
                            </td>
                            <td>
                                <label>&nbsp;</label>
                                <div class="input-group">
                                    <input type="number" name="cost"  step="1" min="0" class="flat js-cost" disabled value="<?php echo $_POST['cost']?:0 ?>"/>
                                    <span class="input-symbol">&euro;</span>
                                </div>
                            </td>
                        </tr>

                        <!-- Order -->
                        <tr>
                            <th>B. Commande(s)</th>
                            <th>Nombre de passagers.</th>
                        </tr>
                        <?php if(is_array($_POST['order_passengers_count']) && !empty($_POST['order_passengers_count'])): ?>
                            <?php foreach($_POST['order_passengers_count'] as $order => $orderQuantity): ?>
                                <tr class="order-row">
                                    <td>
                                        <span class="fa fa-trash remove js-remove" data-order-id="<?php echo $order; ?>"></span>
                                        <span class="js-order-ref"><?php echo $orders[$order]?></span>
                                    </td>
                                    <td><input type="number" value="<?php echo $orderQuantity; ?>" min="1" max="5" name="order_passengers_count[<?php echo $order; ?>]" class="js-nbr-pax" /></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr class="js-order">
                            <td>
                                <?php
                                echo $html::selectarray(
                                    'order_id',
                                    $orders,
                                    $_POST['order_id'],
                                    1,
                                    0,
                                    $validator->hasError('order_id') ? 'error' : '',
                                    0,
                                    '100%',
                                    0,
                                    0,
                                    '',
                                    'js-order-select',
                                    true
                                );
                                ?>
                                <span class="text-muted">Sélection de la commande réalisée en totalité (ou en partie)</span>
                            </td>

                            <td >&nbsp;</td>
                        </tr>

                    </table>
                </div>
            </div>
        </section>

        <!-- Comments -->
        <section class="form-section">
            <h1 class="form-section-title"><?php echo $langs->trans('Commentaires') ?></h1>
            <div>
                <!-- commentaires -->
                <div class=" form-group">
                    <label class="fieldrequired"> Note sur le vol </label>
                    <textarea rows="2" cols="60" class="flat" name="comm" placeholder="RAS"><?php print $_POST['comm']; ?></textarea>
                </div>

                <!-- incidents -->
                <div class=" form-group">
                    <label class="fieldrequired"> Incidents, Brulure, ...</label>
                    <textarea rows="2" cols="60" class="flat" name="inci" placeholder="RAS"><?php print $_POST['inci']; ?></textarea>
                    <p class="text-muted">
                        Incidents ou dégâts occasionés ou constatés sur le matériel.<br/>
                        Ce champ enverra un e-mail automatique aux titulaires ainsi qu'au repsonsable matériel volant et non volant.
                    </p>
                </div>

            </div>
        </section>

        <div class="d-grid">
            <div class="grid-col grid-col-6">
                <button class="button _info" type="button" name="cancel" ><?php print $langs->trans("Cancel") ?></button>
            </div>

            <div class="grid-col grid-col-6">
                <button class="button _success" type="submit" ><span class="fa fa-check"></span> <?php print $langs->trans("Save") ?></button>
            </div>
        </div>
    </form>
<?php

$db->close();
?>

<script type="text/html" id="orderRow">

    <tr class="order-row">
        <td><span class="fa fa-trash remove js-remove"></span> <span class="js-order-ref"></span></td>
        <td><input type="number" value="1" min="1" max="5" name="" class="js-nbr-pax" /></td>
    </tr>
</script>

<script type="application/javascript">

    /**
     * get the flight type object from an id.
     */
    function getFlightType(flightTypeId){
        var types = {
            1:{
                'billable' : 1,
                'expensable' : 1,
                'id' : 1
            },
            2:{
                'billable' : 1,
                'expensable' : 1,
                'id' : 2
            },
            3:{
                'billable' : 0,
                'expensable' : 0,
                'id' : 3
            },
            4:{
                'billable' : 0,
                'expensable' : 0,
                'id' : 4
            },
            5:{
                'billable' : 0,
                'expensable' : 0,
                'id' : 5
            },
            6:{
                'billable' : 0,
                'expensable' : 0,
                'id' : 6
            },
            7:{
                'billable' : 0,
                'expensable' : 0,
                'id' : 7
            }
        };

        var flightTypeNull = {
            'billable' : 0,
            'expensable' : 0,
            'id' : 0
        };

        return typeof types[flightTypeId] === 'undefined' ? flightTypeNull : types[flightTypeId];
    }

    function flightTypeChanged($this){
        var typeId = $this.val();
        var flightType = getFlightType(typeId);

        if(flightType.billable === 1){
            $('.js-form .js-billable-field').removeClass('hidden');
        }else{
            $('.js-form .js-billable-field').addClass('hidden');
        }

        if(flightType.expensable === 1){
            $('.js-form .js-expensable-field').removeClass('hidden');
        }else{
            $('.js-form .js-expensable-field').addClass('hidden');
        }

        if(flightType.id === 6){
            //instruction flight
            $('.js-form .js-instructor-field').removeClass('hidden');
            $('.js-form .js-organisator-field').addClass('hidden');
        } else {
            $('.js-form .js-instructor-field').addClass('hidden');
            $('.js-form .js-organisator-field').removeClass('hidden');
        }

    }

    function removeOrderLine(){
        var $this = $(this);
        $('.js-base-form .js-order select option[value="'+$this.data('orderId')+'"]').attr('disabled', false);
        $this.parents('tr').remove();
    }

    function addOrder(){
        var orderId = parseInt($('.js-base-form .js-order select').val(), 10);
        var $option = $('.js-base-form .js-order select option[value="'+orderId+'"]');
        var orderRef = $option.html();
        var $addingElement = $($('#orderRow').html());
        var $removeButton = $addingElement.find('.js-remove');
        var nbrPax = 1;

        if(orderId <= 0){
            return;
        }

        // Manage remove button
        $removeButton.data('orderId', orderId);
        $removeButton.on('click', removeOrderLine);

        // Add the reference
        $addingElement.find('.js-order-ref').html(orderRef);

        // Add the number of pax
        $addingElement.find('.js-nbr-pax').html(nbrPax);
        $addingElement.find('input.js-nbr-pax').val(nbrPax);
        $addingElement.find('input.js-nbr-pax').attr('name', 'order_passengers_count['+orderId+']');

        // disable the option
        $option.attr('disabled', true);

        $('.js-base-form .js-order').before($addingElement);

    }

    function changeReceiver(){
        var $select = $(this);
        var userId = parseInt($select.val(), 10);
        var currentUserId = parseInt($select.parents('.js-receiver').data('userId'), 10);
        var $cost = $('input.js-cost');

        $cost.val(0);
        $cost.prop('disabled', true);
        if(userId === currentUserId){
            $cost.val(0);
            $cost.prop('disabled', false);
        }
    }

    $(function(){
        $('.js-base-form .js-order select').on('change', addOrder);
        $('.js-base-form .js-receiver select').on('change', changeReceiver);

        $('.js-flight-type').on('change', function(){
            var $this = $(this);
            flightTypeChanged($this);
        });
        $('.js-remove').on('click', removeOrderLine);
        flightTypeChanged($('.js-flight-type:checked'));

    });
</script>

</section>
