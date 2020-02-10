<?php


namespace FlightLog\Infrastructure\Common\Routes;


use FlightLog\Http\Web\Controller\WebController;

final class Route
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $method;

    /**
     * @param string $name
     * @param string $controller
     * @param string $method
     */
    public function __construct($name, $controller, $method)
    {
        $this->controller = $controller;
        $this->name = $name;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    public function match($route){
        return $this->name === $route;
    }

}