<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';

// Include UTF-8 function
require PUN_ROOT.'include/utf8/substr_replace.php';
require PUN_ROOT.'include/utf8/ucwords.php'; // utf8_ucwords needs utf8_substr_replace
require PUN_ROOT.'include/utf8/strcasecmp.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;
$section = isset($_GET['section']) ? $_GET['section'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 2)
	fama_message($lang_common['Bad request']);

if ($action != 'change_pass' || !isset($_GET['key']))
{
	if ($pun_user['g_read_board'] == '0')
		fama_message($lang_common['No view']);
	else if ($pun_user['g_view_users'] == '0' && ($pun_user['is_guest'] || $pun_user['id'] != $id))
		fama_message($lang_common['No permission']);
}

// Load the profile.php/register.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';

// Load the profile.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/profile.php';


if ($action == 'change_pass')
{
	if (isset($_GET['key']))
	{
		// If the user is already logged in we shouldn't be here :)
		if (!$pun_user['is_guest'])
		{
			header('Location: index.php');
			exit;
		}

		$key = $_GET['key'];

		$result = $db->query('SELECT * FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch new password', __FILE__, __LINE__, $db->error());
		$cur_user = $db->fetch_assoc($result);

		if ($key == '' || $key != $cur_user['activate_key'])
			fama_message($lang_profile['Pass key bad'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.');
		else
		{
			$db->query('UPDATE '.$db->prefix.'users SET password=\''.$cur_user['activate_string'].'\', activate_string=NULL, activate_key=NULL'.(!empty($cur_user['salt']) ? ', salt=NULL' : '').' WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update password', __FILE__, __LINE__, $db->error());

			fama_message($lang_profile['Pass updated'], true);
		}
	}

	// Make sure we are allowed to change this users password
	if ($pun_user['id'] != $id)
	{
		if (!$pun_user['is_admmod']) // A regular user trying to change another users password?
			fama_message($lang_common['No permission']);
		else if ($pun_user['g_moderator'] == '1') // A moderator trying to change a users password?
		{
			$result = $db->query('SELECT u.group_id, g.g_moderator FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON (g.g_id=u.group_id) WHERE u.id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if (!$db->num_rows($result))
				fama_message($lang_common['Bad request']);

			list($group_id, $is_moderator) = $db->fetch_row($result);

			if ($pun_user['g_mod_edit_users'] == '0' || $pun_user['g_mod_change_passwords'] == '0' || $group_id == PUN_ADMIN || $is_moderator == '1')
				fama_message($lang_common['No permission']);
		}
	}

	if (isset($_POST['form_sent']))
	{
		if ($pun_user['is_admmod'])
			confirm_referrer('profile.php');

		$old_password = isset($_POST['req_old_password']) ? pun_trim($_POST['req_old_password']) : '';
		$new_password1 = pun_trim($_POST['req_new_password1']);
		$new_password2 = pun_trim($_POST['req_new_password2']);

		if ($new_password1 != $new_password2)
			fama_message($lang_prof_reg['Pass not match']);
		if (pun_strlen($new_password1) < 4)
			fama_message($lang_prof_reg['Pass too short']);

		$result = $db->query('SELECT * FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch password', __FILE__, __LINE__, $db->error());
		$cur_user = $db->fetch_assoc($result);

		$authorized = false;

		if (!empty($cur_user['password']))
		{
			$old_password_hash = pun_hash($old_password);

			if ($cur_user['password'] == $old_password_hash || $pun_user['is_admmod'])
				$authorized = true;
		}

		if (!$authorized)
			fama_message($lang_profile['Wrong pass']);

		$new_password_hash = pun_hash($new_password1);

		$db->query('UPDATE '.$db->prefix.'users SET password=\''.$new_password_hash.'\''.(!empty($cur_user['salt']) ? ', salt=NULL' : '').' WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update password', __FILE__, __LINE__, $db->error());

		if ($pun_user['id'] == $id)
			pun_setcookie($pun_user['id'], $new_password_hash, time() + $pun_config['o_timeout_visit']);

		redirect('profile.php?section=essentials&amp;id='.$id, $lang_profile['Pass updated redirect']);
	}

	$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_profile['Change pass']);
	$required_fields = array('req_old_password' => $lang_profile['Old pass'], 'req_new_password1' => $lang_profile['New pass'], 'req_new_password2' => $lang_profile['Confirm new pass']);
	$focus_element = array('change_pass', ((!$pun_user['is_admmod']) ? 'req_old_password' : 'req_new_password1'));
	define('PUN_ACTIVE_PAGE', 'profile');
	require PUN_ROOT.'header.php';

?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Change pass'] ?></span></h2>
	<div class="box">
		<form id="change_pass" method="post" action="profile.php?action=change_pass&amp;id=<?php echo $id ?>" onsubmit="return process_form(this)">
			<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
				<fieldset>
					<legend><?php echo $lang_profile['Change pass legend'] ?></legend>
					<div class="infldset">
<?php if (!$pun_user['is_admmod']): ?>						<label class="required"><strong><?php echo $lang_profile['Old pass'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<input type="password" name="req_old_password" size="16" /><br /></label>
<?php endif; ?>						<label class="conl required"><strong><?php echo $lang_profile['New pass'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<input type="password" name="req_new_password1" size="16" /><br /></label>
						<label class="conl required"><strong><?php echo $lang_profile['Confirm new pass'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<input type="password" name="req_new_password2" size="16" /><br /></label>
						<p class="clearb"><?php echo $lang_profile['Pass info'] ?></p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="update" value="<?php echo $lang_common['Submit'] ?>" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


else if ($action == 'change_email')
{
	// Make sure we are allowed to change this users email
	if ($pun_user['id'] != $id)
	{
		if (!$pun_user['is_admmod']) // A regular user trying to change another users email?
			fama_message($lang_common['No permission']);
		else if ($pun_user['g_moderator'] == '1') // A moderator trying to change a users email?
		{
			$result = $db->query('SELECT u.group_id, g.g_moderator FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON (g.g_id=u.group_id) WHERE u.id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			if (!$db->num_rows($result))
				fama_message($lang_common['Bad request']);

			list($group_id, $is_moderator) = $db->fetch_row($result);

			if ($pun_user['g_mod_edit_users'] == '0' || $group_id == PUN_ADMIN || $is_moderator == '1')
				fama_message($lang_common['No permission']);
		}
	}

	if (isset($_GET['key']))
	{
		$key = $_GET['key'];

		$result = $db->query('SELECT activate_string, activate_key FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch activation data', __FILE__, __LINE__, $db->error());
		list($new_email, $new_email_key) = $db->fetch_row($result);

		if ($key == '' || $key != $new_email_key)
			fama_message($lang_profile['Email key bad'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.');
		else
		{
			$db->query('UPDATE '.$db->prefix.'users SET email=activate_string, activate_string=NULL, activate_key=NULL WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update email address', __FILE__, __LINE__, $db->error());

			fama_message($lang_profile['Email updated'], true);
		}
	}
	else if (isset($_POST['form_sent']))
	{
		if (pun_hash($_POST['req_password']) !== $pun_user['password'])
			fama_message($lang_profile['Wrong pass']);

		require PUN_ROOT.'include/email.php';

		// Validate the email address
		$new_email = strtolower(trim($_POST['req_new_email']));
		if (!is_valid_email($new_email))
			fama_message($lang_common['Invalid email']);

		$new_email_key = random_pass(8);

		$db->query('UPDATE '.$db->prefix.'users SET activate_string=\''.$db->escape($new_email).'\', activate_key=\''.$new_email_key.'\' WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update activation data', __FILE__, __LINE__, $db->error());

		// Load the "activate email" template
		$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/activate_email.tpl'));

		// The first row contains the subject
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = trim(substr($mail_tpl, $first_crlf));

		$mail_message = str_replace('<username>', $pun_user['username'], $mail_message);
		$mail_message = str_replace('<base_url>', get_base_url(), $mail_message);
		$mail_message = str_replace('<activation_url>', get_base_url().'/profile.php?action=change_email&id='.$id.'&key='.$new_email_key, $mail_message);
		$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);

		pun_mail($new_email, $mail_subject, $mail_message);

		fama_message($lang_profile['Activate email sent'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.', true);
	}

	$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_profile['Change email']);
	$required_fields = array('req_new_email' => $lang_profile['New email'], 'req_password' => $lang_common['Password']);
	$focus_element = array('change_email', 'req_new_email');
	define('PUN_ACTIVE_PAGE', 'profile');
	require PUN_ROOT.'header.php';

?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Change email'] ?></span></h2>
	<div class="box">
		<form id="change_email" method="post" action="profile.php?action=change_email&amp;id=<?php echo $id ?>" id="change_email" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_profile['Email legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<label class="required"><strong><?php echo $lang_profile['New email'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="text" name="req_new_email" size="50" maxlength="80" /><br /></label>
						<label class="required"><strong><?php echo $lang_common['Password'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="password" name="req_password" size="16" /><br /></label>
						<p><?php echo $lang_profile['Email instructions'] ?></p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="new_email" value="<?php echo $lang_common['Submit'] ?>" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


else if (isset($_POST['update_group_membership']))
{
	if ($pun_user['g_id'] > PUN_ADMIN)
		fama_message($lang_common['No permission']);

	confirm_referrer('profile.php');

	$new_group_id = intval($_POST['group_id']);

	$db->query('UPDATE '.$db->prefix.'users SET group_id='.$new_group_id.' WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to change user group', __FILE__, __LINE__, $db->error());

	$result = $db->query('SELECT g_moderator FROM '.$db->prefix.'groups WHERE g_id='.$new_group_id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch group', __FILE__, __LINE__, $db->error());
	$new_group_mod = $db->result($result);

	// If the user was a moderator or an administrator, we remove him/her from the moderator list in all forums as well
	if ($new_group_id != PUN_ADMIN && $new_group_mod != '1')
	{
		$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

		while ($cur_forum = $db->fetch_assoc($result))
		{
			$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

			if (in_array($id, $cur_moderators))
			{
				$username = array_search($id, $cur_moderators);
				unset($cur_moderators[$username]);
				$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';

				$db->query('UPDATE '.$db->prefix.'forums SET moderators='.$cur_moderators.' WHERE id='.$cur_forum['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update forum', __FILE__, __LINE__, $db->error());
			}
		}
	}

	redirect('profile.php?section=admin&amp;id='.$id, $lang_profile['Group membership redirect']);
}


else if (isset($_POST['update_forums']))
{
	if ($pun_user['g_id'] > PUN_ADMIN)
		fama_message($lang_common['No permission']);

	confirm_referrer('profile.php');

	// Get the username of the user we are processing
	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$username = $db->result($result);

	$moderator_in = (isset($_POST['moderator_in'])) ? array_keys($_POST['moderator_in']) : array();

	// Loop through all forums
	$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

	while ($cur_forum = $db->fetch_assoc($result))
	{
		$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
		// If the user should have moderator access (and he/she doesn't already have it)
		if (in_array($cur_forum['id'], $moderator_in) && !in_array($id, $cur_moderators))
		{
			$cur_moderators[$username] = $id;
			uksort($cur_moderators, 'utf8_strcasecmp');

			$db->query('UPDATE '.$db->prefix.'forums SET moderators=\''.$db->escape(serialize($cur_moderators)).'\' WHERE id='.$cur_forum['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update forum', __FILE__, __LINE__, $db->error());
		}
		// If the user shouldn't have moderator access (and he/she already has it)
		else if (!in_array($cur_forum['id'], $moderator_in) && in_array($id, $cur_moderators))
		{
			unset($cur_moderators[$username]);
			$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';

			$db->query('UPDATE '.$db->prefix.'forums SET moderators='.$cur_moderators.' WHERE id='.$cur_forum['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update forum', __FILE__, __LINE__, $db->error());
		}
	}

	redirect('profile.php?section=admin&amp;id='.$id, $lang_profile['Update forums redirect']);
}


else if (isset($_POST['delete_user']) || isset($_POST['delete_user_comply']))
{
	if ($pun_user['g_id'] > PUN_ADMIN)
		fama_message($lang_common['No permission']);

	confirm_referrer('profile.php');

	// Get the username and group of the user we are deleting
	$result = $db->query('SELECT group_id, username FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	list($group_id, $username) = $db->fetch_row($result);

	if ($group_id == PUN_ADMIN)
		fama_message($lang_profile['No delete admin message']);

	if (isset($_POST['delete_user_comply']))
	{
		// If the user is a moderator or an administrator, we remove him/her from the moderator list in all forums as well
		$result = $db->query('SELECT g_moderator FROM '.$db->prefix.'groups WHERE g_id='.$group_id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch group', __FILE__, __LINE__, $db->error());
		$group_mod = $db->result($result);

		if ($group_id == PUN_ADMIN || $group_mod == '1')
		{
			$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

			while ($cur_forum = $db->fetch_assoc($result))
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

				if (in_array($id, $cur_moderators))
				{
					unset($cur_moderators[$username]);
					$cur_moderators = (!empty($cur_moderators)) ? '\''.$db->escape(serialize($cur_moderators)).'\'' : 'NULL';

					$db->query('UPDATE '.$db->prefix.'forums SET moderators='.$cur_moderators.' WHERE id='.$cur_forum['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update forum', __FILE__, __LINE__, $db->error());
				}
			}
		}

		// Remove him/her from the online list (if they happen to be logged in)
		$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to remove user from online list', __FILE__, __LINE__, $db->error());

		// Should we delete all posts made by this user?
		if (isset($_POST['delete_posts']))
		{
			require PUN_ROOT.'include/search_idx.php';
			@set_time_limit(0);

			// Find all posts made by this user
			$result = $db->query('SELECT p.id, p.topic_id, t.forum_id FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id WHERE p.poster_id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
			if ($db->num_rows($result))
			{
				while ($cur_post = $db->fetch_assoc($result))
				{
					// Determine whether this post is the "topic post" or not
					$result2 = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$cur_post['topic_id'].' ORDER BY posted LIMIT 1'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

					if ($db->result($result2) == $cur_post['id'])
						delete_topic($cur_post['topic_id']);
					else
						delete_post($cur_post['id'], $cur_post['topic_id']);

					update_forum($cur_post['forum_id']);
				}
			}
		}
		else
			// Set all his/her posts to guest
			$db->query('UPDATE '.$db->prefix.'posts SET poster_id=1 WHERE poster_id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update posts', __FILE__, __LINE__, $db->error());

		// Delete the user
		$db->query('DELETE FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to delete user', __FILE__, __LINE__, $db->error());

		redirect('index.php', $lang_profile['User delete redirect']);
	}

	$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_profile['Confirm delete user']);
	define('PUN_ACTIVE_PAGE', 'profile');
	require PUN_ROOT.'header.php';

?>
<div class="blockform">
	<h2><span><?php echo $lang_profile['Confirm delete user'] ?></span></h2>
	<div class="box">
		<form id="confirm_del_user" method="post" action="profile.php?id=<?php echo $id ?>">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_profile['Confirm delete legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $lang_profile['Confirmation info'].' <strong>'.fama_htmlspecialchars($username).'</strong>.' ?></p>
						<div class="rbox">
							<label><input type="checkbox" name="delete_posts" value="1" checked="checked" /><?php echo $lang_profile['Delete posts'] ?><br /></label>
						</div>
						<p class="warntext"><strong><?php echo $lang_profile['Delete warning'] ?></strong></p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="delete_user_comply" value="<?php echo $lang_profile['Delete'] ?>" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


else if (isset($_POST['form_sent']))
{
	// Fetch the user group of the user we are editing
	$result = $db->query('SELECT u.username, u.group_id, g.g_moderator FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON (g.g_id=u.group_id) WHERE u.id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		fama_message($lang_common['Bad request']);

	list($old_username, $group_id, $is_moderator) = $db->fetch_row($result);

	if ($pun_user['id'] != $id &&																	// If we arent the user (i.e. editing your own profile)
		(!$pun_user['is_admmod'] ||																	// and we are not an admin or mod
		($pun_user['g_id'] != PUN_ADMIN &&															// or we aren't an admin and ...
		($pun_user['g_mod_edit_users'] == '0' ||													// mods aren't allowed to edit users
		$group_id == PUN_ADMIN ||																	// or the user is an admin
		$is_moderator))))																			// or the user is another mod
		fama_message($lang_common['No permission']);

	if ($pun_user['is_admmod'])
		confirm_referrer('profile.php');

	$username_updated = false;

	// Validate input depending on section
	switch ($section)
	{
		case 'essentials':
		{
			// Make sure we got a valid language string
			if (isset($_POST['form']['language']))
			{
				$languages = forum_list_langs();
				$form['language'] = pun_trim($_POST['form']['language']);
				if (!in_array($form['language'], $languages))
					fama_message($lang_common['Bad request']);
			}

			if ($pun_user['is_admmod'])
			{
				// Are we allowed to change usernames?
				if ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_moderator'] == '1' && $pun_user['g_mod_rename_users'] == '1'))
				{
					$form['username'] = pun_trim($_POST['req_username']);

					if ($form['username'] != $old_username)
					{
						// Check username
						require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';

						$errors = array();
						check_username($form['username'], $id);
						if (!empty($errors))
							fama_message($errors[0]);

						$username_updated = true;
					}
				}

				// We only allow administrators to update the post count
				if ($pun_user['g_id'] == PUN_ADMIN)
					$form['num_posts'] = intval($_POST['num_posts']);
			}

			require PUN_ROOT.'include/email.php';

			// Validate the email address
			$form['email'] = strtolower(trim($_POST['req_email']));
			if (!is_valid_email($form['email']))
				fama_message($lang_common['Invalid email']);

			break;
		}

		default:
			fama_message($lang_common['Bad request']);
	}


	// Single quotes around non-empty values and NULL for empty values
	$temp = array();
	foreach ($form as $key => $input)
	{
		$value = ($input !== '') ? '\''.$db->escape($input).'\'' : 'NULL';

		$temp[] = $key.'='.$value;
	}

	if (empty($temp))
		fama_message($lang_common['Bad request']);


	$db->query('UPDATE '.$db->prefix.'users SET '.implode(',', $temp).' WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update profile', __FILE__, __LINE__, $db->error());

	// If we changed the username we have to update some stuff
	if ($username_updated)
	{
		$db->query('UPDATE '.$db->prefix.'posts SET poster=\''.$db->escape($form['username']).'\' WHERE poster_id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update posts', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'posts SET edited_by=\''.$db->escape($form['username']).'\' WHERE edited_by=\''.$db->escape($old_username).'\''.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update posts', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'topics SET poster=\''.$db->escape($form['username']).'\' WHERE poster=\''.$db->escape($old_username).'\''.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update topics', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'topics SET last_poster=\''.$db->escape($form['username']).'\' WHERE last_poster=\''.$db->escape($old_username).'\''.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update topics', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'forums SET last_poster=\''.$db->escape($form['username']).'\' WHERE last_poster=\''.$db->escape($old_username).'\''.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update forums', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'online SET ident=\''.$db->escape($form['username']).'\' WHERE ident=\''.$db->escape($old_username).'\''.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update online list', __FILE__, __LINE__, $db->error());

		// If the user is a moderator or an administrator we have to update the moderator lists
		$result = $db->query('SELECT group_id FROM '.$db->prefix.'users WHERE id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		$group_id = $db->result($result);

		$result = $db->query('SELECT g_moderator FROM '.$db->prefix.'groups WHERE g_id='.$group_id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch group', __FILE__, __LINE__, $db->error());
		$group_mod = $db->result($result);

		if ($group_id == PUN_ADMIN || $group_mod == '1')
		{
			$result = $db->query('SELECT id, moderators FROM '.$db->prefix.'forums'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

			while ($cur_forum = $db->fetch_assoc($result))
			{
				$cur_moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();

				if (in_array($id, $cur_moderators))
				{
					unset($cur_moderators[$old_username]);
					$cur_moderators[$form['username']] = $id;
					uksort($cur_moderators, 'utf8_strcasecmp');

					$db->query('UPDATE '.$db->prefix.'forums SET moderators=\''.$db->escape(serialize($cur_moderators)).'\' WHERE id='.$cur_forum['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update forum', __FILE__, __LINE__, $db->error());
				}
			}
		}
	}

	redirect('profile.php?section='.$section.'&amp;id='.$id, $lang_profile['Profile redirect']);
}


$result = $db->query('SELECT u.username, u.email, u.language, u.num_posts, u.last_post, u.registered, u.registration_ip, u.last_visit, g.g_id, g.g_user_title, g.g_moderator FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id='.$id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result))
	fama_message($lang_common['Bad request']);

$user = $db->fetch_assoc($result);

$last_post = format_time($user['last_post']);


// View or edit?
if ($pun_user['id'] != $id &&																	// If we arent the user (i.e. editing your own profile)
	(!$pun_user['is_admmod'] ||																	// and we are not an admin or mod
	($pun_user['g_id'] != PUN_ADMIN &&															// or we aren't an admin and ...
	($pun_user['g_mod_edit_users'] == '0' ||													// mods aren't allowed to edit users
	$user['g_id'] == PUN_ADMIN ||																// or the user is an admin
	$user['g_moderator'] == '1'))))																// or the user is another mod
{
	$user_personal = array();

	$user_personal[] = '<dt>'.$lang_common['Username'].'</dt>';
	$user_personal[] = '<dd>'.fama_htmlspecialchars($user['username']).'</dd>';

	$user_personal[] = '<dt>'.$lang_common['Title'].'</dt>';
	$user_personal[] = '<dd>'.fama_htmlspecialchars($user['g_user_title']).'</dd>';

	if (!$pun_user['is_guest'] && $pun_user['g_send_email'] == '1')
		$email_field = '<a href="misc.php?email='.$id.'">'.$lang_common['Send email'].'</a>';

	if ($email_field != '')
	{
		$user_personal[] = '<dt>'.$lang_common['Email'].'</dt>';
		$user_personal[] = '<dd><span class="email">'.$email_field.'</span></dd>';
	}

	$user_activity = array();

	if ($user['num_posts'] > 0)
	{
		$user_activity[] = '<dt>'.$lang_common['Last post'].'</dt>';
		$user_activity[] = '<dd>'.$last_post.'</dd>';
	}

	$user_activity[] = '<dt>'.$lang_common['Registered'].'</dt>';
	$user_activity[] = '<dd>'.format_time($user['registered'], true).'</dd>';

	$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), sprintf($lang_profile['Users profile'], fama_htmlspecialchars($user['username'])));
	define('PUN_ALLOW_INDEX', 1);
	define('PUN_ACTIVE_PAGE', 'index');
	require PUN_ROOT.'header.php';

?>
<div id="viewprofile" class="block">
	<h2><span><?php echo $lang_common['Profile'] ?></span></h2>
	<div class="box">
		<div class="fakeform">
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_profile['Section personal'] ?></legend>
					<div class="infldset">
						<dl>
							<?php echo implode("\n\t\t\t\t\t\t\t", $user_personal)."\n" ?>
						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
				<legend><?php echo $lang_profile['User activity'] ?></legend>
					<div class="infldset">
						<dl>
							<?php echo implode("\n\t\t\t\t\t\t\t", $user_activity)."\n" ?>
						</dl>
						<div class="clearer"></div>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
</div>

<?php

	require PUN_ROOT.'footer.php';
}
else
{
	if (!$section || $section == 'essentials')
	{
		if ($pun_user['is_admmod'])
		{
			if ($pun_user['g_id'] == PUN_ADMIN || $pun_user['g_mod_rename_users'] == '1')
				$username_field = '<label class="required"><strong>'.$lang_common['Username'].' <span>'.$lang_common['Required'].'</span></strong><br /><input type="text" name="req_username" value="'.fama_htmlspecialchars($user['username']).'" size="25" maxlength="25" /><br /></label>'."\n";
			else
				$username_field = '<p>'.sprintf($lang_profile['Username info'], fama_htmlspecialchars($user['username'])).'</p>'."\n";

			$email_field = '<label class="required"><strong>'.$lang_common['Email'].' <span>'.$lang_common['Required'].'</span></strong><br /><input type="text" name="req_email" value="'.$user['email'].'" size="40" maxlength="80" /><br /></label><p><span class="email"><a href="misc.php?email='.$id.'">'.$lang_common['Send email'].'</a></span></p>'."\n";
		}
		else
		{
			$username_field = '<p>'.$lang_common['Username'].': '.fama_htmlspecialchars($user['username']).'</p>'."\n";
			$email_field = '<label class="required"><strong>'.$lang_common['Email'].' <span>'.$lang_common['Required'].'</span></strong><br /><input type="text" name="req_email" value="'.$user['email'].'" size="40" maxlength="80" /><br /></label>'."\n";
		}

		$posts_field = '';
		$posts_actions = array();

		if ($pun_user['g_id'] == PUN_ADMIN)
			$posts_field .= '<label>'.$lang_common['Posts'].'<br /><input type="text" name="num_posts" value="'.$user['num_posts'].'" size="8" maxlength="8" /><br /></label>';

		if ($pun_user['g_search'] == '1' || $pun_user['g_id'] == PUN_ADMIN)
		{
			$posts_actions[] = '<a href="search.php?action=show_user_topics&amp;user_id='.$id.'">'.$lang_profile['Show topics'].'</a>';
			$posts_actions[] = '<a href="search.php?action=show_user_posts&amp;user_id='.$id.'">'.$lang_profile['Show posts'].'</a>';
		}

		$posts_field .= (!empty($posts_actions) ? '<p class="actions">'.implode(' - ', $posts_actions).'</p>' : '')."\n";


		$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_profile['Section essentials']);
		$required_fields = array('req_username' => $lang_common['Username'], 'req_email' => $lang_common['Email']);
		define('PUN_ACTIVE_PAGE', 'profile');
		require PUN_ROOT.'header.php';

		generate_profile_menu('essentials');

?>
	<div class="blockform">
		<h2><span><?php echo fama_htmlspecialchars($user['username']).' - '.$lang_profile['Section essentials'] ?></span></h2>
		<div class="box">
			<form id="profile1" method="post" action="profile.php?section=essentials&amp;id=<?php echo $id ?>" onsubmit="return process_form(this)">
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Username and pass legend'] ?></legend>
						<div class="infldset">
							<input type="hidden" name="form_sent" value="1" />
							<?php echo $username_field ?>
<?php if ($pun_user['id'] == $id || $pun_user['g_id'] == PUN_ADMIN || ($user['g_moderator'] == '0' && $pun_user['g_mod_change_passwords'] == '1')): ?>							<p class="actions"><span><a href="profile.php?action=change_pass&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Change pass'] ?></a></span></p>
<?php endif; ?>						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_prof_reg['Email legend'] ?></legend>
						<div class="infldset">
							<?php echo $email_field ?>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_prof_reg['Localisation legend'] ?></legend>
						<div class="infldset">
<?php

		$languages = forum_list_langs();

		// Only display the language selection box if there's more than one language available
		if (count($languages) > 1)
		{

?>
							<label><?php echo $lang_prof_reg['Language'] ?>
							<br /><select name="form[language]">
<?php

			foreach ($languages as $temp)
			{
				if ($user['language'] == $temp)
					echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
				else
					echo "\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
			}

?>
							</select>
							<br /></label>
<?php

		}

?>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['User activity'] ?></legend>
						<div class="infldset">
							<p><?php printf($lang_profile['Registered info'], format_time($user['registered'], true).(($pun_user['is_admmod']) ? ' (<a href="moderate.php?get_host='.fama_htmlspecialchars($user['registration_ip']).'">'.fama_htmlspecialchars($user['registration_ip']).'</a>)' : '')) ?></p>
							<p><?php printf($lang_profile['Last post info'], $last_post) ?></p>
							<p><?php printf($lang_profile['Last visit info'], format_time($user['last_visit'])) ?></p>
							<?php echo $posts_field ?>
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="update" value="<?php echo $lang_common['Submit'] ?>" /> <?php echo $lang_profile['Instructions'] ?></p>
			</form>
		</div>
	</div>
<?php

	}
	else if ($section == 'admin')
	{
		if (!$pun_user['is_admmod'])
			fama_message($lang_common['Bad request']);

		$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_common['Profile'], $lang_profile['Section admin']);
		define('PUN_ACTIVE_PAGE', 'profile');
		require PUN_ROOT.'header.php';

		generate_profile_menu('admin');

?>
	<div class="blockform">
		<h2><span><?php echo fama_htmlspecialchars($user['username']).' - '.$lang_profile['Section admin'] ?></span></h2>
		<div class="box">
			<form id="profile7" method="post" action="profile.php?section=admin&amp;id=<?php echo $id ?>">
				<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
					<fieldset>
<?php

		if ($pun_user['id'] != $id)
		{

?>
						<legend><?php echo $lang_profile['Group membership legend'] ?></legend>
						<div class="infldset">
							<select id="group_id" name="group_id">
<?php

			$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.PUN_GUEST.' ORDER BY g_title'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

			while ($cur_group = $db->fetch_assoc($result))
			{
				if ($cur_group['g_id'] == $user['g_id'] || ($cur_group['g_id'] == $pun_config['o_default_user_group'] && $user['g_id'] == ''))
					echo "\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.fama_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
				else
					echo "\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.fama_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
			}

?>
							</select>
							<input type="submit" name="update_group_membership" value="<?php echo $lang_profile['Save'] ?>" />
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
<?php

		}

?>
						<legend><?php echo $lang_profile['Delete ban legend'] ?></legend>
						<div class="infldset">
							<input type="submit" name="delete_user" value="<?php echo $lang_profile['Delete user'] ?>" />
						</div>
					</fieldset>
				</div>
<?php

		if ($user['g_moderator'] == '1' || $user['g_id'] == PUN_ADMIN)
		{

?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_profile['Set mods legend'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_profile['Moderator in info'] ?></p>
<?php

			$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators FROM '.$db->prefix.'forums AS f ORDER BY f.disp_position'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

			if ($db->num_rows($result) > 0)
			{
				echo "\t\t\t\t\t\t\t".'<div class="conl">'."\n\t\t\t\t\t\t\t\t".'<p><strong>Forum</strong></p>'."\n\t\t\t\t\t\t\t\t".'<div class="rbox">';
				
				while ($cur_forum = $db->fetch_assoc($result))
				{
					$moderators = ($cur_forum['moderators'] != '') ? unserialize($cur_forum['moderators']) : array();
					echo "\n\t\t\t\t\t\t\t\t\t".'<label><input type="checkbox" name="moderator_in['.$cur_forum['fid'].']" value="1"'.((in_array($id, $moderators)) ? ' checked="checked"' : '').' />'.fama_htmlspecialchars($cur_forum['forum_name']).'<br /></label>'."\n";
				}
				
				echo "\n\t\t\t\t\t\t\t\t".'</div>';
				echo "\n\t\t\t\t\t\t\t".'</div>'."\n";
			}

?>
							<br class="clearb" /><input type="submit" name="update_forums" value="<?php echo $lang_profile['Update forums'] ?>" />
						</div>
					</fieldset>
				</div>
<?php

		}

?>
			</form>
		</div>
	</div>
<?php

	}
	else
		fama_message($lang_common['Bad request']);

?>
	<div class="clearer"></div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}
