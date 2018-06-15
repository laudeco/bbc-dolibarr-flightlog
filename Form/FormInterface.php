<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
interface FormInterface
{

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param FormElementInterface $element
     *
     * @return FormInterface
     */
    public function add(FormElementInterface $element);

    /**
     * @return boolean
     */
    public function validate();

    /**
     * @param \stdClass $object
     *
     * @return FormInterface
     */
    public function bind($object);

    /**
     * @param array $data
     *
     * @return FormInterface
     */
    public function setData(array $data);

    /**
     * @return \stdClass
     */
    public function getObject();

    /**
     * @param string $elementName
     *
     * @return FormElementInterface
     */
    public function getElement($elementName);
}