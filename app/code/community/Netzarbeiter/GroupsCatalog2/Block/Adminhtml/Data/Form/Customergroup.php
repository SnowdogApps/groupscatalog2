<?php
/**
 * Netzarbeiter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupsCatalog2
 * @copyright  Copyright (c) 2013 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_GroupsCatalog2_Block_Adminhtml_Data_Form_Customergroup
    extends Varien_Data_Form_Element_Multiselect
{
    /**
     * Set the default value to USE_DEFAULT. This is needed if the extension is is installed
     * after products already where created.
     *
     * @return int
     */
    public function getValue()
    {
        // Don't use parent::getValue(); since some PHP versions don't map that to __call()
        $value = $this->getData('value');
        if (!is_null($value) && !is_array($value)) {
            $value = explode(',', (string)$value);
        }
        if (empty($value)) {
            $value = array(Netzarbeiter_GroupsCatalog2_Helper_Data::USE_DEFAULT);
        }

        return $value;
    }
}
