<?php
class Cammino_Cielo_Model_Source_Integrationtype {
	
	public function toOptionArray() {
		return array(
			array(
				"value" => "cielo",
				"label" => "ByPage Cielo"
			),
			array(
				"value" => "store",
				"label" => "ByPage Loja"
			)
		);
	}
}