<?php
class Cammino_Cielo_Block_Form extends Mage_Payment_Block_Form {
	
	protected function _construct() {
		$this->setTemplate('cielo/form.phtml');
		parent::_construct();
	}
	
}