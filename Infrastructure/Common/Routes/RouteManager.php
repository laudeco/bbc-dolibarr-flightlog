<?php


namespace FlightLog\Infrastructure\Common\Routes;


use FlightLog\Http\Web\Response\Redirect;
use FlightLog\Http\Web\Response\Response;
use User;

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

    /**
     * @var Guard[]|array
     */
    private $guards;

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
     * @param array|Guard[] $routesGuards
     */
    public function loadGuards($routesGuards)
    {
        foreach($routesGuards as $guard){
            $this->guards[$guard->getRouteName()] = $guard;
        }
    }

    /**
     * Is the user authorized to reach the endpoint?
     * @param User $user
     * @param $routeName
     * @return bool
     */
    public function isAuthorized(User $user, $routeName){
        if(!isset($this->guards[$routeName])){
            return true;
        }

        return $this->guards[$routeName]->__invoke($user);
    }

    /**
     * @param string $name
     *
     * @param User $user
     *
     * @return Redirect|Response
     *
     * @throws \Exception
     */
    public function __invoke($name, User $user)
    {
        if(!isset($this->routes[$name])){
            throw new \Exception('Route not found');
        }

        if(!$this->isAuthorized($user, $name)){
            throw new \Exception('Action not allowed');
        }

        $route = $this->routes[$name];

        $controllerName = $route->getController();
        $controller = new $controllerName($this->db);

        return call_user_func([$controller, $route->getMethod()]);
    }


}