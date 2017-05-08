<?
/***************************************************************************
* Date				: Apr 30, 2014
* Copywrite			: (c) 2014 by MensGo
* Website			: http://www.mensgo.com
*
* Product Name		: FAQ
* Product Version	: 1.0.1
*
* IMPORTANT: This is a commercial product made by MensGo
* and cannot be modified other than personal use.
*  
* This product cannot be redistributed for free or a fee without written
* permission from MensGo.
*
***************************************************************************/

bx_import('BxDolModuleDb');

class MgFaqDb extends BxDolModuleDb {

	function MgFaqDb(&$oConfig) {
		parent::BxDolModuleDb();
        $this->_sPrefix = $oConfig->getDbPrefix();
    }
	
	function deleteCat($id) {
		$query = "DELETE FROM `mg_faq_cats` WHERE `ID` = '" . $id . "'";
		
		return $this->query($query);
    }
    
    function deleteFaq($id) {
		$query = "DELETE FROM `mg_faq` WHERE `ID` = '" . $id . "'";
		
		return $this->query($query);
    }
	
	function getCat($id) {
		$query = "SELECT * FROM `mg_faq_cats` WHERE `ID` = '" . $id . "' LIMIT 1";
		
		return $this->getRow($query);
    }
	
	function getCats() {
		$query = "SELECT * FROM `mg_faq_cats` ORDER BY `ID`";
		
		return $this->getAll($query);
    }
    
    function getFaq($idLanguage=0) {
		$query = "SELECT * FROM `mg_faq`";
		
		if ($idLanguage) {
			$query .= " WHERE `IDLanguage`=" . $idLanguage;
		}
		
		$query .= " ORDER BY `ID`";
		
		return $this->getAll($query);
    }
	
	function getSetting($name) {
		$query = "SELECT `VALUE` FROM `sys_options`
			INNER JOIN `sys_options_cats` ON `sys_options_cats`.`ID`=`sys_options`.`kateg`
			WHERE `sys_options_cats`.`name` = 'Faq' AND `sys_options`.`Name` = '" . $name . "' LIMIT 1";
		
        return $this->getOne($query);
    }
    
	function getSettingsCategory() {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Faq' LIMIT 1");
    }
	
	function insertCat($picto, $caption) {
		$query = "INSERT INTO `mg_faq_cats`
			SET `Picto`='" . $picto . "', `Caption`='" . $caption . "'";
		
		return $this->query($query);
    }
	
    function insertFaq($idLanguage, $idCat, $question, $answer) {
		$query = "INSERT INTO `mg_faq`
			SET `IDLanguage`='" . $idLanguage . "', `IDCat`='" . $idCat . "', `Question`='" . $question . "', `Answer`='" . $answer . "'";
		
		return $this->query($query);
    }
    
    function searchFaq($q, $idLanguage, $idCat=0) {
		$keywords = htmlspecialchars($q, ENT_QUOTES, "UTF-8");
		$query = "SELECT * FROM `mg_faq` WHERE `IDLanguage`=" . $idLanguage . "
			AND (`Question` LIKE '%" . $keywords . "%' OR `Answer` LIKE '%" . $keywords . "%')";
		
		if ($idCat) {
			$query .= " AND `IDCat`=" . $idCat;
		}
		
		$query .= " ORDER BY `ID`";
		
		return $this->getAll($query);
    }
    
    function searchFaq2($q, $idLanguage, $idCat=0) {
		$dsn = "mysql:host=".$this->host.";dbname=".$this->dbname;
		$params = array(
			'lang' => $idLanguage,
			'search' => htmlspecialchars($q)
		);
		$query = "SELECT * FROM `mg_faq` WHERE `IDLanguage`=:lang
			AND (`Question` LIKE '%:search%' OR `Answer` LIKE '%:search%')";
		if ($idCat) {
			$params['cat'] = $idCat;
			$query .= " AND `IDCat`=:cat";
		}
		$query .= " ORDER BY `ID`";
			
		try {
			$pdo = new PDO($dsn, $this->user, $this->password);
			$pdo->exec("SET CHARACTER SET utf8");
			$req = $pdo->prepare($query);
			$req->execute($params);
			return $req->fetchAll();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		return false;
    }
    
    function searchFaq3($q) {
		try {
			$dsn = "mysql:host=".$this->host.";dbname=".$this->dbname;
			$pdo = new PDO($dsn, $this->user, $this->password);
			$pdo->exec("SET CHARACTER SET utf8");
			$query = "SELECT *, MATCH(Question, Answer) AGAINST (:search) AS score
				FROM mg_faq WHERE MATCH(Question, Answer) AGAINST (:search) ORDER BY score DESC";
			$req = $pdo->prepare($query);
			$req->execute(array(
				'search' => htmlspecialchars($q)
			));
			return $req->fetchAll();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
		
		return fase;
    }
	
	function updateCat($id, $picto, $caption) {
		$query = "UPDATE `mg_faq_cats`
			SET `Picto`='" . $picto . "', `Caption`='" . $caption . "'
			WHERE `ID`='" . $id . "'";
			
		return (int)$this->query($query);
    }
    
    function updateFaq($id, $idLanguage, $idCat, $question, $answer) {
		$query = "UPDATE `mg_faq`
			SET `IDLanguage`='" . $idLanguage . "', `IDCat`='" . $idCat . "', `Question`='" . $question . "', `Answer`='" . $answer . "'
			WHERE `ID`='" . $id . "'";
			
		return (int)$this->query($query);
    }
}

?>
