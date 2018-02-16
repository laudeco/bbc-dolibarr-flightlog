<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2012-2016 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015-2017 Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2016      Josep Lluís Amador   <joseplluis@lliuretic.cat>
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
 */

/**
 *      \file       htdocs/projet/element.php
 *      \ingroup    projet
 *        \brief      Page of project referrers
 */

/*
 * Links between flights and tasks are managed by the elements_elements table, the source is the task and the destination is the flight.
 */

require '../../../main.inc.php';
dol_include_once('/projet/class/project.class.php');
dol_include_once('/projet/class/task.class.php');
dol_include_once('/adherents/class/adherent.class.php');
dol_include_once('/flightlog/class/instruction/StudentId.php');
dol_include_once('/flightlog/query/InstructionFlightQuery.php');
dol_include_once('/flightlog/query/InstructionFlightQueryHandler.php');

dol_include_once('/core/lib/project.lib.php');
dol_include_once('/core/lib/date.lib.php');
dol_include_once('/core/class/html.formother.class.php');

$langs->load("projects");
$langs->load("companies");
$langs->load("suppliers");
$langs->load("compta");

global $db, $langs, $user, $conf;

/*
 * Parameters
 */
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$objectives = GETPOST('objective', 'array');
$progressions = GETPOST('progression', 'array');

if ($id == '' && $projectid == '' && $ref == '') {
    dol_print_error('', 'Bad parameter');
    exit;
}


/*
 * Objects
 */
$project = new Project($db);
$project->fetch($id);
$project->fetch_thirdparty();

if ($project->restrictedProjectArea($user) < 0) {
    accessforbidden();
}

$task = new Task($db);
$projectTasks = $task->getTasksArray(null, null, $id);

$adherent = new Adherent($db);
if (0 > $adherent->fetch(null, null, $project->socid)) {
    return;
}

$instructionFlightsHandler = new InstructionFlightQueryHandler($db);
$instructionFlights = $instructionFlightsHandler->__invoke(new InstructionFlightQuery(new StudentId($adherent->user_id)));
$formOther = new FormOther($db);

/*
 * Security
 */
restrictedArea($user, 'projet', $id, 'projet&project');
$userWrite = $project->restrictedProjectArea($user, 'write') || $project->public;

/*
 * Actions
 */

if ($action === "save") {

    foreach ($instructionFlights as $flight) {
        /** @var Task $currentTask */
        foreach ($projectTasks as $currentTask) {
            $flight->deleteObjectLinked($currentTask->id, $currentTask->table_element);
        }

        if (isset($objectives[$flight->getId()])) {
            foreach ($objectives[$flight->getId()] as $currentObjectiveTaskId) {
                $flight->add_object_linked($task->table_element, $currentObjectiveTaskId);
            }
        }
    }

    foreach ($progressions as $taskId => $progressionPercent) {
        $task->fetch($taskId);
        $task->progress = (int) $progressionPercent;

        if ($task->progress <= 100) {
            $task->fetchObjectLinked($task->id, $task->table_element, null, 'flightlog_bbcvols');

            if (empty($task->linkedObjects['flightlog_bbcvols'])) {
                setEventMessages(sprintf('la tâche %s, n\'a pas de vol associé', $task->ref), null, 'warnings');
            } else {
                $earliestDate = null;
                /** @var Bbcvols $currentFlight */
                foreach ($task->linkedObjects['flightlog_bbcvols'] as $currentFlight) {
                    $earliestDate = null === $earliestDate || $currentFlight->date <= $earliestDate ? $currentFlight->date : $earliestDate;
                }

                $task->date_start = $earliestDate;
            }
        }


        if ($task->progress === 100) {
            $task->fetchObjectLinked($task->id, $task->table_element, null, 'flightlog_bbcvols');

            if (empty($task->linkedObjects['flightlog_bbcvols'])) {
                setEventMessages(sprintf('la tâche %s, n\'a pas de vol associé', $task->ref), null, 'warnings');
            } else {
                $latestDate = null;
                /** @var Bbcvols $currentFlight */
                foreach ($task->linkedObjects['flightlog_bbcvols'] as $currentFlight) {
                    $latestDate = $currentFlight->date > $latestDate ? $currentFlight->date : $latestDate;
                }

                $task->date_end = $latestDate;
            }

        }


        $task->update($user);
    }
}

/*
 *	View
 */
llxHeader("", $langs->trans("Vol d'instructions"));

$head = project_prepare_head($project);
dol_fiche_head($head, 'instruction', $langs->trans("Project"), -1, ($project->public ? 'projectpub' : 'project'));

$morehtmlref = '<div class="refidno">';
$morehtmlref .= $project->title;
$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ';
$morehtmlref .= $project->thirdparty->getNomUrl(1, 'project');
$morehtmlref .= '</div>';

dol_banner_tab($project, 'ref', null, false, 'rowid', 'ref', $morehtmlref);
?>
    <div>
        <p>
            Ceci est le <b>tableau de progression</b> de l'élève. Le vol doit être encodé dans le carnet de vol
            <b>avant</b> de figurer dans la liste ci-dessous.<br/>
            Lorsque la progression est mise à <b><=100%</b>, la date de début de la tâche est automatiquement réglée sur
            la date du <b>premier vol</b> de cette tâche.<br/>
            Lorsque la progression est mise à <b>100%</b>, la date de réalisation de la tâche est automatiquement réglée
            sur la date du <b>dernier vol</b> de cette tâche.
        </p>
    </div>

    <form action="instructions.php" method="POST">

        <input type="hidden" value="<?php echo $id; ?>" name="id"/>
        <table class="noborder" width="100%">

            <tr>
                <td></td>
                <td class="center">Progression</td>

                <?php foreach ($instructionFlights as $flight): ?>
                    <td class="center">
                        (ID <?php echo $flight->getNomUrl(); ?>) <br/>
                        <?php echo dol_print_date($flight->date, '%d-%m-%Y'); ?>
                    </td>
                <?php endforeach; ?>
            </tr>

            <?php /** @var Task $currentTask */ ?>
            <?php foreach ($task->getTasksArray(null, null, $id) as $currentTask): ?>

                <tr>
                    <td>
                        <?php echo $currentTask->getNomUrl(1, '', 'task', 20); ?>
                    </td>

                    <td class="center">
                        <?php if ($userWrite): ?>
                            <?php echo $formOther->select_percent($currentTask->progress,
                                sprintf('progression[%s]', $currentTask->id, false, 10)); ?>
                        <?php else: ?>
                            <span><?php echo $currentTask->progress; ?> %</span>
                        <?php endif; ?>

                    </td>

                    <!-- Start flights checkboxes -->
                    <?php foreach ($instructionFlights as $flight): ?>

                        <?php $flight->fetchObjectLinked(null, $task->table_element, $flight->getId(),
                            $flight->element); ?>

                        <td class="center">

                            <?php if ($userWrite): ?>
                                <input type="checkbox"
                                       value="<?php echo $currentTask->id; ?>"
                                       name="objective[<?php echo $flight->getId(); ?>][]"
                                    <?php echo !empty($flight->linkedObjectsIds) && in_array($currentTask->id,
                                        $flight->linkedObjectsIds[$currentTask->table_element]) ? 'checked' : '' ?>
                                />
                            <?php else: ?>

                                <?php if (!empty($flight->linkedObjectsIds) && in_array($currentTask->id,
                                        $flight->linkedObjectsIds[$currentTask->table_element])): ?>
                                    <span class="fa fa-check"></span>
                                <?php else: ?>
                                    <span class="fa fa-times"></span>
                                <?php endif; ?>

                            <?php endif; ?>


                        </td>
                    <?php endforeach; ?>
                </tr>

            <?php endforeach; ?>
        </table>

        <div class="tabsAction">
            <div class="inline-block divButAction">
                <?php if ($userWrite): ?>
                    <button class="butAction" type="submit" name="action" value="save">Sauver</button>
                <?php endif; ?>
            </div>
        </div>
    </form>


<?php
llxFooter();
$db->close();