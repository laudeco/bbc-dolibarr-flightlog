<?php
/**
 *
 */

/**
 * A tab for card view
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class Tab
{

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $url;

    /**
     * Tab constructor.
     *
     * @param string $title
     * @param string $name
     * @param string $url
     */
    public function __construct($title, $name, $url)
    {
        $this->title = $title;
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            $this->url,
            $this->title,
            $this->name,
        ];
    }
}