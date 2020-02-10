<?php


namespace FlightLog\Infrastructure\Common\Routes;


final class Guard
{

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @param string $routeName
     * @param callable $callable
     */
    public function __construct($routeName, callable $callable)
    {
        $this->routeName = $routeName;
        $this->callable = $callable;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param \User $user
     * @return bool
     */
    public function __invoke(\User $user)
    {
        return call_user_func($this->callable, $user);
    }
}