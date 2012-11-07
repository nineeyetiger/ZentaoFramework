<?php

class ringModel extends model{
	
	public function get($teamId = ''){
		if($teamId == ''){
			return $this->dao->select('*')->from(TABLE_RING)->fetchAll();
		}else{
			return $this->dao->select('*')->from(TABLE_RING)->where('teamId')->eq($teamId)->fetchAll();
		}
	}
	
	public function getSpecificRing($tournamentId, $teamId){
		// 查询球队专属铃声。
		$ret = $this->dao->select('*')->from(TABLE_RING)
					->where('teamId')->eq($teamId)
					->andWhere('type')->eq('1')
					->fetchAll();
					
		// 球队专属铃声不存在时，查询球队所在赛事铃声。
		if(empty($ret)){			
			$ret = $this->dao->select('*')->from(TABLE_RING)
						->where('teamId')->eq($tournamentId)
						->andWhere('type')->eq('4')
						->fetchAll();
		}
		
		return $ret;
	}
	
	public function add($filename, $storedDir, $type, $teamId){
		$ret=$this->dao->select('*')->from(TABLE_RING)
			           ->where('name')->eq($filename)
			           ->andWhere('type')->eq($type)
			           ->andWhere('teamId')->eq($teamId)->fetch();
		if(! $ret){
			$data->name=$filename;
			$data->url=$storedDir;
			$data->type=$type;
			$data->teamId=$teamId;
			$this->dao->insert(TABLE_RING)->data($data)->exec();
			return $this->dao->lastInsertID();
		}else{
			return false;
		}
	}
}
