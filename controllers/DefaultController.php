<?php
class Cammino_Cielo_DefaultController extends Mage_Core_Controller_Front_Action {
	
	public function receiptAction() {

		$session = Mage::getSingleton('checkout/session');
		$cielo = Mage::getModel('cielo/default');
		$orderId = $this->getRequest()->getParam("id");

		if(!$orderId) {
			$orderId = $session->getLastRealOrderId();
		}

		$cieloData = $cielo->processReturn($orderId);
		Mage::register("cielo_data", $cieloData);

		$block = $this->getLayout()->createBlock('cielo/receipt');
		$this->loadLayout();
		$this->analyticsTrack();
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}
	
	public function payAction() {
		$session = Mage::getSingleton('checkout/session');
		$cielo = Mage::getModel('cielo/default');
		$orderId = $this->getRequest()->getParam("id");

		if(!$orderId) {
			$orderId = $session->getLastRealOrderId();
		}

		$cieloData = $cielo->doTransaction($orderId);
		Mage::register("cielo_data", $cieloData);

		if ( !isset($cieloData["error"]) || (strval($cieloData["error"]) == "")) {
			$url = "";
			if (strval($cieloData["paymenturl"]) != "") {
				$url = $cieloData["paymenturl"];
			} else {
				$url = Mage::getUrl('cielo/default/receipt', array('id' => $orderId));
			}
			Mage::app()->getFrontController()->getResponse()->setRedirect($url)->sendResponse();
		}

		$block = $this->getLayout()->createBlock('cielo/pay');

		$this->loadLayout();
		$this->analyticsTrack();
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();	
	}

	private function analyticsTrack() {
		$session = Mage::getSingleton('checkout/session');
		$orderId = $session->getLastOrderId();
		Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($orderId)));
	}

}