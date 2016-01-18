<?php
class Cammino_Cielo_Block_Pay extends Mage_Payment_Block_Form {
	
	private $_error;
	private $_orderId;

	protected function _construct() {

		$cieloData = Mage::registry("cielo_data");
		$this->_error = $cieloData["error"];
		$this->_orderId = $cieloData["order_id"];
		$this->setTemplate("cielo/pay.phtml");
		parent::_construct();
	}

	public function getError() {
		return $this->_error;
	}

	public function getOrderId() {
		return $this->_orderId;
	}
	
}