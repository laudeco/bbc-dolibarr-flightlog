<?php

require_once __DIR__ . '/../class/instruction/StudentId.php';

/**
 * Query class to get all instructions flight of a user.
 *
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class InstructionFlightQuery
{

    /**
     * @var StudentId
     */
    private $studentId;

    /**
     * @param StudentId $studentId
     */
    public function __construct(StudentId $studentId)
    {
        $this->studentId = $studentId;
    }

    /**
     * @return int
     */
    public function getStudentId()
    {
        return $this->studentId->getId();
    }
}