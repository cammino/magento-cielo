<?php
class Cammino_Cielo_Block_Info extends Mage_Payment_Block_Info {
    
    protected $_infoCielo;

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('cielo/info.phtml');
    }

    protected function _convertAdditionalData() {

        $details = @unserialize( $this->getInfo()->getData("additional_data") );

        if (is_object($details)) {
             $this->_infoCielo = $details;
        }
        
        return $this;
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

    /**
    * @return Object
    **/
    public function getInfoCielo() {
        
        if (empty($this->_infoCielo)) {
            $this->_convertAdditionalData();
        }

        return $this->_infoCielo;
    }

    /**
    * @return String
    **/
    public function getCieloPlotValue($plots) {
        
        $grand_total = 0;

        if ($this->getOrder()) {
            $grand_total = $this->getOrder()->getGrandTotal();
        } else {
            $totals = $this->getInfo()->getQuote()->getTotals();
            $grand_total = $totals['grand_total']->_data['value'];
        }

        $total_plot  = ($plots) ? $grand_total / $plots : $grand_total;

        return Mage::helper('core')->currency($total_plot, true, false);
    }
}