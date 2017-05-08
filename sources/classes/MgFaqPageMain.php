<?php
/***************************************************************************
* Date				: Apr 30, 2014
* Copywrite			: (c) 2014 by MensGo
* Website			: http://www.mensgo.com
*
* Product Name		: FAQ
* Product Version	: 1.0.1
* Last modification : May 20, 2014
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
        $sFeedbackAppear = $this->oMain->_oDb->getSetting('mg_faq_feedback');
        $bFeedbackAppear = $sFeedbackAppear == 'any' || $sFeedbackAppear == 'all_result';
        
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
            $aFaq[$i]['bx_if:feedbackAppear'] = array(
                'condition' => $bFeedbackAppear,
                'content' => array(
                    'id' => $aFaq[$i]['ID']
                )
            );
        }
        
        // FAQ
        $aVars = array (
            'bx_repeat:faq' => $aFaq,
        );
        return $this->oMain->_oTemplate->parseHtmlByName('faq', $aVars);
    }

    function getBlockCode_Search() {
        $sFeedbackAppear = $this->oMain->_oDb->getSetting('mg_faq_feedback');
        $bFeedbackAppear = $sFeedbackAppear == 'any' || $sFeedbackAppear == 'search_result';
        $iLangID = getLangIdByName(getCurrentLangName());
        $oSearchForm = new BxTemplFormView($this->oMain->getSearchForm());
        
        // Formulaire de recherche
        $aVars = array (
            'searchForm' => $oSearchForm->getCode(),
        );
        $sResult = $this->oMain->_oTemplate->parseHtmlByName('search', $aVars);
        
        // Recherche par mots et/ou par catégorie
        if (isset($_REQUEST['q'])) {
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
            
            function getWords() {
                return explode(" ", $_REQUEST['q']);
            }
            
            function getReplaceWords($words) {
                $replaceWords = array();
                foreach ($words as $word) {
                    $replaceWords[] = '<span id="keyword">' . $word . '</span>';
                }
                return $replaceWords;
            }
            
            $words = getWords();
            $replaceWords = getReplaceWords($words);
            
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
                $aSearch[$i]['Question'] = str_ireplace($words, $replaceWords, $aSearch[$i]['Question'], $count1);
                $pattern = "'>([^<]*)'si"; // recherche les textes en dehors des balises
                $aSearch[$i]['Answer'] = preg_replace_callback($pattern, function($matches) {
                    $words = getWords();
                    $replaceWords = getReplaceWords($words);
                    return str_ireplace($words, $replaceWords, $matches[0]);
                }, $aSearch[$i]['Answer'], -1, $count2); 
                //$aSearch[$i]['Answer'] = str_ireplace($words, $replaceWords, $aSearch[$i]['Answer'], $count2);
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
                $aSearch[$i]['bx_if:feedbackAppear'] = array(
                    'condition' => $bFeedbackAppear,
                    'content' => array(
                        'id' => $aSearch[$i]['ID']
                    )
                );
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
        elseif (!empty($_GET['rank'])) {
            $aFaq = $this->oMain->_oDb->getFaq($iLangID);
            for ($i = 0; $i < count($aFaq); $i++) {
                $iRank = $i + 1;
                if ($_GET['rank'] == $iRank) {
                    $aFaq[$i]['Rank'] = $iRank;
                    $aCat = $this->oMain->_oDb->getCat($aFaq[$i]['IDCat']);
                    $aFaq[$i]['bx_if:pictoUrl'] = array(
                        'condition' => $aCat['ID'] != 0,
                        'content' => array(
                            'url' => $aCat['Picto'],
                            'title' => _t($aCat['Caption'])
                        )
                    );
                    $aFaq[$i]['RowId'] = $aFaq[$i]['Rank'] % 2 > 0 ? "unpair" : "pair";
                    $aFaq[$i]['bx_if:feedbackAppear'] = array(
                        'condition' => $bFeedbackAppear,
                        'content' => array(
                            'id' => $aFaq[$i]['ID']
                        )
                    );
                    $aOneFaq = array($aFaq[$i]);
                    break;
                }
            }
            $aVars = array (
                'bx_repeat:faq' => $aOneFaq
            );
            $sResult .= $this->oMain->_oTemplate->parseHtmlByName('faq', $aVars);
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
            $sSender = !empty($aProfil) ? $aProfil['NickName'] : _t('_Guest');
            $subject = sprintf($aTemplate['Subject'], $sSender);
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
        
        unset($oSuggForm);
        
        return $this->oMain->_oTemplate->parseHtmlByName('suggestion', $aVars);
    }
}
