<?php
class Cammino_Cielo_Block_Info extends Mage_Payment_Block_Info {
	
    protected function _construct()
    {
        parent::_construct();
    }

    public function getOrder() {
        return $this->getInfo()->getOrder();
    }
    
    public function getOrderId() {
        return $this->getOrder()->getRealOrderId();
    }

	public function getPayUrl() {
		return Mage::getUrl('cielo/pay', array('id' => $this->getOrderId()));
	}
	
}
