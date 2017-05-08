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

$aConfig = array(
	/**
	 * Main Section.
	 */	
	'title' => 'Faq', // module title, this name will be displayed in the modules list
    'version' => '1.0.1', // module version, change this number everytime you publish your mod
	'vendor' => 'MensGo', // vendor name, also it is a folder name in modules folder
	'update_url' => 'http://www.boonex.com/market/update_ckeck?product=mg_faq', // url to get info about available module updates
	
	'compatible_with' => array( // module compatibility
        '7.1.x'  // it tells that the module can be installed on Dolphin 7.0.0 only.
    ),

    /**
	 * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
	 */
	'home_dir' => 'mensgo/faq/', // folder where module files are located, it describes path from /modules/ folder.
	'home_uri' => 'faq', // module URI, so the module will be accessable via the following urls: m/faq/ or modules/?r=faq/
	
	'db_prefix' => 'mg_faq_', // database prefix for all module tables, it is better to compose it from vendor prefix + module prefix
    'class_prefix' => 'MgFaq', // class prefix for all module classes, it is better to compose it from vendor prefix + module prefix

	/**
	 * Installation instructions, for complete list refer to BxDolInstaller Dolphin class
	 */
	'install' => array(
        'update_languages' => 1, // add languages
        'execute_sql' => 1,
        'clear_db_cache' => 1,
	),
	/**
	 * Uninstallation instructions, for complete list refer to BxDolInstaller Dolphin class
	 */    
	'uninstall' => array (
        'update_languages' => 1, // remove added languages
        'execute_sql' => 1,
        'clear_db_cache' => 1,
    ),

	/**
	 * Category for language keys, all language keys will be places to this category, but it is still good practive to name each language key with module prefix, to avoid conflicts with other mods.
	 */
	'language_category' => 'Faq',

	/**
	 * Permissions Section, list all permissions here which need to be changed before install and after uninstall, see examples in other BoonEx modules
	 */
	'install_permissions' => array(),
    'uninstall_permissions' => array(),

	/**
	 * Introduction and Conclusion Section, reclare files with info here, see examples in other BoonEx modules
	 */
	'install_info' => array(
		'introduction' => '',
		'conclusion' => ''
	),
	'uninstall_info' => array(
		'introduction' => '',
		'conclusion' => ''
	)
);

?>
