<?php


namespace FlightLog\Http\Web\Controller;


use FlightLog\Http\Web\Requests\Request;
use FlightLog\Http\Web\Response\Redirect;
use FlightLog\Http\Web\Response\Response;

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

    /**
     * @param string $template
     * @param array $variables
     *
     * @return Response
     */
    protected function render($template, array $variables = []){

        foreach($variables as $variableName => $variableValue){
            $GLOBALS[$variableName] = $variableValue;
        }

        return new Response(__DIR__.'/../templates/'.$template);
    }

    /**
     * @param string $html
     */
    protected function renderHtml($html){
        print $html;
    }

    /**
     * @param string $location
     *
     * @return Redirect
     */
    protected function redirect($location)
    {
        return Redirect::create($location);
    }

}