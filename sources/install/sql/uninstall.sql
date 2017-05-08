-- structure
DROP TABLE `mg_faq`;
DROP TABLE `mg_faq_cats`;

-- settings
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'Faq' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'mg_faq';

-- Contenu de la table `sys_page_compose`
DELETE FROM `sys_page_compose` WHERE `Page`='mg_faq_main';

-- Contenu de la table `sys_page_compose_pages`
DELETE FROM `sys_page_compose_pages` WHERE `Name`='mg_faq_main';

-- Contenu de la table `sys_email_templates`
DELETE FROM `sys_email_templates` WHERE `Name`='t_faq_suggestion';