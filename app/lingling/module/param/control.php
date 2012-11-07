<?php
class param extends control{
	public function __construct(){
		parent::__construct();
	}

	public function getVersion(){
		$version = $this->param->get('version');
		$this->assignData($version);
		$this->display();
	}
	
	public function update($key, $val){
		$value = $val;
		if($key == "updateRankTime"){
			if($value == ""){
				$value = date("Y-m-d H:i:s");
			}
		}
		
		$ret = $this->param->update($key, $value);
		$this->assignData($ret);
		$this->display();
	}
	
	/**
	 * 获取所有电视直播信息的外部接口。
	 */
	public function getTvChannels(){
		$ret = $this->param->getChannels();
		$this->assignData($ret);
//		$this->assignData(1);
		$test = json_encode($ret);
		$test = json_encode($test);
		$test = json_encode('aa');
		$test = json_encode($test);
		$this->display();
	}
	
	public function displayOffers(){
		$offers = $this->param->getOffers();
		
		$this->view->offers = $offers;
		$this->display();
	}
}