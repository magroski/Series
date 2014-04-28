<?php
class Zend_View_Helper_Navbar extends Zend_View_Helper_Abstract{
	
	public function navbar($navbar,$route='pagination',$params=array(),$current=1){
		$tmp ='';

		if(isset($navbar['<'])){
			$params['pg'] = $navbar['<'];
			$tmp .= '<div id="anterior-arrow"></div><a href="'.$this->view->url($params,$route,true).'" id="anterior">ANTERIOR</a>';
		}
		if(isset($navbar['>'])){
			$params['pg'] = $navbar['>'];
			$tmp .= '<div id="proximo-arrow"></div><a href="'.$this->view->url($params,$route,true).'" id="proximo">PRÓXIMO</a>';
		}
		if(!empty($navbar)){
			$tmp .= '<div id="nav-pages">';
			foreach ($navbar as $key => $value){
				if(!is_int($key)) continue;
				if($key == $current){
					$tmp .= '<div id="nav-atual">'.$key.'</div>';
					continue;	
				}
				$params['pg'] = $value;
				$tmp .= '<a href="'.$this->view->url($params,$route,true).'" class="nav-link"  >'.$key.'</a>';
			}
			$tmp .= '</div>';
		}
		return $tmp;
	}
	
}