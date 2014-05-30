<?php
class Cammino_Cielo_Adminhtml_CieloController extends Mage_Adminhtml_Controller_Action {

	public function queryAction() {
		$orderId = $this->getRequest()->getParam("id");
		$cielo = Mage::getModel('cielo/default');
		$xml = $cielo->sendXml($cielo->generateXmlQuery($orderId));

		$str = str_replace(" ", "&nbsp;&nbsp;&nbsp;&nbsp;", str_replace("\n", "<br/>", htmlentities($xml->asXML())));

		echo "<html><body>" . $str . "</body></html>";
	}

}