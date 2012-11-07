<?php

class paramModel extends model{
	
	private $fieldKey = 'keyword';
	private $fieldVal = 'value';
	
	private $channelsKey = 'channels';
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get($key){
		return $this->dao->select($this->fieldVal)->from(TABLE_PARAM)
					->where($this->fieldKey)->eq($key)
					->fetch();
	}
	
	public function getOffers(){
		$rets = $this->dao->select('*')->from(TABLE_PARAM)
					 ->where($this->fieldKey)->regexp('^[0-9_]+$')->orderBy('keyword DESC')
					 ->fetchAll();
		return $rets;
	}
	
	public function getChannels(){
		return $this->get($this->channelsKey);
	}
	
	public function update($key, $val){		
		$data[$this->fieldKey] = $key;
		$data[$this->fieldVal] = $val;
		
		$ret = $this->dao->select('*')->from(TABLE_PARAM)
					->where($this->fieldKey)->eq($key)->fetch();
		if($ret){
			$this->dao->update(TABLE_PARAM)->data($data)
				 ->where($this->fieldKey)->eq($key)->exec();
		}else{
			$this->dao->insert(TABLE_PARAM)->data($data)->exec();
		}
		
		return true;
	}
	
	/**
	 * 更新缓存的直播频道列表。
	 * @param array $newChannelArray
	 */
	public function updateTvChannels($newChannels){		
		$value = '';		
		$newChannelArray = explode(',', $newChannels);
		$oldChannel = $this->get($this->channelsKey);		
		if(empty($oldChannel->value)){
			$flag = true;
			$value = implode(',', $newChannelArray);											
		}else{
			$oldChannel = $oldChannel->value;
			$flag = false;			
			$oldChannelArray = explode(',', $oldChannel);
			foreach($newChannelArray as $channel){
				// 将新频道添加到旧频道数组中。
				if(empty($channel)){
					continue;
				}
				
				if(false == strstr($oldChannel, $channel)){
					$flag = true;
					$oldChannelArray[] = $channel;
					
					// TODO 有新的频道加入时，给自己邮箱发个邮件，人工审核该频道是否存在。
				}
			}
						
			if($flag){
				$value = implode(',', $oldChannelArray);
			}
		}
		
		if(! empty($value)){
			$this->update($this->channelsKey, $value);
		}
	}
}