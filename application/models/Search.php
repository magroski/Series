<?php

class Application_Model_Search extends Frogg_DAO{
	
	public function searchKey($key, $pg = 1, $qtde = Frogg_DAO::ALL){
		$busca = "
			(
				SELECT
					* , MATCH (name) AGAINST ('".mysql_escape_string($key)."' IN NATURAL LANGUAGE MODE) AS rank
				FROM 
					seriesbucket
				WHERE
					MATCH (name) AGAINST ('".mysql_escape_string($key)."' IN NATURAL LANGUAGE MODE)
				ORDER BY 
					rank DESC
			)
		";
		$limit = (($qtde==self::ALL)?'':' LIMIT '.(($pg-1)*$qtde).' , '.$qtde);
		$sql = new Frogg_Db_Sql($busca.$limit);
		
		$lista = array();		
		if($sql->rows()){
			while($row = $sql->fetch()){
				$tmp = new Application_Model_SeriesBucket();
				$tmp->loadData($row);
				array_push($lista, $tmp);
			}
		}
		
		return $lista;
	}

}