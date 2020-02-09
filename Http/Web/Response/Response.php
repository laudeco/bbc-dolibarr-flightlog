<?php


namespace FlightLog\Http\Web\Response;


final class Response
{

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->content;
    }
}