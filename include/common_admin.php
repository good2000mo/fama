<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Make sure we have a usable language pack for admin.
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/admin_common.php'))
	$admin_language = $pun_user['language'];
else if (file_exists(PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/admin_common.php'))
	$admin_language = $pun_config['o_default_lang'];
else
	$admin_language = 'English';

// Attempt to load the admin_common language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_common.php';

//
// Display the admin navigation menu
//
function generate_admin_menu($page = '')
{
	global $pun_config, $pun_user, $lang_admin_common;

	$is_admin = $pun_user['g_id'] == PUN_ADMIN ? true : false;

?>
<div id="adminconsole" class="block2col">
	<div id="adminmenu" class="blockmenu">
		<h2><span><?php echo $lang_admin_common['Moderator menu'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'index') echo ' class="isactive"'; ?>><a href="admin_index.php"><?php echo $lang_admin_common['Index'] ?></a></li>
					<li<?php if ($page == 'users') echo ' class="isactive"'; ?>><a href="admin_users.php"><?php echo $lang_admin_common['Users'] ?></a></li>
				</ul>
			</div>
		</div>
<?php

	if ($is_admin)
	{

?>
		<h2 class="block2"><span><?php echo $lang_admin_common['Admin menu'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'options') echo ' class="isactive"'; ?>><a href="admin_options.php"><?php echo $lang_admin_common['Options'] ?></a></li>
					<li<?php if ($page == 'forums') echo ' class="isactive"'; ?>><a href="admin_forums.php"><?php echo $lang_admin_common['Forums'] ?></a></li>
					<li<?php if ($page == 'groups') echo ' class="isactive"'; ?>><a href="admin_groups.php"><?php echo $lang_admin_common['User groups'] ?></a></li>
				</ul>
			</div>
		</div>
<?php

	}

	// See if there are any plugins
	$plugins = forum_list_plugins($is_admin);

	// Did we find any plugins?
	if (!empty($plugins))
	{

?>
		<h2 class="block2"><span><?php echo $lang_admin_common['Plugins menu'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
<?php

		foreach ($plugins as $plugin_name => $plugin)
			echo "\t\t\t\t\t".'<li'.(($page == $plugin_name) ? ' class="isactive"' : '').'><a href="admin_loader.php?plugin='.$plugin_name.'">'.str_replace('_', ' ', $plugin).'</a></li>'."\n";

?>
				</ul>
			</div>
		</div>
<?php

	}

?>
	</div>

<?php

}


//
// Delete topics from $forum_id that are "older than" $prune_date (if $prune_sticky is 1, sticky topics will also be deleted)
//
function prune($forum_id, $prune_sticky, $prune_date)
{
	global $db;

	$extra_sql = ($prune_date != -1) ? ' AND last_post<'.$prune_date : '';

	if (!$prune_sticky)
		$extra_sql .= ' AND sticky=\'0\'';

	// Fetch topics to prune
	$result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id.$extra_sql.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --', true) or error('Unable to fetch topics', __FILE__, __LINE__, $db->error());

	$topic_ids = '';
	while ($row = $db->fetch_row($result))
		$topic_ids .= (($topic_ids != '') ? ',' : '').$row[0];

	if ($topic_ids != '')
	{
		// Fetch posts to prune
		$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id IN('.$topic_ids.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --', true) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());

		$post_ids = '';
		while ($row = $db->fetch_row($result))
			$post_ids .= (($post_ids != '') ? ',' : '').$row[0];

		if ($post_ids != '')
		{
			// Delete topics
			$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.$topic_ids.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or error('Unable to prune topics', __FILE__, __LINE__, $db->error());
			// Delete posts
			$db->query('DELETE FROM '.$db->prefix.'posts WHERE id IN('.$post_ids.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or error('Unable to prune posts', __FILE__, __LINE__, $db->error());

			// We removed a bunch of posts, so now we have to update the search index
			require_once PUN_ROOT.'include/search_idx.php';
			strip_search_index($post_ids);
		}
	}
}
