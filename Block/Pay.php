<?php
class Cammino_Cielo_Block_Pay extends Mage_Payment_Block_Form {
	
	private $_orderId;
	private $_xml;
	private $_error;
	private $_paymenturl;

	protected function _construct() {

		$session = Mage::getSingleton('checkout/session');
		$order 	 = Mage::getModel("sales/order");
		$cielo   = Mage::getModel('cielo/default');
			
		$this->_orderId = $this->getRequest()->getParam("id");

		if(!$this->_orderId) {
			$this->_orderId = $session->getLastRealOrderId();
		}

		$order->loadByIncrementId($this->_orderId);
		$payment = $order->getPayment();
		$addata = unserialize($payment->getData("additional_data"));
		
		if (strval($addata["paymenturl"]) == "") {
			$this->_xml = $cielo->sendXml($cielo->generateXml($this->_orderId));

			if (strval($this->_xml->tid) != "") {
				$addata["tid"] = strval($this->_xml->tid);
				$addata["paymenturl"] = strval($this->_xml->{'url-autenticacao'});
				$payment->setAdditionalData(serialize($addata))->save();
			}				
		}

		$this->_paymenturl = $addata["paymenturl"];
		$this->setTemplate("cielo/pay.phtml");

		parent::_construct();
	}


	public function getUrlAuth()
	{
		return $this->_paymenturl;
	}

	public function getError()
	{
		if (strval($this->_paymenturl) == "") {
			try {
				$message = $this->_xml->{'codigo'} . " - " . $this->_xml->{'mensagem'};
				return $message; 
			} catch(Exception $e) {
				return $e->getMessage();
			}
		} else {
			return false;
		}
	}
	
}