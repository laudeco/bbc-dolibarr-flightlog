<?php
/**
 *
 */

/**
 * GraphicalType class
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class GraphicalType
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * GraphicalType constructor.
     *
     * @param int $id
     * @param string          $title
     */
    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

}