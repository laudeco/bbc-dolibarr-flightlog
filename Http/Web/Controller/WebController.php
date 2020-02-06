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

    protected function render($template, array $variables = []){

        foreach($variables as $variableName => $variableValue){
            $GLOBALS[$variableName] = $variableValue;
        }

        return include __DIR__.'/../templates/'.$template;
    }

    /**
     * @param string $html
     */
    protected function renderHtml($html){
        print $html;
    }

    /**
     * @param string $location
     */
    protected function redirect($location)
    {
        if (headers_sent()) {
            echo("<script>location.href='$location'</script>");
            return;
        }

        header("Location: $location");
        exit;
    }

}