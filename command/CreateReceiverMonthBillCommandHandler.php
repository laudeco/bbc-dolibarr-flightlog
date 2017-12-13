<?php

require_once __DIR__ . '/../class/bbcvols.class.php';
require_once __DIR__ . '/CreateReceiverMonthBillCommand.php';
require_once __DIR__ . '/../../product/class/product.class.php';
require_once __DIR__ . '/../../compta/facture/class/facture.class.php';
require_once __DIR__ . '/../../user/class/user.class.php';
require_once __DIR__ . '/../../adherents/class/adherent.class.php';
require_once __DIR__ . '/AbstractBillCommandHandler.php';
require_once __DIR__ . '/CommandInterface.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateReceiverMonthBillCommandHandler extends AbstractBillCommandHandler
{

    /**
     * @param CreateReceiverMonthBillCommand|CommandInterface $command
     */
    public function handle(CommandInterface $command)
    {
        $object = new Facture($this->db);
        $object->fetch_thirdparty();

        $object->socid = $this->getCustomer($command->getReceiverId())->id;
        $object->type = $command->getBillingType();
        $object->number = "provisoire";
        $object->date = $this->generateBillDate($command->getYear(), $command->getMonth());
        $object->date_pointoftax = "";
        $object->note_public = $command->getPublicNote();
        $object->note_private = $command->getPrivateNote();
        $object->ref_client = "";
        $object->ref_int = "";
        $object->modelpdf = $command->getModelDocument();
        $object->cond_reglement_id = $command->getBillingCondition();
        $object->mode_reglement_id = $command->getBillType();
        $object->fk_account = $this->getBankAccount();

        $id = $object->create($this->user);

        if ($id <= 0) {
            throw new \InvalidArgumentException('Error during bill creation');
        }

        $flightProduct = $this->getProduct();
        foreach ($command->getFlights() as $flight) {
            $this->addOrderLine($object, $flightProduct, $flight);
            $this->addLinks($object, $flight);
            $this->flagFlightAsBilled($flight);
        }

        $this->generateBillDocument($command, $object, $id);

        $this->validates($object, $id);

        $this->generateBillDocument($command, $object, $id);
    }


    /**
     * @return int
     */
    private function getBankAccount()
    {
        return $this->conf->BBC_DEFAULT_BANK_ACCOUNT;
    }

    /**
     * @param int $year
     * @param int $month
     *
     * @return int
     */
    private function generateBillDate($year, $month)
    {
        $day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $date = (new DateTime())->setDate($year, $month, $day);
        return $date->getTimestamp();
    }
}