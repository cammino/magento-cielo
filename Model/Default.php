<?php
class Cammino_Cielo_Model_Default extends Mage_Payment_Model_Method_Abstract {
	
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = false;
	protected $_code = 'cielo_default';
	protected $_formBlockType = 'cielo/form';
	protected $_infoBlockType = 'cielo/info';

	// public function isAvailable($quote = null) {
	// 	if ($this->isTestInProduction()) {
	// 		return parent::isAvailable();
	// 	} else {
	// 		return false;
	// 	}
	// }

    public function assignData($data) {

		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}

		if ($this->getIntegrationType() == "store") {
			$data["cielo_card_number"] = Mage::helper('core')->encrypt($data["cielo_card_number"]);
			$data["cielo_card_security"] = Mage::helper('core')->encrypt($data["cielo_card_security"]);
			$data["cielo_card_expiration"] = Mage::helper('core')->encrypt($data["cielo_card_expiration"]);
		}

		$info = $this->getInfoInstance();
		$info->setAdditionalData(serialize($data));
		
        return $this;
    }

    public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('cielo/default/pay');
	}

	public function getIntegrationType() {
		//if ($this->isTestInProduction()) {
		//	return "store";
		//} else {
			return $this->getConfigdata("integration_type");
		//}
	}

	public function getAffiliation() {
		//if ($this->isTestInProduction()) {
		//	return "1006993069";
		//} else {
			return $this->getConfigdata("cielo_number");
		//}
	}

	public function getAffiliationKey() {
		//if ($this->isTestInProduction()) {
		//	return "25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3";
		//} else {
			return $this->getConfigdata("cielo_key");
		//}
	}

	public function isTestInProduction() {
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		return (isset($customer) && ($customer->getEmail() == "cieloecommerce@cielo.com.br"));
	}

	public function generateXml($orderId) {
		
		$url_return_default = Mage::getUrl('cielo/default/receipt/id/'.$orderId);
		$order = Mage::getModel("sales/order");
		$order->loadByIncrementId($orderId);

		// get payment and add data
		$payment = $order->getPayment();
		$addata = unserialize($payment->getData("additional_data"));
		
		$customer = Mage::getModel("customer/customer");
		$customer->load($order->getCustomerId());
		$billingAddress = $order->getBillingAddress();

		// default for operation
		$cieloNumber = $this->getAffiliation();
		$cieloKey = $this->getAffiliationKey();

		// $cieloAuthTrans = $this->getConfigdata("auth_transition") ? $this->getConfigdata("auth_transition") : 3;
		$cieloAuthTrans	= 3;
		$cieloRetUrl = $this->getConfigdata("url_return") ? $this->getConfigdata("url_return") : $url_return_default;
		$cieloCapture = $this->getConfigdata("capture") ? $this->getConfigdata("capture"):'false';
		$cieloDesc = $this->getConfigdata("description") ? $this->getConfigdata("description") : '';
		$cieloToken = $this->getConfigdata("token") ? $this->getConfigdata("token") : 'false';
		// $cieloPlotsType	= $this->getConfigdata("plots_type") ? $this->getConfigdata("plots_type") : 'L';
		$cieloPlotsType	= 'L';
		
		// payment
		$payMethod = $addata->_data['cielo_type']; // 1- Credit Card / A- Debit Card / 3 - Credit card plots
		$card = $addata->_data['cielo_card']; // visa, master, elo
		$plots = $addata->_data['cielo_plots']; // 1x, 3x, 6x, 12x, 18x, 36x, 56x.

		if (strval($payMethod) == "A") {
			$cieloAuthTrans = 1;
			$plots = 1;
		}

		if (intval($plots) > 1) {
			$payMethod = "2";
		}

		// if (strval($payMethod) == "3") {
		// 	$payMethod = (strval($cieloPlotsType) == "L") ? "2" : "3";
		// }

		// order
		$orderData = str_replace(' ', 'T', $order->_data['created_at']);
		$orderTotal = number_format($order->getTotalDue(), 2, "", "");
		$orderCode = $orderId;
		$orderIp   = $_SERVER["REMOTE_ADDR"];
		
		$xml = '';

		$xml  = '<?xml version="1.0" encoding="ISO-8859-1"?>';
		$xml .= '<requisicao-transacao id="'.$orderId.'" versao="1.2.1">';
    	$xml .= '<dados-ec><numero>'.$cieloNumber.'</numero><chave>'.$cieloKey.'</chave></dados-ec>';

		if ($this->getIntegrationType() == "store") {

			$cardNumber = Mage::helper('core')->decrypt($addata->_data["cielo_card_number"]);
			$cardNumber = str_replace(" ", "", $cardNumber);
			$cardSecurity = Mage::helper('core')->decrypt($addata->_data["cielo_card_security"]);
			$cardExpiration = explode("/", Mage::helper('core')->decrypt($addata->_data["cielo_card_expiration"]));
			$cardExpiration = end($cardExpiration) . array_shift($cardExpiration);

	    	$xml .= '<dados-portador>
	    				<numero>'.$cardNumber.'</numero>
	    				<validade>'.$cardExpiration.'</validade>
	    				<indicador>1</indicador>
	    				<codigo-seguranca>'.$cardSecurity.'</codigo-seguranca>
	    			 </dados-portador>';
	    }

    	$xml .= '<dados-pedido> 
    				<numero>'.$orderId.'</numero>
    				<valor>'.$orderTotal.'</valor>
    				<moeda>986</moeda> 
    				<data-hora>'.$orderData.'</data-hora> 
    				<descricao>[origem:'.$orderIp.']</descricao> 
    				<idioma>PT</idioma> 
    				<soft-descriptor></soft-descriptor>
				</dados-pedido>';
		$xml .= '<forma-pagamento>
    				<bandeira>'.$card.'</bandeira> 
    				<produto>'.$payMethod.'</produto>
    				<parcelas>'.$plots.'</parcelas> 
				 </forma-pagamento>';
		$xml .= '<url-retorno>'.$cieloRetUrl.'</url-retorno> 
    			<autorizar>'.$cieloAuthTrans.'</autorizar>
    			<capturar>'.$cieloCapture.'</capturar> 
    			<campo-livre>'.$cieloDesc.'</campo-livre> 
    			<gerar-token>'.$cieloToken.'</gerar-token>';
		$xml .= '</requisicao-transacao>';		

		return $xml;
	}

	public function generateXmlCapture($orderId, $amount, $tid)
	{
		$cieloNumber = $this->getAffiliation();
		$cieloKey = $this->getAffiliationKey();
		$amount = number_format($amount, 2, "", "");

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<requisicao-captura id="'.$orderId.'" versao="1.2.1">
				<tid>'.$tid.'</tid>
				<dados-ec>
					<numero>'.$cieloNumber.'</numero>
					<chave>'.$cieloKey.'</chave>
				</dados-ec>
				<valor>'. $amount .'</valor>
			</requisicao-captura>';

		return $xml;
	}

	public function sendXml($xmlRequest)
	{
		if($this->getConfigdata("environment") == 'homolog'){
    		$url = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do';
		}else{
    		$url = 'https://ecommerce.cbmp.com.br/servicos/ecommwsec.do';
		}

	    $ch = curl_init();
	    
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,  'mensagem=' . $xmlRequest);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_FAILONERROR, true);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
	    
	    $xmlReturn = curl_exec($ch);
	    curl_close($ch);

	    $this->log("XML Request:\n" . $xmlRequest);
	    $this->log("XML Return:\n" . $xmlReturn);
	    
	    $xml = simplexml_load_string($xmlReturn);

	    return $xml;
	}

	public function generateXmlQueryByOrder($orderId)
	{
		$cieloNumber = $this->getAffiliation();
		$cieloKey = $this->getAffiliationKey();

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<requisicao-consulta-chsec id=\"a51489b1-93d5-437f-bb4f-5b932fade248\" versao=\"1.2.1\">
				<numero-pedido>". $orderId ."</numero-pedido>
				<dados-ec>
					<numero>". $cieloNumber ."</numero><chave>". $cieloKey ."</chave>
				</dados-ec>
			</requisicao-consulta-chsec>";

		return $xml;
	}

	public function generateXmlQueryByTid($tid)
	{
		$cieloNumber = $this->getAffiliation();
		$cieloKey = $this->getAffiliationKey();

		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
			<requisicao-consulta id=\"6fcf758e-bc60-4d6a-acf4-496593a40441\" versao=\"1.2.1\">
				<tid>". $tid ."</tid>
				<dados-ec>
					<numero>". $cieloNumber ."</numero><chave>". $cieloKey ."</chave>
				</dados-ec>
			</requisicao-consulta>";

		return $xml;
	}

	public function doTransaction($orderId)
	{
		$order = Mage::getModel("sales/order")->loadByIncrementId($orderId);
		$payment = $order->getPayment();
		$addata = unserialize($payment->getData("additional_data"));

		if (strval($addata["paymenturl"]) == "") {

			$xml = $this->sendXml($this->generateXml($orderId));

			if ($xml->getName() != "erro") {

				if (strval($xml->tid) != "") {
					$cardNumber = Mage::helper('core')->decrypt($addata["cielo_card_number"]);
					$maskedCardNumber = substr($cardNumber, 0, 6) . str_repeat("*", (strlen($cardNumber)-10)) . substr($cardNumber, -4);

					$addata["tid"] = strval($xml->tid);
					$addata["paymenturl"] = strval($xml->{'url-autenticacao'});
					$addata["cielo_card_number"] = Mage::helper('core')->encrypt($maskedCardNumber);
					$addata["cielo_card_security"] = "";
					$addata["cielo_card_expiration"] = "";
					$payment->setAdditionalData(serialize($addata))->save();
				}

				return array("paymenturl" => $addata["paymenturl"]);

			} else {
				$errorMessage = $xml->codigo . " - " . str_replace("\n", "", $xml->mensagem);


				// cancel order 
				if ($order->getStatus() == "pending") {
					$order->cancel();
					$order->setState('canceled', 'canceled', $errorMessage, false);
					$order->save();
				}

				return array("error" => $errorMessage, 'order_id' => $orderId);
			}

			//if($this->getIntegrationType() == "store") {
			//}

		} else {
			return array("paymenturl" => $addata["paymenturl"]);
		}
	}

	public function processReturn($orderId) {
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$payment = $order->getPayment();
		$addata = unserialize($payment->getData("additional_data"));

		$xml = $this->sendXml($this->generateXmlQueryByTid($addata["tid"]));
		$error = "";
		
		if ($xml->status) {
			if ((strval($xml->status) == "4") ||
				(strval($xml->status) == "6")) {

				$state   = 'pending_payment';
				$status  = 'pending_payment';
				$comment = 'Cartão aprovado, aguardando captura.';
			} else {
				$state = 'canceled';
				$status = 'canceled';
				
				if ( isset($xml->autorizacao) ) { 
					$comment = $xml->autorizacao->mensagem;
				} else if ( isset($xml->cancelamentos->cancelamento) ) { 
					$comment = $xml->cancelamentos->cancelamento->mensagem;
				} else {
					$comment = 'Transação não autorizada.';
				}

				$error = $comment;
			}
		} else {
			if ( isset($xml->erro) ) { 
				$error = $xml->erro->mensagem;
			} else {
				$error = "Erro não identificado.";
			}
		}

		if ($order->getStatus() == "pending") {

			if ($state == 'canceled') {
				$order->cancel();
			}

			$order->setState($state, $status, $comment, false);
			$order->save();

			if ($status != 'canceled') {
				$order->save();
				$order->sendNewOrderEmail();
			}
		}

		return array("tid" => $addata["tid"], "order_id" => $orderId, "error" => $error);
	}

	public function capture(Varien_Object $payment, $amount)
	{
		$order = $payment->getOrder();
		$orderId = $order->getRealOrderId();
		$addata = unserialize($payment->getData("additional_data"));
		$xml = $this->sendXml($this->generateXmlQueryByTid($addata["tid"]));

		if (strval($xml->captura) == "") {
			$tid = $addata["tid"];
			$xml = $this->sendXml($this->generateXmlCapture($orderId, $amount, $tid));

			if (strval($xml->captura) == "") {
				$message = $xml->mensagem;
				Mage::logException($message);
				Mage::throwException($message);
			} else {
				return $this;
			}
		} else {
			return $this;
		}
	}

	private function log($xml)
	{
		$xml = preg_replace("/<dados-portador>.*?<numero>.*?<\/numero>/s", "<dados-portador><numero></numero>", $xml);
		$xml = preg_replace("/<validade>.*?<\/validade>/s", "<validade></validade>", $xml);
		$xml = preg_replace("/<codigo-seguranca>.*?<\/codigo-seguranca>/s", "<codigo-seguranca></codigo-seguranca>", $xml);
		$xml = preg_replace("/<bandeira>.*?<\/bandeira>/s", "<bandeira></bandeira>", $xml);

		Mage::log($xml, null, 'cielo.log');
	}
}