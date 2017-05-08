<?php
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

bx_import('BxDolModule');

class MgFaqModule extends BxDolModule {

    function MgFaqModule(&$aModule) {        
        parent::BxDolModule($aModule);
    }
    
    function actionAdministration ($sUrl='faq') {
        if (!$GLOBALS['logged']['admin']) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }
        
        $this->_oTemplate->pageStart();
        
        $aMenu = array(
            'faq' => array(
                'title' => _t('_mg_faq'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/faq',
                '_func' => array (),
            ),
            'faq-new' => array(
                'title' => _t('_mg_faq_insert'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/faq-new',
                '_func' => array (),
            ),
            'cats' => array(
                'title' => _t('_Categories'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/cats',
                '_func' => array (),
            ),
            'cat-new' => array(
                'title' => _t('_mg_faq_cat_insert'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/cat-new',
                '_func' => array (),
            ),
            'settings' => array(
                'title' => _t('_Settings'),
                'href' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'administration/settings',
                '_func' => array (),
            ),
        );

        if (empty($aMenu[$sUrl])) {
            $sUrl = 'faq';
        }

        $aMenu[$sUrl]['active'] = 1;
        
        // Chargement des catégories
        $aCats = $this->_oDb->getCats();
        $aCatChooser = array(array('key' => 0, 'value' => _t("_None")));
        foreach($aCats as $aCat) {
            $value = !empty($aCat['Caption']) ? _t($aCat['Caption']) : $aCat['Picto'];
            $aCatChooser[] = array('key' => $aCat['ID'], 'value' => $value);
        }
        
        $urlFaq = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "administration/faq";
        $urlCats = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "administration/cats";
        
        if ($sUrl == 'cats') {
            // Formulaire de modification
            $oModifyForm = new BxTemplFormView($this->getCatModifyForm($aCats));
            $oModifyForm->initChecker();
            $sResult = "";
            if($oModifyForm->isSubmittedAndValid()) {
                $iResult = 0;
                foreach($aCats as $aCat) {
                    if ($_POST[$aCat['ID'] . '_RemoveChecker']) {
                        $this->_oDb->deleteCat($aCat['ID']);
                    }
                    else {
                        $this->_oDb->updateCat($aCat['ID'], $_POST[$aCat['ID'] . '_Picto'], process_db_input($_POST[$aCat['ID'] . '_Caption']));
                    }
                }
                $sJQueryJS = genAjaxyPopupJS(0, 'ajaxy_popup_result_div', $urlCats);
				echo MsgBox(_t('_adm_txt_settings_success')) . $sJQueryJS;
            }
            $sResult .= $oModifyForm->getCode();
            unset($oModifyForm);
            echo DesignBoxAdmin (_t('_mg_faq_cat_modify'), $sResult, $aMenu); // dsiplay box
        }
        elseif ($sUrl == 'cat-new') {
            // Formulaire d'ajout
            $oInsertForm = new BxTemplFormView($this->getCatInsertForm());
            $oInsertForm->initChecker();
            $sResult = "";
            if($oInsertForm->isSubmittedAndValid()) {
                $this->_oDb->insertCat(process_db_input($_POST['NewCat_Picto']), process_db_input($_POST['NewCat_Caption']));
                $sJQueryJS = genAjaxyPopupJS($this->_oDb->lastId(), 'ajaxy_popup_result_div', $urlCats);
				echo MsgBox(_t('_adm_txt_settings_success')) . $sJQueryJS;
            }
            $sResult .= $oInsertForm->getCode();
            unset($oInsertForm);
            echo DesignBoxAdmin (_t('_mg_faq_cat_insert'), $sResult, $aMenu); // display box
        }
        else if ($sUrl == 'settings') {
            // Réglages
            $iId = $this->_oDb->getSettingsCategory(); // get our setting category id
            if(empty($iId)) { // if category is not found display page not found
                echo MsgBox(_t('_sys_request_page_not_found_cpt'));
                $this->_oTemplate->pageCodeAdmin (_t('_mg_faq'));
                return;
            }
            bx_import('BxDolAdminSettings'); // import class
            $mixedResult = '';
            if(isset($_POST['save']) && isset($_POST['cat'])) { // save settings
                $oSettings = new BxDolAdminSettings($iId);
                $mixedResult = $oSettings->saveChanges($_POST);
            }
            $oSettings = new BxDolAdminSettings($iId); // get display form code
            $sResult = $oSettings->getForm();
            if($mixedResult !== true && !empty($mixedResult)) { // attach any resulted messages at the form beginning
                $sResult = $mixedResult . $sResult;
            }
            echo $this->_oTemplate->adminBlock($sResult, _t('_Settings'), $aMenu); // dsiplay box
        }
        else {
            // Chargement des langues
            $aLanguages = $this->_oDb->getAll("SELECT `ID` AS `id`, `Title` AS `title` FROM `sys_localization_languages`");
            foreach($aLanguages as $aLanguage) {
                $aLanguageChooser[] = array('key' => $aLanguage['id'], 'value' => $aLanguage['title']);
            }
            if ($sUrl == 'faq-new') {
                // Formulaire d'ajout
                $aInsertForm = array(
                    'form_attrs' => array(
                        'id' => 'adm-faq-insert',
                        'action' => '',
                        'method' => 'post',
                        'enctype' => 'multipart/form-data',
                    ),
                    'params' => array (
                        'db' => array(
                            'table' => 'mg_faq',
                            'key' => 'ID',
                            'uri' => '',
                            'uri_title' => '',
                            'submit_name' => 'adm-faq-insert-save'
                        ),
                    ),
                    'inputs' => array ()
                );
                $aInsertForm['inputs'] = array_merge($aInsertForm['inputs'], array(
                    'NewFaq_Language' => array(
                        'type' => 'select',
                        'name' => 'NewFaq_Language',
                        'caption' => _t("_Language"),
                        'value' => 0,
                        'values' => $aLanguageChooser,
                        'db' => array (
                            'pass' => 'Int',
                        ),
                    ),
                    'NewFaq_Cat' => array(
                        'type' => 'select',
                        'name' => 'NewFaq_Cat',
                        'caption' => _t("_Category"),
                        'value' => 0,
                        'values' => $aCatChooser,
                        'db' => array (
                            'pass' => 'Int',
                        ),
                    ),
                    'NewFaq_Question' => array(
                        'type' => 'text',
                        'name' => 'NewFaq_Question',
                        'caption' => _t("_mg_faq_question"),
                        'value' => '',
                        'db' => array (
                            'pass' => 'Xss',
                        ),
                        'required' => true,
                        'checker' => array (  
                            'func' => 'length',
                            'params' => array(1,255),
                            'error' => _t('_mg_faq_questionRequired'),
                        ),
                        'attrs' => array(
                            'id' => 'NewFaq_Question',
                        ),
                    ),
                    'NewFaq_Answer' => array(
                        'type' => 'textarea',
                        'name' => 'NewFaq_Answer',
                        'caption' => _t("_mg_faq_answer"),
                        'html' => 2,
                        'db' => array (
                            'pass' => 'XssHtml',
                        ),
                        'attrs' => array(
                            'class' => 'FaqEditor',
                            'id' => 'NewFaq_Answer',
                        ),
                    ),
                ));
                $aInsertForm['inputs']['adm-faq-insert-save'] = array(
                    'type' => 'submit',
                    'name' => 'adm-faq-insert-save',
                    'value' => _t('_Submit'),
                );
                $oInsertForm = new BxTemplFormView($aInsertForm);
                $oInsertForm->initChecker();
                $sResult = "";
                if($oInsertForm->isSubmittedAndValid()) {
                    $bResult = $this->_oDb->insertFaq($_POST['NewFaq_Language'], $_POST['NewFaq_Cat'], process_db_input($_POST['NewFaq_Question']), process_db_input($_POST['NewFaq_Answer']));
                    //$sResult .= MsgBox(_t($bResult == true ? "_adm_faq_success_save" : "_adm_faq_nothing_changed"), 3);
                    $sJQueryJS = genAjaxyPopupJS($this->_oDb->lastId(), 'ajaxy_popup_result_div', $urlFaq);
                    echo MsgBox(_t('_adm_txt_settings_success')) . $sJQueryJS;
                }
                $sResult .= $oInsertForm->getCode();
                unset($oInsertForm, $aInsertForm);
                echo DesignBoxAdmin (_t('_mg_faq_insert'), $sResult, $aMenu); // display box
            }
            else {
				// Pagination
				$iTotalNum = db_value("SELECT COUNT(`ID`) FROM `mg_faq`");
				$urlPagination = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "administration/faq&page={page}";
				$iPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
				$iPerPage = db_value("SELECT COUNT(`ID`) FROM `mg_faq` GROUP BY `IDLanguage`");
				if ($iPerPage > 60) {
					$iPerPage = 60;
				}
				$iLimitFrom = ($iPage - 1) * $iPerPage;
				$oPaginate = new BxDolPaginate(
					array(
						'page_url'	        => $urlPagination,
						'count'		        => $iTotalNum,
						'per_page'	        => $iPerPage,
						'range'             => 20,
						'page'		        => $iPage,
						'per_page_changer'  => false,
						'page_reloader'		=> false,
						'view_all'          => false
					)
				);
				$sPaginate = $oPaginate->getPaginate();
				unset($oPaginate);
		
                // Formulaires de modification
                $aModifyForm = array(
                    'form_attrs' => array(
                        'id' => 'adm-faq-modify',
                        'action' => '',
                        'method' => 'post',
                        'enctype' => 'multipart/form-data',
                    ),
                    'params' => array (
                        'db' => array(
                            'table' => 'mg_faq',
                            'key' => 'ID',
                            'uri' => '',
                            'uri_title' => '',
                            'submit_name' => 'adm-faq-modify-save'
                        ),
                    ),
                    'inputs' => array ()
                );
                $aFaqs = $this->_oDb->getFaq(0, 'IDLanguage', $iLimitFrom, $iPerPage);
                $iRank = 1;
                $iLang = 0;
                foreach ($aFaqs as $aFaq) {
                    $aVars = array(
                        'bx_if:avalaible' => array(
                            'condition' => $aFaq['FeedbackYes'] + $aFaq['FeedbackNo'] > 0,
                            'content' => array(
                                'yes' => $aFaq['FeedbackYes'],
                                'no' => $aFaq['FeedbackNo'],
                                'rate' => $this->getFeedbackRate($aFaq['FeedbackYes'], $aFaq['FeedbackNo'])
                            )
                        ),
                        'bx_if:notAvalaible' => array(
                            'condition' => $aFaq['FeedbackYes'] + $aFaq['FeedbackNo'] <= 0,
                            'content' => array(
                            )
                        )
                    );
                    if ($aFaq['IDLanguage'] != $iLang) {
                        $iRank = 1;
                        $iLang = $aFaq['IDLanguage'];
                    }
                    else {
                        $iRank++;
                    }
                    $sHeaderStart = $iRank . '.' . getLangNameById($aFaq['IDLanguage']) . '.';
                    $sHeaderEnd = $this->_oTemplate->parseHtmlByName('feedback_stat', $aVars);
                    $aModifyForm['inputs'] = array_merge($aModifyForm['inputs'], array(
                        $aFaq['ID'] . '_Start' => array(
                            'type' => 'block_header',
                            'name' => $aFaq['ID'] . '_Start',
                            'caption' => $sHeaderStart . ' ' . $aFaq['Question'] . $sHeaderEnd,
                            'collapsable' => true,
                            'collapsed' => true
                        ),
                        $aFaq['ID'] . '_Language' => array(
                            'type' => 'select',
                            'name' => $aFaq['ID'] . '_Language',
                            'caption' => _t("_Language"),
                            'value' => $aFaq['IDLanguage'],
                            'values' => $aLanguageChooser,
                            'db' => array (
                                'pass' => 'Int',
                            ),
                        ),
                        $aFaq['ID'] . '_Cat' => array(
                            'type' => 'select',
                            'name' => $aFaq['ID'] . '_Cat',
                            'caption' => _t("_Category"),
                            'value' => $aFaq['IDCat'],
                            'values' => $aCatChooser,
                            'db' => array (
                                'pass' => 'Int',
                            ),
                        ),
                        $aFaq['ID'] . '_Question' => array(
                            'type' => 'text',
                            'name' => $aFaq['ID'] . '_Question',
                            'caption' => _t("_mg_faq_question"),
                            'value' => $aFaq['Question'],
                            'db' => array (
                                'pass' => 'Xss',
                            ),
                            'required' => true,
                            'checker' => array (  
                                'func' => 'length',
                                'params' => array(1,255),
                                'error' => _t('_mg_faq_questionRequired'),
                            ),
                            'attrs' => array(
                                'id' => $aFaq['ID'] . '_Question',
                            ),
                        ),
                        $aFaq['ID'] . '_Answer' => array(
                            'type' => 'textarea',
                            'name' => $aFaq['ID'] . '_Answer',
                            'caption' => _t("_mg_faq_answer"),
                            'html' => 2,
                            'value' => $aFaq['Answer'],
                            'db' => array (
                                'pass' => 'XssHtml',
                            ),
                            'attrs' => array(
                                'class' => 'FaqEditor',
                                'id' => $aFaq['ID'] . '_Answer',
                            ),
                        ),
                        $aFaq['ID'] . '_RemoveChecker' => array(
                            'type' => 'checkbox',
                            'name' => $aFaq['ID'] . '_RemoveChecker',
                            'caption' => _t("_Delete"),
                            'value' => $aFaq['ID'],
                            'attrs' => array(
                                'class' => 'RemoveChecker',
                                'id' => $aFaq['ID'] . '_RemoveChecker',
                            )
                        ),
                        $aFaq['ID'] . '_End' => array(
                            'type' => 'block_end',
                            'name' => $aFaq['ID'] . '_End',
                        )
                    ));
                }
                $aModifyForm['inputs']['adm-faq-modify-save'] = array(
                    'type' => 'submit',
                    'name' => 'adm-faq-modify-save',
                    'value' => _t('_adm_btn_settings_save'),
                );
                $oModifyForm = new BxTemplFormView($aModifyForm);
                $oModifyForm->initChecker();
                $sResult = "";
                if($oModifyForm->isSubmittedAndValid()) {
                    $iResult = 0;
                    foreach($aFaqs as $aFaq) {
                        if ($_POST[$aFaq['ID'] . '_RemoveChecker']) {
                            $this->_oDb->deleteFaq($aFaq['ID']);
                        }
                        else {
                            $this->_oDb->updateFaq($aFaq['ID'], $_POST[$aFaq['ID'] . '_Language'], $_POST[$aFaq['ID'] . '_Cat'],
                                process_db_input($_POST[$aFaq['ID'] . '_Question']), process_db_input($_POST[$aFaq['ID'] . '_Answer']));
                        }
                    }
                    //$sResult .= MsgBox(_t($iResult > 0 ? "_adm_faq_success_save" : "_adm_faq_nothing_changed"), 3);
                    $sJQueryJS = genAjaxyPopupJS(0, 'ajaxy_popup_result_div', $urlFaq);
                    echo MsgBox(_t('_adm_txt_settings_success')) . $sJQueryJS;
                }
                $sResult .= $this->_oTemplate->addJs("faq.js", true);
                $sResult .= $oModifyForm->getCode();
                unset($oModifyForm, $aModifyForm);
				$sResult .= $sPaginate;
                echo DesignBoxAdmin (_t('_mg_faq_modify'), $sResult, $aMenu); // dsiplay box
            }
        }
        
        // Affiche la page
        $this->_oTemplate->pageCodeAdmin (_t('_mg_faq')); // output is completed, admin page will be displaed here
    }
    
    function actionFeedback($id, $vote) {
        if ($vote == 'yes') {
            $aFaq = $this->_oDb->feedbackYes($id);
        }
        else {
            $aFaq = $this->_oDb->feedbackNo($id);
        }
        
        echo _t('_mg_faq_feedbackSent');
    }

    function actionHome () {
        $this->_oTemplate->pageStart();
        bx_import ('PageMain', $this->_aModule);
        $sClass = $this->_aModule['class_prefix'] . 'PageMain';
        $oPage = new $sClass ($this);
        echo $oPage->getCode();
        $this->_oTemplate->addCss (array('faq.css'));
        $this->_oTemplate->addJs("faq.js");
        $this->_oTemplate->pageCode(_t('_mg_faq_text'), false, false);
    }
    
    function getCatInsertForm() {
        $aForm = array(
            'form_attrs' => array(
                'id' => 'adm-cat-insert',
                'action' => '',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ),
            'params' => array (
                'db' => array(
                    'table' => 'mg_faq_cats',
                    'key' => 'ID',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'adm-cat-insert-save'
                ),
            ),
            'inputs' => array ()
        );
        
        $aForm['inputs'] = array_merge($aForm['inputs'], array(
            'NewCat_Picto' => array(
                'type' => 'text',
                'name' => 'NewCat_Picto',
                'caption' => _t("_mg_faq_cat_picto"),
                'value' => '',
                'db' => array (
                    'pass' => 'Xss',
                ),
            ),
            'NewCat_Caption' => array(
                'type' => 'text',
                'name' => 'NewCat_Caption',
                'caption' => _t("_mg_faq_cat_caption"),
                'value' => '',
                'db' => array (
                    'pass' => 'Xss',
                ),
                'required' => true,
                'checker' => array (  
                    'func' => 'length',
                    'params' => array(1,100),
                    'error' => _t('_mg_faq_captionRequired'),
                ),
                'attrs' => array(
                    'id' => 'NewCat_Caption',
                ),
            ),
        ));
        
        $aForm['inputs']['adm-cat-insert-save'] = array(
            'type' => 'submit',
            'name' => 'adm-cat-insert-save',
            'value' => _t('_Submit'),
        );
        
        return $aForm;
    }
    
    function getCatModifyForm($aCats) {
        $aForm = array(
            'form_attrs' => array(
                'id' => 'adm-cat-modify',
                'action' => '',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ),
            'params' => array (
                'db' => array(
                    'table' => 'mg_faq_cats',
                    'key' => 'ID',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'adm-cat-modify-save'
                ),
            ),
            'inputs' => array ()
        );
        
        foreach ($aCats as $aCat) {
            $aForm['inputs'] = array_merge($aForm['inputs'], array(
                $aCat['ID'] . '_Start' => array(
                    'type' => 'block_header',
                    'name' => $aCat['ID'] . '_Start',
                    'caption' => _t($aCat['Caption']),
                    'collapsable' => true,
                    'collapsed' => true
                ),
                $aCat['ID'] . '_Picto' => array(
                    'type' => 'text',
                    'name' => $aCat['ID'] . '_Picto',
                    'caption' => _t("_mg_faq_cat_picto"),
                    'value' => $aCat['Picto'],
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                $aCat['ID'] . '_Caption' => array(
                    'type' => 'text',
                    'name' => $aCat['ID'] . '_Caption',
                    'caption' => _t("_mg_faq_cat_caption"),
                    'value' => $aCat['Caption'],
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                    'required' => true,
                    'checker' => array (  
                        'func' => 'length',
                        'params' => array(1,100),
                        'error' => _t('_mg_faq_captionRequired'),
                    ),
                    'attrs' => array(
                        'id' => $aCat['ID'] . '_Caption',
                    ),
                ),
                $aCat['ID'] . '_RemoveChecker' => array(
                    'type' => 'checkbox',
                    'name' => $aCat['ID'] . '_RemoveChecker',
                    'caption' => _t("_Delete"),
                    'value' => $aCat['ID'],
                    'attrs' => array(
                        'class' => 'RemoveChecker',
                        'id' => $aCat['ID'] . '_RemoveChecker',
                    )
                ),
                $aCat['ID'] . '_End' => array(
                    'type' => 'block_end',
                    'name' => $aCat['ID'] . '_End',
                )
            ));
        }
        
        $aForm['inputs']['adm-picto-modify-save'] = array(
            'type' => 'submit',
            'name' => 'adm-cat-modify-save',
            'value' => _t('_adm_btn_settings_save'),
        );
        
        return $aForm;
    }
    
    function getFeedbackRate($iYes, $iNo) {
        $iTotal = $iYes + $iNo;

        if (!$iTotal) {
            return 0;
        }
        
        return ceil($iYes / $iTotal * 100);
    }
    
    function getSearchForm() {
        // Chargement des catégories
        $aCats = $this->_oDb->getCats();
        $aCatChooser = array(array('key' => 0, 'value' => _t("_categ_all")));
        foreach($aCats as $aCat) {
            $value = !empty($aCat['Caption']) ? _t($aCat['Caption']) : $aCat['Picto'];
            $aCatChooser[] = array('key' => $aCat['ID'], 'value' => $value);
        }
        
        $aForm = array(
            'form_attrs' => array(
                'name'     => 'form_search',
                'action'   => 'm/' . $this->_oConfig->_sUri, // La method GET ne supporte pas la redirection vers une url de type ?r=faq
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
        
        return $aForm;
    }
    
    function getSuggestionForm() {
        $aForm = array(
            'form_attrs' => array(
                'id' => 'form-suggestion',
                'action' => '',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ),
            'params' => array (
                'db' => array(
                    'table' => 'mg_faq',
                    'key' => 'ID',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'form-suggestion-save'
                ),
            ),
            'inputs' => array ()
        );
        
        $aForm['inputs'] = array_merge($aForm['inputs'], array(
            'Question' => array(
                'type' => 'textarea',
                'name' => 'Question',
                'caption' => _t("_mg_faq_suggestion_msg"),
            ),
            'Captcha' => array(
                'type' => 'captcha',
                'name' => 'Captcha',
                'caption' => _t('_Enter what you see'),
                'required' => true,
                'checker' => array(
                    'func' => 'captcha',
                    'error' => _t( '_Incorrect Captcha' ),
                ),
            ),
        ));
        
        $aForm['inputs']['form-suggestion-save'] = array(
            'type' => 'submit',
            'name' => 'form-suggestion-save',
            'value' => _t('_Submit'),
        );
        
        return $aForm;
    }
}

?>
