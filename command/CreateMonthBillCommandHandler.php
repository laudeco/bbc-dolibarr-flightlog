<?php

require_once __DIR__ . '/../query/MonthlyBillableQueryHandler.php';
require_once __DIR__ . '/../query/MonthlyBillableQuery.php';
require_once __DIR__ . '/CreateReceiverMonthBillCommandHandler.php';
require_once __DIR__ . '/CreateReceiverMonthBillCommand.php';
require_once __DIR__ . '/CreateMonthBillCommand.php';
require_once __DIR__ . '/CommandHandlerInterface.php';
require_once __DIR__ . '/CommandInterface.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class CreateMonthBillCommandHandler implements CommandHandlerInterface
{
    /**
     * @var DoliDB
     */
    private $db;

    /**
     * @var stdClass
     */
    private $conf;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Translate
     */
    private $langs;

    /**
     * @var MonthlyBillableQueryHandler
     */
    private $queryHandler;

    /**
     * @var CreateReceiverMonthBillCommandHandler
     */
    private $receiverMonthBillCommandHandler;

    /**
     * @param DoliDB    $db
     * @param stdClass  $conf
     * @param User      $user
     * @param Translate $langs
     */
    public function __construct($db, $conf, $user, $langs)
    {
        $this->db = $db;
        $this->conf = $conf;
        $this->user = $user;
        $this->langs = $langs;
        $this->queryHandler = new MonthlyBillableQueryHandler($db, $conf);
        $this->receiverMonthBillCommandHandler = new CreateReceiverMonthBillCommandHandler($db, $conf, $user, $langs);
    }

    /**
     * @param CommandInterface|CreateMonthBillCommand $command
     *
     * @throws Exception
     */
    public function handle(CommandInterface $command)
    {
        $queryResult = $this->queryHandler->__invoke(new MonthlyBillableQuery(
                $command->getMonth(),
                $command->getYear())
        );

        foreach ($queryResult->getFlights() as $currentReceiver) {
            $this->db->begin();

            try {
                $this->createBill($command, $currentReceiver);
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollback($e->getTraceAsString());
                throw $e;
            }

        }

    }

    /**
     * @param CreateMonthBillCommand|CommandInterface $command
     * @param MonthlyFlightBill                       $currentReceiver
     */
    private function createBill($command, $currentReceiver)
    {
        $receiverCommand = new CreateReceiverMonthBillCommand(
            $currentReceiver,
            $command->getBillingType(),
            $command->getBillType(),
            $command->getBillingCondition(),
            $command->getModelDocument(),
            $command->getPublicNote(),
            $command->getPrivateNote(),
            $command->getYear(),
            $command->getMonth()
        );

        $this->receiverMonthBillCommandHandler->handle($receiverCommand);
    }
}