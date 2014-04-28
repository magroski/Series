<?php
class Frogg_TagContentDAO extends Frogg_DAO{
	
	public $_content;
	
	public final function __construct(Frogg_Model $content) {
		$this->_content = $content;
	}
	
	public function getTable(){
		return 'FrTC_'.$this->_content->getClass();;
	}
	
	public function search($amount, $page=1, $cond='',$fields='*',$order_by='') {
		$list = parent::search($amount, $page, $cond,$fields,$order_by);
		$this->setContent($list);
		return $list;
	}
	private function setContent(array &$list){
		foreach ($list as $tag) {
			$tag->setContent($this->_content);
		}
	}
}