<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Http\Web\Requests\Request;

abstract class WebController
{
    /**
     * @var \DoliDB
     */
    protected $db;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(\DoliDB $db)
    {
        $this->db = $db;
        $this->request = new Request();
    }

    protected function render($view, array $variables = []){

        foreach($variables as $variableName => $variableValue){
            $GLOBALS[$variableName] = $variableValue;
        }

        return include __DIR__.'/../templates/'.$view;
    }

}