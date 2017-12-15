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
        if (!($command instanceof CreateReceiverMonthBillCommand)) {
            throw new \InvalidArgumentException('Command not correct');
        }

        $object = new Facture($this->db);
        $object->fetch_thirdparty();

        $object->socid = $this->fetchCustomer($command->getReceiverId())->id;
        $object->type = $command->getBillType();
        $object->number = "provisoire";
        $object->date = $this->generateBillDate($command->getYear(), $command->getMonth());
        $object->date_pointoftax = "";
        $object->note_public = $command->getPublicNote();
        $object->note_private = $command->getPrivateNote();
        $object->ref_client = "";
        $object->ref_int = "";
        $object->modelpdf = $this->getModelDocument();
        $object->cond_reglement_id = $this->getBillingCondition();
        $object->mode_reglement_id = $this->getBillingType();
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

        $this->generateBillDocument($object, $id);

        $this->validates($object, $id);

        $this->generateBillDocument($object, $id);
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

    /**
     * Get the default billing condition if not set in the customer.
     *
     * @return int
     */
    private function getBillingCondition()
    {
        if (!empty($this->getCustomer()->cond_reglement_id)) {
            return $this->getCustomer()->cond_reglement_id;
        }

        if (!empty($this->conf->BBC_DEFAULT_PAYMENT_TERM_ID)) {
            return $this->conf->BBC_DEFAULT_PAYMENT_TERM_ID;
        }

        throw new \InvalidArgumentException('Billing condition / MAIN_DEFAULT_PAYMENT_TERM_ID not set');
    }

    /**
     * Get the default billing type if not set in the customer.
     *
     * @return int
     */
    private function getBillingType()
    {
        if (!empty($this->getCustomer()->mode_reglement_id)) {
            return $this->getCustomer()->mode_reglement_id;
        }

        if (!empty($this->conf->BBC_DEFAULT_PAYMENT_TYPE_ID)) {
            return $this->conf->BBC_DEFAULT_PAYMENT_TYPE_ID;
        }

        throw new \InvalidArgumentException('Billing type / MAIN_DEFAULT_PAYMENT_TYPE_ID not set');
    }


}