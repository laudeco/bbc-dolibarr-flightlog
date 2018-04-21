<?php

require_once __DIR__ . '/../../command/CreateMonthBillCommandHandler.php';
require_once __DIR__ . '/../../command/CreateMonthBillCommand.php';

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class BbcMonthlyFlightsBillCron
{

    /**
     * @var DoliDB
     */
    private $db;

    /**
     * @var stdClass
     */
    private $conf;

    private $langs;

    private $user;

    /**
     * @var CreateMonthBillCommandHandler
     */
    private $handler;

    /**
     * @param DoliDB $db
     */
    public function __construct($db)
    {
        global $conf, $langs, $user;

        $this->db = $db;
        $this->conf = $conf->global;
        $this->langs = $langs;
        $this->user = $user;
        $this->handler = new CreateMonthBillCommandHandler($this->db, $this->conf, $this->user, $this->langs);
    }

    /**
     * Run the cron job.
     *
     * @return int <0 if error
     *
     * @throws Exception
     */
    public function run()
    {
        dol_syslog('Monthly bill generation : Start');

        try {

            if(date('d')>=15){
                dol_syslog('Monthly bill generation : date over');
                return -2;
            }

            $command = new CreateMonthBillCommand(0, '', '', date('Y'), (date('m') - 1));
            $this->handler->handle($command);

            dol_syslog('Monthly bill generation : OK');
            return 0;

        } catch (Exception $e) {
            dol_syslog($e->getMessage(), LOG_ERR);
            dol_syslog('Monthly bill generation : NOK');
            return -1;
        }
    }

}