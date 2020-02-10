<?php


namespace FlightLog\Http\Web\Requests;


final class Request
{

    /**
     * @param string $name
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getParam($name, $defaultValue = null){
        if($_POST[$name]){
            return $_POST[$name];
        }

        if($_GET[$name]){
            return $_GET[$name];
        }

        return $defaultValue;
    }

    /**
     * @return bool
     */
    public function isPost(){
        return strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    /**
     * @return array
     */
    public function getPostParameters(){
        return $_POST;
    }
}