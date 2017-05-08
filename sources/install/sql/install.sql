-- structure mg_faq
CREATE TABLE `mg_faq` (
    `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `Question` TEXT NOT NULL ,
    `Answer` TEXT NOT NULL ,
    `IDLanguage` TINYINT UNSIGNED NOT NULL default '0' ,
    `IDCat` SMALLINT UNSIGNED NOT NULL default '0' ,
    `FeedbackYes` INT UNSIGNED NOT NULL default '0' ,
    `FeedbackNo` INT UNSIGNED NOT NULL default '0' ,
    FULLTEXT KEY `Question` (`Question`,`Answer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- structure mg_picto
CREATE TABLE `mg_faq_cats` (
    `ID` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `Picto` VARCHAR(255) NOT NULL,
    `Caption` VARCHAR(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- settings
SET @iMaxOrder = (SELECT `menu_order` + 1 FROM `sys_options_cats` ORDER BY `menu_order` DESC LIMIT 1);
INSERT INTO `sys_options_cats` (`name`, `menu_order`) VALUES ('Faq', @iMaxOrder);
SET @iCategId = (SELECT LAST_INSERT_ID());
INSERT INTO `sys_options` (`Name`, `VALUE`, `kateg`, `desc`, `Type`, `check`, `err_text`, `order_in_kateg`, `AvailableValues`) VALUES
('mg_faq_suggEmail', '', @iCategId, 'Suggestion e-mail', 'digit', '', '', '1', ''),
('mg_faq_feedback', 'any', @iCategId, 'Feedback appear', 'select', '', '', '2', 'any,all_result,search_result,none');

-- admin menu
SET @iMax = (SELECT MAX(`order`) FROM `sys_menu_admin` WHERE `parent_id` = '2');
INSERT IGNORE INTO `sys_menu_admin` (`parent_id`, `name`, `title`, `url`, `description`, `icon`, `order`) VALUES
(2, 'mg_faq', '_mg_faq', '{siteUrl}modules/?r=faq/administration/', 'Frequently Asked Questions', 'modules/mensgo/faq/|admin-menu.png', @iMax+1);

-- Contenu de la table `sys_page_compose_pages`
SELECT @iPCPOrder:=MAX(`Order`) FROM `sys_page_compose_pages`;
INSERT INTO `sys_page_compose_pages` (`Name`, `Title`, `Order`) VALUES
('mg_faq_main', 'FAQ', @iPCPOrder+1);

-- Contenu de la table `sys_page_compose`
INSERT INTO `sys_page_compose` (`Page`, `PageWidth`, `Desc`, `Caption`, `Column`, `Order`, `Func`, `Content`, `DesignBox`, `ColWidth`, `Visible`, `MinWidth`, `Cache`) VALUES
('mg_faq_main', '1140px', 'FAQ search form', '_mg_faq_title3', 1, 0, 'Search', '', 1, 100, 'non,memb', 0, 0),
('mg_faq_main', '1140px', 'FAQ', '_mg_faq_title', 1, 1, 'Faq', '', 1, 100, 'non,memb', 0, 0),
('mg_faq_main', '1140px', 'FAQ suggestion', '_mg_faq_suggestion', 1, 2, 'Suggestion', '', 1, 100, 'non,memb', 0, 0);

-- Contenu de la table `sys_email_templates`
INSERT INTO `sys_email_templates` (`Name`, `Subject`, `Body`, `Desc`, `LangID`) VALUES
('t_faq_suggestion', 'FAQ - Suggestion from %s', '<h2>Profil informations</h2><table><tr><td><b>ID</b></td><td><SenderID></td></tr>\r\n<tr><td><b>NickName</b></td><td><SenderName></td></tr><tr><td><b>Email</b></td><td><SenderEmail></td></tr></table><h2>Message</h2><p><Message></p>', 'FAQ suggestion', 0);
