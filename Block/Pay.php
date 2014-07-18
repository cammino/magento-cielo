<?php
class Cammino_Cielo_Block_Pay extends Mage_Payment_Block_Form {
	
	private $_error;

	protected function _construct() {

		$cieloData = Mage::registry("cielo_data");
		$this->_error = $cieloData["error"];

		$this->setTemplate("cielo/pay.phtml");
		parent::_construct();
	}

	public function getError() {
		return $this->_error;
	}
	
}