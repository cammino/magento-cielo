<?php
class Cammino_Cielo_Adminhtml_CieloController extends Mage_Adminhtml_Controller_Action {

	public function queryAction() {
		$orderId = $this->getRequest()->getParam("id");
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$cielo = Mage::getModel('cielo/default');
		$payment = $order->getPayment();
		$addata = unserialize($payment->getData("additional_data"));
		$xml = $cielo->sendXml($cielo->generateXmlQueryByTid($addata["tid"]));

		$str = json_encode($xml, JSON_PRETTY_PRINT);
		$str = str_replace("\n", "<br/>", $str);
		$str = str_replace("    ", "&nbsp;&nbsp;&nbsp;&nbsp;", $str);

		echo "<html><body>" . $str . "</body></html>";
	}

}