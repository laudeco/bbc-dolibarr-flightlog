<?php
/**
 *
 */

/**
 * User id of the student.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class StudentId
{

    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        if ((int) $id <= 0) {
            throw new InvalidArgumentException('Invalid Student ID');
        }

        $this->id = (int) $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}