<?php
class Cammino_Cielo_Block_Pay extends Mage_Payment_Block_Form {
	
	private $_orderId;
	private $_xml;
	private $_error;

	protected function _construct() {

		$session = Mage::getSingleton('checkout/session');
		$order 	 = Mage::getModel("sales/order");
		$cielo   = Mage::getModel('cielo/default');
			
		$this->_orderId = $this->getRequest()->getParam("id");

		if(!$this->_orderId){
			$order->loadByIncrementId($session->getLastRealOrderId());
			$this->_orderId = $order->getRealOrderId();
		}
		
		$this->_xml = $cielo->sendXml($cielo->generateXml($this->_orderId));

		if (strval($this->_xml->tid) != "") {
			$payment = $order->getPayment();
			$addata = unserialize($payment->getData("additional_data"));
			$addata["tid"] = strval($this->_xml->tid);
			$payment->setAdditionalData(serialize($addata))->save();
		}

		$this->setTemplate("cielo/pay.phtml");

		parent::_construct();
	}


	public function getUrlAuth()
	{
		return $this->_xml->{'url-autenticacao'};
	}

	public function getError()
	{
		if (strval($this->_xml->{'url-autenticacao'}) == "") {
			return $this->_xml->{'codigo'} . " - " . $this->_xml->{'mensagem'};
		} else {
			return false;
		}
	}
	
}