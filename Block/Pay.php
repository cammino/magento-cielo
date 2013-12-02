<?php
class Cammino_Cielo_Block_Pay extends Mage_Payment_Block_Form {
	
	private $_orderId;
	private $_xml;

	protected function _construct() {

		$session = Mage::getSingleton('checkout/session');
		$order 	 = Mage::getModel("sales/order");
		$cielo   = Mage::getModel('cielo/default');
			
		$this->_orderId = $this->getRequest()->getParam("id");

		if(!$this->_orderId){
			$order->loadByIncrementId($session->getLastRealOrderId());
			$this->_orderId = $order->getRealOrderId();
		}

		
		$this->_xml = $cielo->sendXml($this->_orderId);
		
		$this->setTemplate("cielo/pay.phtml");

		parent::_construct();
	}


	public function getUrlAuth()
	{
		return $this->_xml->{'url-autenticacao'};
	}
	
}