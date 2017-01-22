<?php
/**
 *
 * Change Post Time
 *
 * @copyright (c) 2015 javiexin ( www.exincastillos.es )
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 * @author Javier Lopez (javiexin)
 */

namespace javiexin\chgposttime\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var ContainerInterface */
	protected $container;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor of event listener
	 *
	 * @param \phpbb\db\driver\driver_interface		$db				Database
	 * @param \phpbb\user							$user			User object
	 * @param \phpbb\auth\auth						$auth			Auth object
	 * @param \phpbb\request\request				$request		Request object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request $request, ContainerInterface $container, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->container = $container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		// Add language vars
		$this->user->add_lang_ext('javiexin/chgposttime', 'info_mcp_chgposttime');
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array<string,string>
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'core.permissions'							=> 'mcp_post_chg_post_time_permission',		// permissions
			'core.mcp_post_additional_options'			=> 'mcp_post_chg_post_time_action',			// perform action
			'core.mcp_post_template_data'				=> 'mcp_post_chg_post_time_template',		// template variables
		);
	}

	/**
	 * Adds the permission to the right permission category
	 *
	 * @param object $event The event object
	 * @return void
	 */
	public function mcp_post_chg_post_time_permission($event)
	{
		$permissions = array_merge($event['permissions'], array(
				'm_chgposttime'		=> array('lang' => 'ACL_M_CHGPOSTTIME', 'cat' => 'post_actions'),
			));
		$event['permissions'] = $permissions;
	}

	/**
	 * Validates input and effectively changes posting date and time of the corresponding post
	 *
	 * @param object $event The event object
	 * @return void
	 */
	public function mcp_post_chg_post_time_action($event)
	{
		// We only deal with Change Post Time action
		if ($event['action'] !== 'chgposttime')
		{
			return;
		}
		$post_info = $event['post_info'];
		$post_id = $post_info['post_id'];

		// Check permissions
		if (!$this->auth->acl_get('m_chgposttime', $post_info['forum_id']))
		{
			trigger_error('NOT_AUTHORISED');
		}

		// Get the original time, and format it according to the user timezone
		$oldtime = $this->user->create_datetime()->setTimestamp($post_info['post_time']);
		$from_oldtime = $oldtime->format();

		$year = $this->request->variable('jx_year', 0);
		$month = $this->request->variable('jx_month', 0);
		$day = $this->request->variable('jx_day', 0);
		$hour = $this->request->variable('jx_hour', -1);
		$minute = $this->request->variable('jx_minute', -1);

		$year = ($year) ? $year : (int) $oldtime->format('Y');
		$month = ($month) ? $month : (int) $oldtime->format('n');
		$day = ($day) ? $day : (int) $oldtime->format('j');
		$hour = ($hour>=0) ? $hour : (int) $oldtime->format('G');
		$minute = ($minute>=0) ? $minute : (int) $oldtime->format('i');

		// Use user timezone to create UNIX timestamp
		$newtime = $this->user->create_datetime()->setDate($year, $month, $day)->setTime($hour, $minute);
		$update_time = $newtime->getTimestamp();

		// Update post_time in database
		$sql = 'UPDATE ' . POSTS_TABLE . ' SET post_time = ' . (int) $update_time . 
			' WHERE post_id = ' . (int) $post_id;
		$this->db->sql_query($sql);

		// Synchronize topic and forum
		if (!function_exists('sync'))
		{
			include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		}
		sync('topic', 'topic_id', $post_info['topic_id'], true);
		sync('forum', 'forum_id', $post_info['forum_id'], true);

		// Renew post info
		if (!function_exists('phpbb_get_post_data'))
		{
			include($this->root_path . 'includes/functions_mcp.' . $this->php_ext);
		}
		$post_info = phpbb_get_post_data(array($post_id), false, true);

		if (!sizeof($post_info))
		{
			trigger_error('POST_NOT_EXIST');
		}

		$post_info = $post_info[$post_id];

		$to_newtime = $newtime->format();

		// Now add log entry
		$phpbb_log = $this->container->get('log');
		$phpbb_log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MCP_JX_CHANGE_POSTTIME', false, array(
				'forum_id'		=> (int) $post_info['forum_id'],
				'topic_id'		=> (int) $post_info['topic_id'],
				$post_info['topic_title'],
				$from_oldtime,
				$to_newtime,
				(int) $post_id,
			));

		$event['post_info'] = $post_info;
	}

	/**
	 * Adds the post date/time change options to the template
	 *
	 * @param object $event The event object
	 * @return void
	 */
	public function mcp_post_chg_post_time_template($event)
	{
		$post_info = $event['post_info'];
		$mcp_post_template_data = $event['mcp_post_template_data'];

		$oldtime = $this->user->create_datetime()->setTimestamp($post_info['post_time']);

		$mcp_post_template_data = array_merge($mcp_post_template_data, array(
				'S_JX_CAN_CHGPOSTTIME'		=> $this->auth->acl_get('m_chgposttime', $post_info['forum_id']),
				'JX_POST_DATE_YEAR'			=> (int) $oldtime->format('Y'),
				'JX_POST_DATE_MONTH'		=> (int) $oldtime->format('n'),
				'JX_POST_DATE_DAY'			=> (int) $oldtime->format('j'),
				'JX_POST_TIME_HOUR'			=> (int) $oldtime->format('G'),
				'JX_POST_TIME_MINUTE'		=> (int) $oldtime->format('i'),
			));

		$event['mcp_post_template_data'] = $mcp_post_template_data;
		$event['s_additional_opts'] = true;
	}
}
