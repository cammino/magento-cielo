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
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->_orderId);
		$cielo = Mage::getModel('cielo/default');
		$xml = $cielo->sendXml($this->_orderId, 'receipt');
		

		if ($xml->status) {
			if ( $xml->status == 1 || $xml->status == 2 || $xml->status == 4 || $xml->status == 6 || $xml->status == 10 ){
				$state   = 'processing';
				$status  = 'processing';
				$comment = 'Processando pedido.';
			} else {
				$state = 'canceled';
				$status = 'canceled'; //status criado por nós anteriormente.
				if ( isset($xml->cancelamentos->cancelamento->mensagem) ) $comment = $xml->cancelamentos->cancelamento->mensagem; else $comment = 'Pedido cancelado pelo sistema Cielo.';
			}
		}

			$order->setState($state, $status, $comment, false);
			$order->save();



		return $xml;
	}

}