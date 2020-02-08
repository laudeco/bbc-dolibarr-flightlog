<?php


namespace FlightLog\Infrastructure\Common\Routes;


final class RouteManager
{

    /**
     * @var array|Route[]
     */
    private $routes;

    /**
     * @var \DoliDB
     */
    private $db;

    public function __construct(\DoliDB $db)
    {
        $this->routes = [];
        $this->db = $db;
    }


    /**
     * @param Route $route
     *
     * @throws \Exception
     */
    public function add(Route $route){
        if(isset($this->routes[$route->getName()])){
            throw new \Exception('Route exists');
        }

        $this->routes[$route->getName()] = $route;
    }

    /**
     * @param array $routes
     *
     * @throws \Exception
     */
    public function load(array $routes){
        foreach($routes as $currentRoute){
            $this->add($currentRoute);
        }
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function __invoke($name)
    {
        if(!isset($this->routes[$name])){
            throw new \Exception('Route not found');
        }

        $route = $this->routes[$name];

        $controllerName = $route->getController();
        $controller = new $controllerName($this->db);

        call_user_func([$controller, $route->getMethod()]);
    }



}