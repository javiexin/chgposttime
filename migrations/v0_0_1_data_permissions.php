<?php
/**
 *
 * Change Post Time
 *
 * @copyright (c) 2015 javiexin ( www.exincastillos.es )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Javier Lopez (javiexin)
 */

namespace javiexin\chgposttime\migrations;

class v0_0_1_data_permissions extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('permission.add', array('m_chgposttime', true)),
			array('permission.add', array('m_chgposttime', false, 'm_chgposter')),
		);
	}
}
