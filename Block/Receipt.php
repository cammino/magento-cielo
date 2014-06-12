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
	
	public function getOrderXml()
	{
		if (!$this->_orderId) { return false; }
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->_orderId);
		$cielo = Mage::getModel('cielo/default');
		$xml = $cielo->sendXml($cielo->generateXmlQuery($this->_orderId));
		
		if ($xml->status) {
			if ((strval($xml->status) == "2") ||
				(strval($xml->status) == "4") ||
				(strval($xml->status) == "6") ||
				(strval($xml->status) == "10")) {

				$state   = 'pending_payment';
				$status  = 'pending_payment';
				$comment = 'Cartão aprovado, aguardando captura.';
			} else {
				$state = 'canceled';
				$status = 'canceled';
				
				if ( isset($xml->cancelamentos->cancelamento->mensagem) ) { 
					$comment = $xml->cancelamentos->cancelamento->mensagem;
				} else {
					$comment = 'Transação não autorizada.';
				}
			}
		}

		$order->setState($state, $status, $comment, false);
		$order->save();

		if ($status != 'canceled') {
			$order->save();
			$order->sendNewOrderEmail();
		}

		return $xml;
	}

}