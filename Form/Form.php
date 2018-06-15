<?php
/**
 *
 */

namespace flightlog\form;

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
        if(key_exists($element->getName(), $this->elements)){
            return;
        }

        $this->elements[$element->getName()] = $element;
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
            throw new \InvalidArgumentException('Object not bind');
        }

        return $this->validator->isValid($this->object);
    }

    /**
     * @inheritDoc
     */
    public function bind($object)
    {
        foreach($this->elements as $element){
            $name = $element->getName();

            if(!property_exists($object, $name)){
                throw new \InvalidArgumentException(sprintf('Property %s not found in the object ', $name));
            }

            $element->setValue($object->{$name});
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
            if(!key_exists($fieldName, $this->elements)){
                continue;
            }

            $this->elements[$fieldName]->setValue($currentData);

            if(null === $this->object || !property_exists($this->object, $fieldName)){
                continue;
            }

            $this->object->{$fieldName} = $currentData;
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

}