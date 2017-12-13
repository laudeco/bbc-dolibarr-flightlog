<?php

require_once __DIR__ . '/CommandInterface.php';

/**
 * Command handler
 */
interface CommandHandlerInterface
{

    /**
     * @param CommandInterface $command
     */
    public function handle(CommandInterface $command);

}