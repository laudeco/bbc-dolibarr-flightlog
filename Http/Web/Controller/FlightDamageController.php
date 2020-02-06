<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Application\Damage\Command\InvoiceDamageCommand;
use FlightLog\Application\Damage\Command\InvoiceDamageCommandHandler;
use FlightLog\Application\Damage\Query\GetDamagesForFlightQueryRepositoryInterface;
use FlightLog\Infrastructure\Damage\Query\Repository\GetDamagesForFlightQueryRepository;
use FlightLog\Infrastructure\Damage\Repository\FlightDamageRepository;
use Form;

final class FlightDamageController extends WebController
{

    /**
     * @return GetDamagesForFlightQueryRepositoryInterface
     */
    private function getDamagesRepository()
    {
        return new GetDamagesForFlightQueryRepository($this->db);
    }

    /**
     * @return FlightDamageRepository
     */
    private function getFlightDamageRepository()
    {
        return new FlightDamageRepository($this->db);
    }

    /**
     * @return InvoiceDamageCommandHandler
     */
    private function getInvoiceDamageCommandHandler()
    {
        return new InvoiceDamageCommandHandler($this->getFlightDamageRepository());
    }

    public function view()
    {
        $flightId = $this->request->getParam('id');

        $this->render('flight_damage/view.php', [
            'damages' => $this->getDamagesRepository()->__invoke($flightId),
            'flightId' => $flightId,
        ]);
    }

    public function bill()
    {
        $flightId = $this->request->getParam('id');
        $currentDamageId = $this->request->getParam('damage');

        $form = new Form($this->db);

        $url = sprintf('%s/flightlog/card_tab_damage.php?id=%s&damage=%s', DOL_URL_ROOT, $flightId, $currentDamageId);

        $html = $form->formconfirm($url, 'êtes-vous sure de vouloir marquer ce dégât comme facturé?', '','confirm_bill_damage');

        $this->renderHtml($html);
    }

    public function handleInvoice()
    {
        $flightId = $this->request->getParam('id');
        $currentDamageId = $this->request->getParam('damage');
        $confirmation = $this->request->getParam('confirm');

        if(!$this->request->isPost() || $confirmation === 'no'){
            $this->redirect($_SERVER["PHP_SELF"].'?id='.$flightId);
            return;
        }

        $this->getInvoiceDamageCommandHandler()->__invoke(new InvoiceDamageCommand($currentDamageId));
        $this->redirect($_SERVER["PHP_SELF"].'?id='.$flightId);
    }
}