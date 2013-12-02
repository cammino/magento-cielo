<?php
class Cammino_Cielo_Block_Receipt extends Mage_Payment_Block_Form {
	
	private $_orderId;
	
	protected function _construct() {

		$session = Mage::getSingleton('checkout/session');
		$order 	 = Mage::getModel("sales/order");

		$this->_orderId = $this->getRequest()->getParam("id");

		if(!$this->_orderId){
			$order->loadByIncrementId($session->getLastRealOrderId());
			$this->_orderId = $order->getRealOrderId();
		}

		$this->setTemplate("cielo/receipt.phtml");
		parent::_construct();
	}
	
	public function getOrderId() {
		return $this->_orderId;
	}
	
	public function getPayUrl() {
		// return Mage::getUrl('cielo/default/payAction');
	}
	
	public function getStatusPedido()
	{
		if (!$this->_orderId) { return false; }

		$cielo = Mage::getModel('cielo/default');
		return $cielo->sendXml($this->_orderId, 'receipt');
	}

}