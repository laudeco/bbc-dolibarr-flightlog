<?php

// Load Dolibarr environment
if (false === (@include '../main.inc.php')) {  // From htdocs directory
    require '../../documents/custom/main.inc.php'; // From "custom" directory
}

global $db, $langs, $user, $conf;

dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');
dol_include_once("/flightlog/lib/flightLog.lib.php");
dol_include_once("/flightlog/validators/SimpleOrderValidator.php");
dol_include_once("/flightlog/command/CreateOrderCommandHandler.php");
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';

// Load object modCodeTiers
$module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
{
    $module = substr($module, 0, dol_strlen($module)-4);
}
$dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
foreach ($dirsociete as $dirroot)
{
    $res=dol_include_once($dirroot.$module.'.php');
    if ($res) break;
}
$modCodeClient = new $module;
// Load object modCodeFournisseur
$module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
{
    $module = substr($module, 0, dol_strlen($module)-4);
}
$dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
foreach ($dirsociete as $dirroot)
{
    $res=dol_include_once($dirroot.$module.'.php');
    if ($res) break;
}
$modCodeFournisseur = new $module;

// Load translation files required by the page
$langs->load("mymodule@flightlog");

$validator = new SimpleOrderValidator($langs, $db, $conf->global->BBC_FLIGHT_TYPE_CUSTOMER);

$customer = new Societe($db);

$successMessage = false;

/* * *****************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 * ****************************************************************** */
$msg = '';
$socid = null;

if (GETPOST('socid', 'int') > 0){
    $socid = GETPOST('socid', 'int');
    $customer->fetch($socid);
}


if (GETPOST("action") == 'add') {
    if (!$_POST["cancel"]) {

        $formObject = new stdClass();
        $formObject->socid = $socid;
        $formObject->name = GETPOST('name','alpha');
        $formObject->firstname = GETPOST('firstname','alpha');
        $formObject->zip = GETPOST('zipcode','alpha');
        $formObject->town = GETPOST('town', 'alpha');
        $formObject->state = GETPOST('state_id', 'int');
        $formObject->phone = GETPOST('phone', 'alpha');
        $formObject->origine = GETPOST('origine', 'int');
        $formObject->email = trim(GETPOST('mail', 'custom', 0, FILTER_SANITIZE_EMAIL));
        $formObject->tva = GETPOST('tva_intra', 'alpha');
        $formObject->nbrPax = GETPOST('nbrPax', 'int');
        $formObject->region = GETPOST('region', 'alpha');
        $formObject->cost = GETPOST('cost');
        $formObject->comment = GETPOST('comm', 'alpha');
        $formObject->civilityId = GETPOST('civility', 'alpha');
        $formObject->language = GETPOST('default_lang', 'int');
        $formObject->isCommentPublic = GETPOST('public_comment', 'int');

        if ($validator->isValid($formObject, $_REQUEST)) {
            $createOrderCommand = new CreateOrderCommand($formObject, $user->id);
            try{
                $handler = new CreateOrderCommandHandler($db, $conf,$user,$langs,$modCodeClient, $modCodeFournisseur);
                $handler->handle($createOrderCommand);

                $msg = '<div class="success ok">Commande et tiers créés. </div>';
                $successMessage = true;
            } catch (\Exception $e) {
                // Creation KO
                $msg = '<div class="error">Erreur lors de l\'ajout de la commande</div>';
            }
        }
    }
}


/* * *************************************************
 * PAGE
 *
 * Put here all code to build page
 * ************************************************** */

llxHeader('', 'Creation d\'une commande', '');

$html = new Form($db);
$formcompany = new FormCompany($db);
$formAdmin = new FormAdmin($db);


$datec = dol_mktime(12, 0, 0, $_POST["remonth"], $_POST["reday"], $_POST["reyear"]);
if ($msg) {
    print $msg;
}

?>

    <!-- Success message with reference -->
    <?php if($successMessage): ?>
        <div class="bbc-style">
            <p class="cadre_msg1">
                Vous avez généré la facture et créé un tiers.<br/>
                Merci,
            </p>

            <?php if(count($validator->getWarningMessages()) > 0):?>
                <div class="box box-warning">
                    <p>Ceci sont des messages d'attention mais qui ne bloquent pas la création de la commande</p>
                    <ul>
                        <?php foreach($validator->getWarningMessages() as $warningMessage): ?>
                            <li class="warning"><?php echo $warningMessage; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <table class="table_resume">

                <tr>
                    <td></td>
                    <td>Statut</td>
                    <td>Référence</td>
                </tr>

                <!-- tiers -->
                <tr>
                    <td>Tiers</td>
                    <td><span class="dashboardlineok">OK</span></td>
                    <td><?php echo $handler->getCustomer()->getNomUrl(); ?></td>
                </tr>

                <!-- Commande -->
                <tr>
                    <td>Commande</td>
                    <td><span class="dashboardlineok">OK</span></td>
                    <td><?php echo $handler->getOrder()->getNomUrl(); ?></td>
                </tr>

                <tr>
                    <td colspan="3">
                        Le passager doit faire le payement avec ce numéro de commande.<br/>
                        Cette référence doit aussi être communiquée au(x) pilote(s) qui feront le vol.
                    </td>
                </tr>
            </table>

            <p>
                Si le passager demande un document, merci de me le communiquer, je ferais le nécessaire.
            </p>

        </div>

        <?php return; ?>
    <?php endif; ?>


    <div class="errors error-messages">
        <?php
        foreach ($validator->getErrors() as $errorMessage) {
            print sprintf('<div class="error"><span>%s</span></div>', $errorMessage);
        }
        ?>
    </div>

    <div>
        <p>
            Cette page vous permettra de créer une commande. La commande est <b>obligatoire</b> si vous désirez faire payer les passagers directement sur le compte du club.<br>
            Si vous avez un doute sur la manière d'encoder la commande, veuillez me contacter AVANT de soumettre le dit formulaire.<br/>
            Si vous avez <b>déjà</b> encodé une commande, et que vous voulez la retrouver veuillez vous rendre sur : <a href="<?php echo sprintf(DOL_URL_ROOT.'/commande/list.php?search_sale=%s', $user->id); ?>">mes commandes.</a>
        </p>
    </div>
    <form class="flight-form" name='add' method="post">
    <input type="hidden" name="action" value="add"/>

    <!-- Commanditaire -->
    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Commanditaire') ?></h1>
        <table class="border" width="100%">

            <tr>
                <td class="">
                    <?php echo $langs->trans('Commanditaire'); ?>
                </td>

                <td>
                    <?php print $form->select_company($customer->id, 'socid', '((s.client = 1 OR s.client = 3) AND s.status=1)', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300'); ?>
                    <script type="text/javascript">
                        $(document).ready(function() {
                            $("#socid").change(function() {
                                var socid = $(this).val();
                                window.location.href = "<?php echo $_SERVER["PHP_SELF"].'?socid="+socid' ?>
                            });
                        });
                    </script>
                </td>

            </tr>


            <?php if($socid === null): ?>
                <!-- Nom -->
                <tr>
                    <td class="fieldrequired">
                        <?php echo $langs->trans('Nom'); ?>
                    </td>
                    <td>
                        <input type="text"
                               name="name"
                               class="flat <?php echo $validator->hasError('name') ? 'error' : '' ?>"
                               value="<?php echo $formObject->name ?>"/>
                    </td>
                </tr>

                <!-- Firstname -->
                <tr>
                    <td class="">
                        <?php echo $langs->trans('Prénom'); ?>
                    </td>
                    <td>
                        <input type="text"
                               name="firstname"
                               class="flat <?php echo $validator->hasError('firstname') ? 'error' : '' ?>"
                               value="<?php echo $formObject->firstname ?>"/>
                    </td>
                </tr>

                <!-- civility-->
                <tr>
                    <td class="">
                        <?php echo $langs->trans('UserTitle'); ?>
                    </td>
                    <td>
                        <?php echo $formcompany->select_civility($formObject->civilityId, 'civility'); ?>
                    </td>
                </tr>

                <!-- Phone -->
                <tr>
                    <td class="">
                        <?php echo $langs->trans('Téléphone'); ?>
                    </td>
                    <td>
                        <input type="text"
                               name="phone"
                               class="flat <?php echo $validator->hasError('phone') ? 'error' : '' ?>"
                               value="<?php echo $formObject->phone ?>"/>
                    </td>
                </tr>

                <!-- Mail -->
                <tr>
                    <td class="">
                        <?php echo $langs->trans('E-mail'); ?>
                    </td>
                    <td>
                        <input type="text"
                               name="mail"
                               class="flat <?php echo $validator->hasError('email') ? 'error' : '' ?>"
                               value="<?php echo $formObject->email; ?>"/>
                    </td>
                </tr>

                <!-- Language -->
                <tr>
                    <td class="fieldrequired">
                        <?php echo $langs->trans('DefaultLang'); ?>
                    </td>
                    <td>
                        <?php echo $formAdmin->select_language($conf->global->MAIN_LANG_DEFAULT,'default_lang',0,0,1,0,0,'maxwidth200onsmartphone'); ?>
                    </td>
                </tr>

                <!-- Region -->
                <tr>
                    <td class="">
                        <?php echo $langs->trans('Region'); ?>
                    </td>
                    <td>
                        <?php print $formcompany->select_state($formObject->state,'BE'); ?>
                    </td>
                </tr>

                <?php
                // Zip / Town
                print '<tr><td>'.fieldLabel('Zip','zipcode').'</td><td>';
                        print $formcompany->select_ziptown($formObject->town,'zipcode',array('town','selectcountry_id','state_id'), 0, 0, '', 'maxwidth100 quatrevingtpercent');
                        print '</td><td>'.fieldLabel('Town','town').'</td><td>';
                        print $formcompany->select_ziptown($formObject->zip,'town',array('zipcode','selectcountry_id','state_id'), 0, 0, '', 'maxwidth100 quatrevingtpercent');
                        print '</td></tr>';
                ?>

                <!-- origine -->
                <tr>
                    <td class="">
                        <?php echo $langs->trans('Origine'); ?>
                    </td>
                    <td>
                        <?php $html->selectInputReason($formObject->origine, 'origine', 1); ?>
                    </td>
                </tr>

                <!-- TVA -->
                <tr>
                    <td class="">
                        Numéro de TVA
                    </td>
                    <td>
                        <input type="text" class="flat" name="tva_intra" id="intra_vat" maxlength="20" value="<?php echo $_POST['tva_intra']; ?>">
                        <?php
                        if (empty($conf->global->MAIN_DISABLEVATCHECK)): ?>

                            <?php if (! empty($conf->use_javascript_ajax)): ?>
                                <script language="JavaScript" type="text/javascript">
                                function CheckVAT(a) {
                                    <?php print "newpopup('".DOL_URL_ROOT."/societe/checkvat/checkVatPopup.php?vatNumber='+a,'".dol_escape_js($langs->trans("VATIntraCheckableOnEUSite"))."',500,300);"; ?>
                                }
                                </script>
                                <a href="#" class="hideonsmartphone" onclick="javascript: CheckVAT(document.add.tva_intra.value);"><?php echo $langs->trans("VATIntraCheck"); ?></a>
                                <?php echo $html->textwithpicto($s,$langs->trans("VATIntraCheckDesc",$langs->trans("VATIntraCheck")),1); ?>
                            <?php else: ?>
                                <a href="<?php echo $langs->transcountry("VATIntraCheckURL",$object->country_id); ?>" target="_blank"><?php echo img_picto($langs->trans("VATIntraCheckableOnEUSite"),'help'); ?></a>
                            <?php endif; ?>
                       <?php endif; ?>
                    </td>
                </tr>

            <?php endif; ?>

        </table>
    </section>

    <!-- Passagers -->
    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Données du vol') ?></h1>
        <table class="border js-compute" width="50%">

            <!-- Nombre -->
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans('Nombre de passagers'); ?></td>
                <td>
                    <input type="number"
                           name="nbrPax"
                           class="flat <?php echo $validator->hasError('nbrPax') ? 'error' : '' ?>"
                           value="<?php echo $_POST['nbrPax'] ?>"/>
                </td>
            </tr>

            <!-- Flight cost -->
            <tr>
                <td class="fieldrequired"><?php echo $langs->trans('Montant demandé (au total)') ?></td>
                <td>
                    <input type="text" name="cost" class="flat  <?php echo $validator->hasError('cost') ? 'error' : '' ?>" value="<?php echo $_POST['cost'] ?> "/>
                    &euro;
                </td>
            </tr>

            <!-- Total / pax -->
            <tr class="cadre_prix_total">
                <td class="fieldrequired"><?php echo $langs->trans('Montant / passager') ?></td>
                <td>
                    <span class="js-total">0</span>&euro;
                </td>
            </tr>
        </table>
    </section>

    <!-- Commentaire -->
    <section class="form-section">
        <h1 class="form-section-title"><?php echo $langs->trans('Commentaire') ?></h1>
        <table class="border" width="50%">

            <!-- Comment -->
            <tr>
                <td><?php echo $langs->trans('Le commentaire doit-il figurer sur la commande') ?></td>
                <td>
                    <input type="radio" id="public_comment" name="public_comment" value="1" <?php echo ($formObject->isCommentPublic == 1)?'checked="checked"' : ''; ?>/>
                    <label for="public_comment">Oui</label>
                    -
                    <input type="radio" id="private_comment" name="public_comment" value="0" <?php echo ($formObject == null || $formObject->isCommentPublic === null || $formObject->isCommentPublic === 0)?'checked="checked"' : ''; ?>/>
                    <label for="private_comment">Non</label>
                </td>
            </tr>

            <tr>
                <td class=""> Commentaire </td>
                <td>
                    <?php
                        print '<textarea rows="2" cols="60" class="flat" name="comm" placeholder="">' . $_POST['comm'] . '</textarea> ';
                    ?>
                </td>
            </tr>
        </table>
    </section>

    <script type="application/javascript">
        (function($){

            var cost = $('.js-compute input[name="cost"]').val();
            var nbrPax = $('.js-compute input[name="nbrPax"]').val();
            compute();

            $('.js-compute input[name="nbrPax"]').on('change', function(){
                nbrPax = $(this).val();
                compute();
            });

            $('.js-compute input[name="cost"]').on('change', function(){
                cost = $(this).val();
                compute();
            });

            function compute(){
                if(isNaN(parseInt(nbrPax, 10)) || parseInt(nbrPax, 10) === 0){
                    $('.js-compute .js-total').text(0);
                    return;
                }

                var total = parseInt((cost / nbrPax)*100, 10    );
                $('.js-compute .js-total').text(total/100);
            }


        })($);
    </script>

<?php

print '<br><input class="button" type="submit" value="' . $langs->trans("Save") . '"> &nbsp; &nbsp; ';
print '<input class="button" type="submit" name="cancel" value="' . $langs->trans("Cancel") . '">';

print '</form>';

$db->close();
