<?php


namespace FlightLog\Http\Web\Response;


final class Redirect
{

    /**
     * @var string
     */
    private $url;

    /**
     * @param string $url
     */
    private function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @param string $url
     *
     * @return Redirect
     */
    public static function create($url){
        return new self($url);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}