<?php
/***************************************************************************
* Date				: Apr 30, 2014
* Copywrite			: (c) 2014 by MensGo
* Website			: http://www.mensgo.com
*
* Product Name		: FAQ
* Product Version	: 1.0.0
*
* IMPORTANT: This is a commercial product made by MensGo
* and cannot be modified other than personal use.
*  
* This product cannot be redistributed for free or a fee without written
* permission from MensGo.
*
***************************************************************************/

bx_import('BxDolTwigPageMain');

class MgFaqPageMain extends BxDolTwigPageMain
{
    function MgFaqPageMain(&$oMain) {
        parent::BxDolTwigPageMain('mg_faq_main', $oMain);
    }
    
    function getBlockCode_Faq() {
        // On créé le tableau de Q&R complet
        $iLangID = getLangIdByName(getCurrentLangName());
        $aFaq = $this->oMain->_oDb->getFaq($iLangID);
        for ($i = 0; $i < count($aFaq); $i++) {
            $aFaq[$i]['Rank'] = $i + 1;
            $aCat = $this->oMain->_oDb->getCat($aFaq[$i]['IDCat']);
            $aFaq[$i]['bx_if:pictoUrl'] = array(
                'condition' => $aCat['ID'] != 0,
                'content' => array(
                    'url' => $aCat['Picto'],
                    'title' => _t($aCat['Caption'])
                )
            );
            $aFaq[$i]['RowId'] = $aFaq[$i]['Rank'] % 2 > 0 ? "unpair" : "pair";
        }
        
        // FAQ
        $aVars = array (
            'bx_repeat:faq' => $aFaq
        );
        return $this->oMain->_oTemplate->parseHtmlByName('faq', $aVars);
    }

    function getBlockCode_Search() {
        // Chargement des catégories
        $aCats = $this->oMain->_oDb->getCats();
        $aCatChooser = array(array('key' => 0, 'value' => _t("_categ_all")));
        foreach($aCats as $aCat) {
            $value = !empty($aCat['Caption']) ? _t($aCat['Caption']) : $aCat['Picto'];
            $aCatChooser[] = array('key' => $aCat['ID'], 'value' => $value);
        }
        
        // Formulaire de recherche
        $aSearchForm = array(
            'form_attrs' => array(
                'name'     => 'form_search',
                'action'   => '',
                'method'   => 'get',
            ),
            'inputs' => array(
                'q' => array(
                    'type' => 'text',
                    'name' => 'q',
                ),
                'cat' => array(
                    'type' => 'select',
                    'name' => 'cat',
                    'value' => 0,
                    'values' => $aCatChooser,
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                'submit' => array (
                    'type' => 'submit',
                    'name' => 'submit_form',
                    'value' => _t('_Search'),
                ),
            ),
        );
        
        $oSearchForm = new BxTemplFormView($aSearchForm);
        
        // Formulaire de recherche
        $aVars = array (
            'searchForm' => $oSearchForm->getCode(),
        );
        $sResult = $this->oMain->_oTemplate->parseHtmlByName('search', $aVars);
        
        // Recherche par mots et/ou par catégorie
        if (isset($_REQUEST['q'])) {
            $iLangID = getLangIdByName(getCurrentLangName());
            $aSearch = $this->oMain->_oDb->searchFaq($_REQUEST['q'], $iLangID, $_REQUEST['cat']);
            $resultCount = count($aSearch);
            $keywords = htmlspecialchars($_REQUEST['q']);
            
            // Titre suivant le type de recherche
            if ($_REQUEST['cat']) {
                $aCat = $this->oMain->_oDb->getCat($_REQUEST['cat']);
                $searchTitle = _t('_mg_faq_search_title2', $resultCount, $keywords, _t($aCat['Caption']));
            }
            else {
                $searchTitle = _t('_mg_faq_search_title1', $resultCount, $keywords);
            }
            
            // On créé le tableau de résultat en surlignant les mot-clés trouvés
            // On calcul la pertinence de la recherche en attribuant un score pour le nombre de mots trouvés
            // +2 pour chaque mot trouvé dans la question et +1 pour chaque mot trouvé dans la réponse
            $listScore = array();
            $aFaq = $this->oMain->_oDb->getFaq($iLangID);
            for ($i = 0; $i < count($aSearch); $i++) {
                for ($j = 0; $j < count($aFaq); $j++) {
                    if ($aFaq[$j]['ID'] == $aSearch[$i]['ID']) {
                        $aSearch[$i]['Rank'] = $j + 1;
                        break;
                    }
                }
                $words = explode(" ", $_REQUEST['q']);
                $replaceWords = array();
                foreach ($words as $word) {
                    $replaceWords[] = str_ireplace($word, '<span id="keyword">' . $word . '</span>', $word);
                }
                $aSearch[$i]['Question'] = str_ireplace($words, $replaceWords, $aSearch[$i]['Question'], $count1);
                $aSearch[$i]['Answer'] = str_ireplace($words, $replaceWords, $aSearch[$i]['Answer'], $count2);
                $aSearch[$i]['Score'] = $count1 * 2 + $count2;
                $aCat = $this->oMain->_oDb->getCat($aSearch[$i]['IDCat']);
                $aSearch[$i]['bx_if:pictoUrl'] = array(
                    'condition' => $aCat['ID'] != 0,
                    'content' => array(
                        'url' => $aCat['Picto'],
                        'title' => _t($aCat['Caption'])
                    )
                );
                $aSearch[$i]['RowId'] = $aSearch[$i]['Rank'] % 2 > 0 ? "unpair" : "pair";
                $listScore[$i] = $aSearch[$i]['Score'];
            }
            
            // On trie par score
            array_multisort($listScore, SORT_DESC, $aSearch);
            
            // Résultat recherche
            $aVars = array (
                'searchTitle' => $searchTitle,
                'bx_repeat:searchFaq' => $aSearch
            );
            $sResult .= $this->oMain->_oTemplate->parseHtmlByName('faq_search', $aVars);
        }
        
        unset($oSearchForm);
        
        return $sResult;
    }
    
    function getBlockCode_Suggestion() {
        global $site;
        
        $oSuggForm = new BxTemplFormView($this->oMain->getSuggestionForm());
        $oSuggForm->initChecker();
        
        if ($oSuggForm->isSubmittedAndValid()) {
            $aProfil = getProfileInfo(getLoggedId());
            $oEmailTemplates = new BxDolEmailTemplates();
            $aTemplate = $oEmailTemplates->getTemplate('t_faq_suggestion');
            unset($oEmailTemplates);
            $subject = sprintf($aTemplate['Subject'], $aProfil['NickName']);
            $aPlus = array(
                'SenderID' => $aProfil['ID'],
                'SenderName' => $aProfil['NickName'],
                'SenderEmail' => $aProfil['Email'],
                'Message' => nl2br($_REQUEST['Question'])
            );
            $suggEmail = $this->oMain->_oDb->getSetting("mg_faq_suggEmail");
            if (!$suggEmail) {
                $suggEmail = $site['email'];
            }
            sendMail($suggEmail, $subject, $aTemplate['Body'], 0, $aPlus);
            header("Location: " . BX_DOL_URL_ROOT . $this->oMain->_oConfig->getBaseUri());
            exit;
        }
        
        $aVars = array (
            'SuggestionForm' => $oSuggForm->getCode(),
        );
        return $this->oMain->_oTemplate->parseHtmlByName('suggestion', $aVars);
    }
}
