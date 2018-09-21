<?php
/**
 *
 */

namespace flightlog\form;

use ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
abstract class Form implements FormInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $options;

    /**
     * @var \stdClass|null
     */
    private $object;

    /**
     * @var null|\ValidatorInterface
     */
    private $validator;

    /**
     * @var array|FormElementInterface[]
     */
    private $elements;

    /**
     * Form constructor.
     *
     * @param string $name
     * @param string $method
     * @param array  $options
     */
    public function __construct($name, $method = FormInterface::METHOD_GET, array $options = [])
    {
        $this->name = $name;
        $this->method = $method;
        $this->options = $options;
        $this->elements = [];
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(){
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function add(FormElementInterface $element)
    {
        Assert::keyNotExists($this->elements, $element->getName(), 'Element already exists');
        $this->elements[$element->getName()] = $element;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if(!$this->validator){
            return true;
        }

        if(null === $this->object){
            throw new \InvalidArgumentException('Object not bound');
        }

        $validation = $this->validator->isValid($this->object, $_REQUEST);
        
        if(!$validation){
            foreach($this->elements as $fieldName => $field){
                $field->setErrors($this->validator->getError($fieldName));
            }
        }

        return $validation;
    }

    /**
     * @return array|string[]
     */
    public function getErrorMessages(){
        return $this->validator->getErrors();
    }

    /**
     * @param null|\ValidatorInterface $validator
     *
     * @return Form
     */
    protected function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function bind($object)
    {
        foreach($this->elements as $element){
            $name = $this->camelCase($element->getName());
            $methodName = 'get'.$name;
            if(!method_exists($object, $methodName)){
                continue;
            }

            $element->setValue($object->{$methodName}());
        }

        $this->object = $object;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data)
    {
        foreach($data as $fieldName => $currentData){
            if(!key_exists($fieldName, $this->elements) || $this->elements[$fieldName]->isDisabled()){
                continue;
            }

            $this->elements[$fieldName]->setValue($currentData);

            $methodName = 'set'.$this->camelCase($fieldName);
            if(null === $this->object){
                continue;
            }

            if(method_exists($this->object, $methodName)){
                $this->object->{$methodName}($currentData);
                continue;
            }

            if(property_exists($this->object, $fieldName)){
                $this->object->{$fieldName} = $currentData;
                continue;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @inheritDoc
     */
    public function getElement($elementName)
    {
        if(!key_exists($elementName, $this->elements)){
            throw new \InvalidArgumentException(sprintf('Element %s not found ', $elementName));
        }

        return $this->elements[$elementName];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function camelCase($name)
    {
        $str = str_replace('_', '', ucwords($name, '-'));
        return  lcfirst($str);
    }

}