<?php 

class vote extends control{
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$this->view->header->title = @"本轮投票";
		$date = $this->vote->createNextRoundVoteRecord();
		$currentVote=$this->vote->getCurrentVote($date);
		$this->basketball = $currentVote[$this->vote->basketball];
		$this->football = $currentVote[$this->vote->football];
		$this->busy = $currentVote[$this->vote->busy];
		$this->date = $date;
		
		$this->historys = $this->vote->getHistorys();
		
		$this->display();
	}
	
	function vote($game, $date, $checkcode){
		$ret = $this->vote->vote($game, $date, $checkcode);
		die(js::locate($this->createLink('vote', 'index')));
	}
}

?>