<?php
class Cammino_Cielo_Block_Pay extends Mage_Payment_Block_Form {
	
	private $_orderId;
	private $_xml;
	private $_error;
	private $_paymenturl;

	protected function _construct()
	{
		$session = Mage::getSingleton('checkout/session');
		$cielo = Mage::getModel('cielo/default');
		$this->_orderId = $this->getRequest()->getParam("id");

		if(!$this->_orderId) {
			$this->_orderId = $session->getLastRealOrderId();
		}

		$cieloReturn = $cielo->doTransaction($this->_orderId);

		die;

		// if (strval($cieloReturn["paymenturl"]) != "") {
		// 	$this->_paymenturl = $cieloReturn["paymenturl"];
		// 	$this->setTemplate("cielo/pay.phtml");
		// }

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