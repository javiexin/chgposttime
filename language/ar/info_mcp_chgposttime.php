<?php
/**
 *
 * Change Post Time [Arabic]
 *
 * @copyright (c) 2015 javiexin ( www.exincastillos.es )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Javier Lopez (javiexin)
 *
 * Translated By : Bassel Taha Alhitary - www.alhitary.net
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'JX_CHANGE_POST_TIME'				=> 'تعديل توقيت المشاركة',
	'JX_CHANGE_POST_TIME_DATE'			=> 'YYYY-MM-DD',
	'JX_CHANGE_POST_TIME_TIME'			=> 'HH:MM',

	'LOG_MCP_JX_CHANGE_POSTTIME'		=> '<strong>تعديل توقيت المشاركة #%4$s في الموضوع “%1$s”</strong><br />» من %2$s إلى %3$s',
));
