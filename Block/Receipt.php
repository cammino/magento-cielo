<?php
class Cammino_Cielo_Block_Receipt extends Mage_Payment_Block_Form {
	
	private $_orderId;
	private $_error;
	private $_tid;
	
	protected function _construct() {

		$cieloData = Mage::registry("cielo_data");
		$this->_error = $cieloData["error"];
		$this->_orderId = $cieloData["order_id"];
		$this->_tid = $cieloData["tid"];

		$this->setTemplate("cielo/receipt.phtml");
		parent::_construct();
	}
	
	public function getOrderId() {
		return $this->_orderId;
	}

	public function getError() {
		return $this->_error;
	}

	public function getTid() {
		return $this->_tid;
	}

}