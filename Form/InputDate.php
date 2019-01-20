<?php
/*
 * This file is part of the Adlogix package.
 *
 * (c) Allan Segebarth <allan@adlogix.eu>
 * (c) Jean-Jacques Courtens <jjc@adlogix.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace flightlog\form;


class InputDate extends BaseInput
{
    /**
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = [])
    {
        parent::__construct($name, FormElementInterface::TYPE_DATE, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        if($value instanceof \DateTime){
            return parent::setValue($value->format('Y-m-d'));
        }

        return parent::setValue($value);

    }


}