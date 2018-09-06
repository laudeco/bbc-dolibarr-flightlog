<?php
/**
 *
 */

namespace flightlog\form;

/**
 * @author Laurent De Coninck <lau.deconinck@gmail.com>
 */
class SimpleFormRenderer
{
    /**
     * @inheritDoc
     */
    public function render($element)
    {
        if ($element instanceof FormInterface) {
            return $this->openingForm($element);
        }

        if ($element instanceof FormElementInterface) {
            return $this->renderElement($element);
        }

        throw new \InvalidArgumentException('unsupported type');
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    public function openingForm(FormInterface $form)
    {
        return sprintf('<form name="%s" method="%s">', $form->getName(), $form->getMethod());
    }

    /**
     * @return string
     */
    public function closingForm()
    {
        return '</form>';
    }

    /**
     * @param FormElementInterface $element
     *
     * @return string
     */
    private function renderElement(FormElementInterface $element)
    {
        switch($element->getType()){
            case FormElementInterface::TYPE_TEXTAREA:
                return sprintf('<textarea name="%s" %s>%s</textarea>', $element->getName(), $this->formatOptions($element->getOptions()), $element->getValue());

            case FormElementInterface::TYPE_SELECT:
                return $this->renderSelectElement($element);

            default:
                return sprintf('<input type="%s" name="%s" value="%s" %s />', $element->getType(), $element->getName(), $element->getValue(), $this->formatOptions($element->getOptions()));
        }
    }

    /**
     * Format the attributes options of the element.
     *
     * @param array $options
     *
     * @return string
     */
    private function formatOptions($options)
    {
        if (!isset($options['attr'])) {
            return '';
        }

        if (!is_array($options['attr'])) {
            return $options['attr'];
        }

        $attributes = '';
        foreach ($options['attr'] as $attributeKey => $attributeValue) {
            $attributes .= sprintf(' %s = %s', $attributeKey, $attributeValue);
        }

        return $attributes;
    }

    /**
     * @param Select $element
     *
     * @return string
     */
    private function renderSelectElement(Select $element)
    {
        $selectElement = sprintf('<select name="%s" >', $element->getName());

        foreach($element->getValueOptions() as $optionValue => $optionLabel){
            $selectedAttribute = $optionValue === $element->getValue() ? 'selected' : '';
            $selectElement .= sprintf('<option value="%s" %s >%s</option>', $optionValue, $selectedAttribute, $optionLabel);
        }

        $selectElement.='</select>';

        return $selectElement;
    }


}