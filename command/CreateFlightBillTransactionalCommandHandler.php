<?php

require_once __DIR__ . '/CreateFlightBillCommandHandler.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateFlightBillTransactionalCommandHandler extends CreateFlightBillCommandHandler
{
    /**
     * @inheritDoc
     */
    public function handle(CreateFlightBillCommand $command)
    {
        $this->db->begin();

        try {
            parent::handle($command);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback($e->getTraceAsString());
            throw $e;
        }
    }

}