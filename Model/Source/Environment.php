<?php
class Cammino_Cielo_Model_Source_Environment {
	
	public function toOptionArray() {
		return array(
			array(
				"value" => "production",
				"label" => "Produção"
			),
			array(
				"value" => "homolog",
				"label" => "Homologação"
			)
		);
	}
}