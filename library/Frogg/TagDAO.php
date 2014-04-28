<?php
class Frogg_TagDAO extends Frogg_DAO{
	
	public $_content;
	
	public final function __construct(Frogg_Model $content) {
		$this->_content = $content;
	}
	
	public function getTable(){
		return 'FrT_'.$this->_content->getClass();;
	}
	
	public function listPopular($amount=15,$pg=1){
		return $this->search($amount,$pg,'','*','ORDER by amount DESC');
	}
	
	public function search($amount, $page=1, $cond='',$fields='*',$order_by='') {
		$list = parent::search($amount, $page, $cond,$fields,$order_by);
		$this->setContent($list);
		return $list;
	}
	
	public function contentsFromTag($tagID, $amount=Frogg_DAO::ALL, $page=1) {
		$dao = new Frogg_TagContentDAO($this->_content);
		$list = $dao->search($amount, $page, 'WHERE `tagID` = '.$tagID);
		$ids = '';
		foreach ($list as $tagContent) {
			$ids.=$tagContent->contentID.',';
		}
		$ids = rtrim($ids,',');
		$class = get_class($this->_content).'DAO';
		if(!class_exists($class)){
			echo 'Class '.$class.' does not exist. Please create it before continuing.';die;
		}
		$specific_dao = new $class();
		$list = $specific_dao->search(Frogg_DAO::ALL, 1, 'WHERE `id` IN ('.$ids.')');
		
		return $list;
	}
	
	public function navBarContentsFromTag($tagID, $amount=Frogg_DAO::ALL, $page=1){
		$dao = new Frogg_TagContentDAO($this->_content);
		$list = $dao->search(Frogg_DAO::ALL, 1, 'WHERE `tagID` = '.$tagID);
		$ids = '';
		foreach ($list as $tagContent) {
			$ids.=$tagContent->contentID.',';
		}
		$ids = rtrim($ids,',');
		$class = get_class($this->_content).'DAO';
		if(!class_exists($class)){
			echo 'Class '.$class.' does not exist. Please create it before continuing.';die;
		}
		$specific_dao = new $class();
		$list = $specific_dao->navbar($amount, $page, 'WHERE `id` IN ('.$ids.')');
		
		return $list;
	}
	
	public function tagsFromContent($contentID, $amount=Frogg_DAO::ALL, $page=1) {
		$dao = new Frogg_TagContentDAO($this->_content);
		$list = $dao->search($amount, $page, 'WHERE `contentID` = '.$contentID);
		$ids = '';
		foreach ($list as $tagContent) {
			$ids.=$tagContent->tagID.',';
		}
		$ids = rtrim($ids,',');
		$tag_dao = new Frogg_TagDAO($this->_content);
		$list = $tag_dao->search(Frogg_DAO::ALL, 1, 'WHERE `id` IN ('.$ids.')');
		
		return $list;
	}
	
	private function setContent(array &$list){
		foreach ($list as $tag) {
			$tag->setContent($this->_content);
		}
	}
}