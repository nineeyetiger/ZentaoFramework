<?php

class ring extends control{
	public function __contruct(){
		parent::__contruct();
	}
	
	public function getRing($tournamentId, $teamId){
		$ret = $this->ring->getSpecificRing($tournamentId, $teamId);
		$this->assignData($ret);
		$this->display();
	}
	
	public function getTeamRing($teamId){
		$ret=$this->ring->get($teamId);
		$this->assignData($ret);
		$this->display();
	}
	
	public function getAll(){
		$ret=$this->ring->get();
		$this->assignData($ret);
		$this->display();
	}
	
	
	public function add(){	
		if(! empty($_POST)){
			if($_FILES['ring']['error'] > 0){
				$this->view->header->title="failed";
			}else{			
				$filename=$_FILES['ring']['name'];
				$tmpDir=$_FILES['ring']['tmp_name'];			
				$filesize=$_FILES['ring']['size'];
				$filetype=$_FILES['ring']['type'];
				if($filetype != 'audio/mp3'){
					die(js::error($this->lang->ring->ringMustBeMp3) 
						. js::locate('index.php?m=ring&f=add'));
				}
				
				// 保存临时文件到特定位置。
				$storedDir=$this->storeToSAE($tmpDir, $filename);
				
				$ringType=$this->getType($_POST['type']);
				$teamId=$_POST['teamId'];
				
				if($ringType == TEAM_RING && empty($teamId)){
					die(js::error($this->lang->ring->teamIdMustBeSet) 
						. js::locate('index.php?m=ring&f=add'));
				}
				// 插入一条铃声数据。
				$pos=strrpos($filename, '.');
				$filename=substr($filename, 0, $pos);
				$this->ring->add($filename, $storedDir, $ringType, $teamId);
			
				$this->view->filename=$filename;
				$this->view->ringType=$ringType;
				$this->view->tmpDir=$tmpDir;
				$this->view->dstPath = $storedDir;
			}
		}else{
			$this->view->header->title=$this->lang->ring->add;
		}
		$this->display();
	}
	
	private function getType($type){
		if($type == "team"){
			return TEAM_RING;
		}else if($type == "android"){
			return ANDROID_RING;
		}else if($type == "ios"){
			return IOS_RING;
		}else{
			return 0;
		}
	}
	
	private function storeFile($tmpFile, $filename){
		global $app;		
		$tmp = $app->getBasePath();
		$dstPath = $tmp."ring/".$filename;
		@copy($tmpFile, $dstPath);
		
		return $dstPath;
	}
	
	private function storeToSAE($tmpFile, $filename){
		$domain = "ring";
		
		$s = new SaeStorage();		
		$s->upload($domain, $filename, $tmpFile);
		$dstPath=$s->getUrl($domain, $filename);
		
		$pos = strrpos($dstPath, '/');
		$url1 = substr($dstPath, 0, $pos + 1);
		$url2 = urlencode(substr($dstPath, $pos + 1));
		
		return $url1 . $url2;
	}
}