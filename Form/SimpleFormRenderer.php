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
     * @param FormInterface|FormElementInterface $element
     * @param array                              $options
     *
     * @return string
     */
    public function render($element, $options = [])
    {
        if ($element instanceof FormInterface) {
            return $this->openingForm($element);
        }

        if ($element instanceof FormElementInterface) {
            return $this->renderElement($element, $options);
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
     * @param array                $options
     *
     * @return string
     */
    private function renderElement(FormElementInterface $element, $options)
    {
        switch ($element->getType()) {
            case FormElementInterface::TYPE_TEXTAREA:
                return sprintf('<textarea name="%s" class="%s" %s>%s</textarea>',
                    $element->getName(),
                    $element->hasError() ? 'error' : '',
                    $this->formatOptions($element->getOptions()),
                    $element->getValue()
                );

            case FormElementInterface::TYPE_SELECT:
                /** @var Select $select */
                $select = $element;
                $html = $this->renderSelectElement($select);

                if (isset($options['ajax']) && $options['ajax']) {
                    $html .= ajax_combobox($element->getName());
                }

                return $html;
            case FormElementInterface::TYPE_CHECKBOX:
                return sprintf('<input type="%s" class="%s" name="%s" value="%s" %s %s />',
                    $element->getType(),
                    ' flat ' . ($element->hasError() ? 'error' : ''),
                    $element->getName(),
                    $element->checkedValue(),
                    $element->getValue() ? 'checked' : '',
                    $this->formatOptions(array_merge($element->getOptions(), $options))
                );
            default:
                return sprintf('<input type="%s" class="%s" name="%s" value="%s" %s />',
                    $element->getType(),
                    ' flat ' . ($element->hasError() ? 'error' : ''),
                    $element->getName(),
                    $element->getValue(),
                    $this->formatOptions(array_merge($element->getOptions(), $options))
                );
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
        $selectElement = sprintf('<select id="%s" class="%s" name="%s" >', $element->getId(),
            $element->hasError() ? 'error' : '', $element->getName());

        if ($element->getValueOptions()) {
            foreach ($element->getValueOptions() as $optionValue => $optionLabel) {
                $selectedAttribute = '' . $optionValue === '' . $element->getValue() ? 'selected' : '';
                $selectElement .= sprintf('<option value="%s" %s >%s</option>', $optionValue, $selectedAttribute,
                    $optionLabel);
            }
        }

        $selectElement .= '</select>';

        return $selectElement;
    }


}