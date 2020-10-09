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
dol_include_once("/flightlog/validators/FlightValidator.php");
dol_include_once("/flightlog/command/CommandInterface.php");
dol_include_once("/flightlog/command/CommandHandlerInterface.php");
dol_include_once("/flightlog/command/CreateFlightCommand.php");
dol_include_once("/flightlog/command/CreateFlightCommandHandler.php");

// Load translation files required by the page
$langs->load("mymodule@flightlog");

$validator = new FlightValidator($langs, $db, $conf->global->BBC_FLIGHT_TYPE_CUSTOMER);
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
        $dated = GETPOST('flight_date');
        $isGroupedFlight = (int) GETPOST('grouped_flight', 'int', 2) === 1;
        $orderIds = GETPOST('order_id', 'array', 2);
        $orderPassengersCount = GETPOST('order_passengers_count', 'array', 2);

        $volCommand = new CreateFlightCommand();
        $volCommand->setDate($dated)
            ->setLieuD($_POST['lieuD'])
            ->setLieuA($_POST['lieuA'])
            ->setHeureD($_POST['heureD'])
            ->setHeureA($_POST['heureA'])
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

        try{
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

$icons = [
    '1' => 'fa fa-ad',
    '2' => 'fa fa-ticket-alt',
    '3' => 'fa fa-user-shield',
    '4' => 'fa fa-users',
    '5' => 'fa fa-images',
    '6' => 'fa fa-graduation-cap',
    '7' => 'fa fa-user-plus',
];
$html = new Form($db);
$commande = new Commande($db);
$datec = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);
if ($msg) {
    print $msg;
}

?>

<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">

<section class="bbc-style">


    <div class="errors error-messages">
        <?php
        foreach ($validator->getErrors() as $errorMessage) {
            print sprintf('<div class="error"><span>%s</span></div>', $errorMessage);
        }
        ?>
    </div>
    <form class="flight-form js-form" name='add' action="addFlight.php" method="post">
        <input type="hidden" name="action" value="add"/>

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
                            <span class="icon <?php echo $icons[$flightType->numero]; ?>"></span>
                            <span class="text-bold"><?php echo "T" . $flightType->numero ?></span>
                            <span class="font-italic"><?php echo $flightType->nom; ?></span>
                        </label>
                    <?php endforeach; ?>

                </div>

                <div class="form-group">
                    <label class="fieldrequired"> Date du vol</label>

                    <input type="date" name="flight_date" value="<?php print (new DateTimeImmutable())->format('Y-m-d')?>"/>
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
                    <input type="text" name="lieuD" class="flat" value="<?php print  $_POST['lieuD'] ?>"/>
                </div>

                <div class="form-group ">
                    <label class="fieldrequired">Lieu d'arriv&#233;e </label>
                    <input type="text" name="lieuA" class="flat" value="<?php print  $_POST['lieuA'] ?>"/>
                </div>

                <div class="form-group">
                    <label class="fieldrequired">Ballon</label>
                    <?php select_balloons($_POST['ballon'], 'ballon', 0, false); ?>
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
                <div id="list_order" class=" js-billable-field form-group">
                    <!-- BASE form -->
                    <div class="js-base-form base-order-row order-row">
                        <div class="js-order-ref order-reference">Comment facturer ce vol?</div>

                        <!-- bill type -->
                        <div class="form-group ">
                            <label>Ce vol est composé de ...</label>
                            <div class="inline-radio">
                                <label class=""><input type="radio" class="js-bill-type" name="billType" value="order"> Une commande</label>
                                <label class=""><input type="radio" class="js-bill-type" name="billType" value="cash"> J'ai reçu du cash</label>
                                <label class=""><input type="radio" class="js-bill-type" name="billType" value="other_receiver"> Quelqu'un d'autre à reçu du cash</label>
                            </div>

                        </div>

                        <!-- Order -->
                        <div class="form-group  js-visible-order js-hide-cash hidden">
                            <label>Sélection de la commande réalisée en totalité (ou en partie)</label>
                            <?php
                            echo $html::selectarray(
                                'order_id',
                                $commande->liste_array(2),
                                $_POST['order_id'],
                                1,
                                0,
                                $validator->hasError('order_id') ? 'error' : '',
                                0,
                                '100%',
                                    0,
                                    0,
                                    '',
                                    'js-order-select'
                            );
                            ?>
                        </div>

                        <!-- Nbr pax -->
                        <div class="form-group js-visible-order js-hide-cash js-hide-other_receiver hidden">
                            <label>Nombre de passager(s)</label>
                            <input type="number" class="js-nb-pax-order" step="1" min="0" max="5" value="1" />
                            <span class="text-muted">Nombre de passagers de la nacelle associés à cette commande.</span>
                        </div>

                        <!-- Money receiver -->
                        <div class="form-group js-visible-cash js-hide-order js-visible-other_receiver  hidden">
                            <label class=""><?php echo $langs->trans('Qui a perçu l\'argent')?></label>
                            <?php print $html->select_dolusers($_POST["fk_receiver"] ? $_POST["fk_receiver"] : $user->id,
                                'fk_receiver', true, null, 0, '', '', 0,0,0,'',0,'','', true); ?>
                        </div>

                        <!-- Flight cost -->
                        <div class=" form-group js-visible-cash js-hide-other_receiver js-hide-order hidden">
                            <label class="">Montant perçu</label>
                            <div class="input-group">
                                <input type="number" name="cost"  step="1" min="0" class="flat js-cost" value="<?php echo $_POST['cost']?:0 ?>"/>
                                <span class="input-symbol">&euro;</span>
                            </div>

                        </div>

                        <div class="form-group center">
                            <button class="button _primary js-add-bill-type" type="button" ><span class="fa fa-plus"></span> <?php echo $langs->trans('Ajouter') ?></button>
                        </div>

                    </div>

                    <div class="js-order list"></div>
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
                    <p class="text-muted">Incidents ou dégâts constatés au ballon.</p>
                </div>
            </div>
        </section>

        <div class="d-grid">
            <div class="grid-col grid-col-6">
                <input class="button" type="submit" name="cancel" value="<?php print $langs->trans("Cancel") ?>">
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
    <div class="js-detail-order order-row selectable">
        <input type="hidden" value="0" name="" class="js-nbr-pax" />

        <!-- Remove button -->
        <div class="remove fa fa-times js-remove"></div>

        <!-- Reference -->
        <div class="js-order-ref order-reference"></div>

        <!-- Number of pax -->
        <div> Nombre de passagers : <span class="js-nbr-pax nbr-pax"></span></div>
    </div>
</script>

<script type="text/html" id="cashRow">
    <div class="js-detail-order order-row cash-row selectable">

        <div class="corner"></div>

        <input type="hidden" value="0" name="amount" class="js-amount" />
        <input type="hidden" value="<?php echo $user->id; ?>" name="receiver" class="js-receiver-id" />

        <!-- Remove button -->
        <div class="remove fa fa-times js-remove"></div>

        <p>Ce membre à perçu l'argent.</p>

        <!-- Reference -->
        <p>Membre : <span class="js-order-ref order-reference"></span></p>

        <!-- Number of pax -->
        <div> Montant reçu : <span class="js-amount"></span>&euro;</div>
    </div>
</script>

<script type="application/javascript">

    <?php if(!empty(GETPOST('order_passengers_count', 'array', 2))): ?>
        var orders = {};
        <?php foreach( GETPOST('order_passengers_count', 'array', 2) as $currentOrderId=>$nbrPaxForOrder): ?>
        orders[<?php echo $currentOrderId; ?>] = <?php echo $nbrPaxForOrder; ?>;
        <?php endforeach; ?>
    <?php endif; ?>

    function hideOrderInformation (){
        var $this = $(this);

        //Multi orders
        $this.find('option:selected').each(function(){

            return;

            var $option = $(this);
            var $addingElement = $($('#orderRow').html());
            var $input = $addingElement.find('.js-order-passenger input');
            var orderId = parseInt($option.val(), 10);
            var $removeButton = $addingElement.find('.js-remove');

            if(orderId <= 0){
                return;
            }

            $addingElement.find('.js-order-ref').html($option.html());

            $removeButton.data('orderId', orderId);
            $removeButton.on('click', function(){
                var $this = $(this);
                $('.js-order select option[value="'+$this.data('orderId')+'"]').attr('disabled', false);
                $this.parents('.js-detail-order').remove();
            });

            $option.attr('disabled', true);

            $input.attr('name' , 'order_passengers_count['+$option.val()+']');
            if(typeof orders !== "undefined" && typeof orders[$option.val()] !== 'undefined'){
                $input.val(orders[orderId]);
            }

            $('#list_order .js-order .order-row:first-child').after($addingElement);
            //$addingElement.find('input').focus();
        });
    }

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

    function flightTypeChanged(){
        var $this = $(this);
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

    function addBillType(){
        var type = $('.js-base-form .js-bill-type:checked').val();
        if(type === 'cash'){
            return addCash();
        }

        if(type === 'order'){
            return addOrder();
        }

        if(type === 'other_receiver'){
            return addCashOther();
        }
    }

    function addOrder(){
        var orderId = parseInt($('.js-base-form .js-order-select').val(), 10);
        var $option = $('.js-base-form .js-order-select option[value="'+orderId+'"]');
        var orderRef = $option.html();
        var $addingElement = $($('#orderRow').html());
        var $removeButton = $addingElement.find('.js-remove');
        var nbrPax = parseInt($('.js-base-form .js-nb-pax-order').val(), 10);

        if(orderId <= 0){
            return;
        }

        // Manage remove button
        $removeButton.data('orderId', orderId);
        $removeButton.on('click', function(){
            var $this = $(this);
            $('.js-base-form .js-order-select option[value="'+$this.data('orderId')+'"]').attr('disabled', false);
            $this.parents('.js-detail-order').remove();
        });

        // Add the reference
        $addingElement.find('.js-order-ref').html(orderRef);

        // Add the number of pax
        $addingElement.find('.js-nbr-pax').html(nbrPax);
        $addingElement.find('input.js-nbr-pax').val(nbrPax);
        $addingElement.find('input.js-nbr-pax').attr('name', 'order_passengers_count['+orderId+']');

        // disable the option
        $option.attr('disabled', true);

        $('#list_order .js-order').append($addingElement);

    }

    function addCash(){
        var amount = parseInt($('.js-base-form input.js-cost').val(), 10);
        var $option = $('.js-base-form select[name="fk_receiver"] option:selected');
        var receiver = $option.html();
        var receiverId = $option.val();

        var $addingElement = $($('#cashRow').html());
        var $removeButton = $addingElement.find('.js-remove');

        // Manage remove button
        $removeButton.on('click', function(){
            var $this = $(this);
            $this.parents('.js-detail-order').remove();
        });

        // Add the receiver name
        $addingElement.find('.js-order-ref').html(receiver);
        $addingElement.find('input.js-receiver').val(receiverId);

        // Add the amount
        $addingElement.find('.js-amount').html(amount);
        $addingElement.find('input.js-amount').val(amount);

        $('#list_order .js-order').append($addingElement);
    }

    function addCashOther(){
        var amount = 0;
        var $option = $('.js-base-form select[name="fk_receiver"] option:selected');
        var receiver = $option.html();
        var receiverId = $option.val();

        var $addingElement = $($('#cashRow').html());
        var $removeButton = $addingElement.find('.js-remove');

        // Manage remove button
        $removeButton.on('click', function(){
            var $this = $(this);
            $this.parents('.js-detail-order').remove();
        });

        // Add the receiver name
        $addingElement.find('.js-order-ref').html(receiver);
        $addingElement.find('input.js-receiver').val(receiverId);

        // Add the amount
        $addingElement.find('span.js-amount').parent().remove();
        $addingElement.find('input.js-amount').val(amount);

        $('#list_order .js-order').append($addingElement);
    }

    function baseBillMethodChange(){
        var method = $(this).val();

        $('.js-visible-'+ method).removeClass('hidden');
        $('.js-hide-'+method).addClass('hidden');
    }

    $(function(){
        $('.js-base-form .js-bill-type').on('change', baseBillMethodChange);
        $('.js-base-form .js-add-bill-type').on('click', addBillType);

        $('.js-flight-type').on('change', flightTypeChanged);
        $('.js-flight-type').each(flightTypeChanged);

    });
</script>

</section>
