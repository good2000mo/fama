<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


if ($pun_user['g_read_board'] == '0')
	fama_message($lang_common['No view']);


$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
if ($tid < 1 && $fid < 1 || $tid > 0 && $fid > 0)
	fama_message($lang_common['Bad request']);

// Fetch some info about the topic and/or the forum
if ($tid)
	$result = $db->query('SELECT f.id, f.forum_name, f.moderators, fp.post_replies, fp.post_topics, t.subject, t.closed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$tid.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());
else
	$result = $db->query('SELECT f.id, f.forum_name, f.moderators, fp.post_replies, fp.post_topics FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND f.id='.$fid.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch forum info', __FILE__, __LINE__, $db->error());

if (!$db->num_rows($result))
	fama_message($lang_common['Bad request']);

$cur_posting = $db->fetch_assoc($result);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_posting['moderators'] != '') ? unserialize($cur_posting['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_moderator'] == '1' && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Do we have permission to post?
if ((($tid && (($cur_posting['post_replies'] == '' && $pun_user['g_post_replies'] == '0') || $cur_posting['post_replies'] == '0')) ||
	($fid && (($cur_posting['post_topics'] == '' && $pun_user['g_post_topics'] == '0') || $cur_posting['post_topics'] == '0')) ||
	(isset($cur_posting['closed']) && $cur_posting['closed'] == '1')) &&
	!$is_admmod)
	fama_message($lang_common['No permission']);

// Load the post.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

// Start with a clean slate
$errors = array();


// Did someone just hit "Submit" or "Preview"?
if (isset($_POST['form_sent']))
{
	// Flood protection
	if (!isset($_POST['preview']) && $pun_user['last_post'] != '' && (time() - $pun_user['last_post']) < $pun_user['g_post_flood'])
		$errors[] = $lang_post['Flood start'].' '.$pun_user['g_post_flood'].' '.$lang_post['flood end'];

	// If it's a new topic
	if ($fid)
	{
		$subject = pun_trim($_POST['req_subject']);

		if ($subject == '')
			$errors[] = $lang_post['No subject'];
		else if (pun_strlen($subject) > 70)
			$errors[] = $lang_post['Too long subject'];
	}

	// If the user is logged in we get the username and email from $pun_user
	if (!$pun_user['is_guest'])
	{
		$username = $pun_user['username'];
		$email = $pun_user['email'];
	}
	// Otherwise it should be in $_POST
	else
	{
		$username = pun_trim($_POST['req_username']);
		$email = strtolower(trim($_POST['req_email']));

		// Load the register.php/prof_reg.php language files
		require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';
		require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';

		// It's a guest, so we have to validate the username
		check_username($username);

		require PUN_ROOT.'include/email.php';
		if (!is_valid_email($email))
			$errors[] = $lang_common['Invalid email'];
	}

	// Clean up message from POST
	$orig_message = $message = pun_linebreaks(pun_trim($_POST['req_message']));

	// Here we use strlen() not pun_strlen() as we want to limit the post to PUN_MAX_POSTSIZE bytes, not characters
	if (strlen($message) > PUN_MAX_POSTSIZE)
		$errors[] = sprintf($lang_post['Too long message'], forum_number_format(PUN_MAX_POSTSIZE));

	// Validate BBCode syntax
	require PUN_ROOT.'include/parser.php';
	$message = preparse_bbcode($message, $errors);

	if (empty($errors))
	{
		if ($message == '')
			$errors[] = $lang_post['No message'];
	}

	$stick_topic = isset($_POST['stick_topic']) && $is_admmod ? '1' : '0';

	$now = time();

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))
	{
		require PUN_ROOT.'include/search_idx.php';

		// If it's a reply
		if ($tid)
		{
			if (!$pun_user['is_guest'])
			{
				$new_tid = $tid;

				// Insert the new post
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_id, poster_ip, message, posted, topic_id) VALUES(\''.$db->escape($username).'\', '.$pun_user['id'].', \''.get_remote_address().'\', \''.$db->escape($message).'\', '.$now.', '.$tid.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to create post', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();
			}
			else
			{
				// It's a guest. Insert the new post
				$email_sql = '\''.$db->escape($email).'\'';
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_ip, poster_email, message, posted, topic_id) VALUES(\''.$db->escape($username).'\', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($message).'\', '.$now.', '.$tid.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to create post', __FILE__, __LINE__, $db->error());
				$new_pid = $db->insert_id();
			}

			// Count number of replies in the topic
			$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'posts WHERE topic_id='.$tid.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
			$num_replies = $db->result($result, 0) - 1;

			// Update topic
			$db->query('UPDATE '.$db->prefix.'topics SET num_replies='.$num_replies.', last_post='.$now.', last_post_id='.$new_pid.', last_poster=\''.$db->escape($username).'\' WHERE id='.$tid.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update topic', __FILE__, __LINE__, $db->error());

			update_search_index('post', $new_pid, $message);

			update_forum($cur_posting['id']);
		}
		// If it's a new topic
		else if ($fid)
		{
			// Create the topic
			$db->query('INSERT INTO '.$db->prefix.'topics (poster, subject, posted, last_post, last_poster, sticky, forum_id) VALUES(\''.$db->escape($username).'\', \''.$db->escape($subject).'\', '.$now.', '.$now.', \''.$db->escape($username).'\', '.$stick_topic.', '.$fid.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to create topic', __FILE__, __LINE__, $db->error());
			$new_tid = $db->insert_id();

			if (!$pun_user['is_guest'])
			{
				// Create the post ("topic post")
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_id, poster_ip, message, posted, topic_id) VALUES(\''.$db->escape($username).'\', '.$pun_user['id'].', \''.get_remote_address().'\', \''.$db->escape($message).'\', '.$now.', '.$new_tid.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to create post', __FILE__, __LINE__, $db->error());
			}
			else
			{
				// Create the post ("topic post")
				$email_sql = '\''.$db->escape($email).'\'';
				$db->query('INSERT INTO '.$db->prefix.'posts (poster, poster_ip, poster_email, message, posted, topic_id) VALUES(\''.$db->escape($username).'\', \''.get_remote_address().'\', '.$email_sql.', \''.$db->escape($message).'\', '.$now.', '.$new_tid.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to create post', __FILE__, __LINE__, $db->error());
			}
			$new_pid = $db->insert_id();

			// Update the topic with last_post_id
			$db->query('UPDATE '.$db->prefix.'topics SET last_post_id='.$new_pid.', first_post_id='.$new_pid.' WHERE id='.$new_tid.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update topic', __FILE__, __LINE__, $db->error());

			update_search_index('post', $new_pid, $message, $subject);

			update_forum($fid);
		}

		// If the posting user is logged in, increment his/her post count
		if (!$pun_user['is_guest'])
		{
			$db->query('UPDATE '.$db->prefix.'users SET num_posts=num_posts+1, last_post='.$now.' WHERE id='.$pun_user['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update user', __FILE__, __LINE__, $db->error());
		}
		else
		{
			$db->query('UPDATE '.$db->prefix.'online SET last_post='.$now.' WHERE ident=\''.$db->escape(get_remote_address()).'\'' .' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update user', __FILE__, __LINE__, $db->error());
		}

		redirect('viewtopic.php?pid='.$new_pid.'#p'.$new_pid, $lang_post['Post redirect']);
	}
}


// If a topic ID was specified in the url (it's a reply)
if ($tid)
{
	$action = $lang_post['Post a reply'];
	$form = '<form id="post" method="post" action="post.php?action=post&amp;tid='.$tid.'" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">';

	// If a quote ID was specified in the url
	if (isset($_GET['qid']))
	{
		$qid = intval($_GET['qid']);
		if ($qid < 1)
			fama_message($lang_common['Bad request']);

		$result = $db->query('SELECT poster, message FROM '.$db->prefix.'posts WHERE id='.$qid.' AND topic_id='.$tid.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch quote info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			fama_message($lang_common['Bad request']);

		list($q_poster, $q_message) = $db->fetch_row($result);

		// If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
		if (strpos($q_message, '[code]') !== false && strpos($q_message, '[/code]') !== false)
		{
			$errors = array();
			list($inside, $outside) = split_text($q_message, '[code]', '[/code]', $errors);
			if (!empty($errors)) // Technically this shouldn't happen, since $q_message is an existing post it should only exist if it previously passed validation
				fama_message($errors[0]);

			$q_message = implode("\1", $outside);
		}

		// Remove [img] tags from quoted message
		$q_message = preg_replace('%\[img(?:=(?:[^\[]*?))?\]((ht|f)tps?://)([^\s<"]*?)\[/img\]%U', '\1\3', $q_message);

		// If we split up the message before we have to concatenate it together again (code tags)
		if (isset($inside))
		{
			$outside = explode("\1", $q_message);
			$q_message = '';

			$num_tokens = count($outside);
			for ($i = 0; $i < $num_tokens; ++$i)
			{
				$q_message .= $outside[$i];
				if (isset($inside[$i]))
					$q_message .= '[code]'.$inside[$i].'[/code]';
			}

			unset($inside);
		}

		$q_message = fama_htmlspecialchars($q_message);

		// If username contains a square bracket, we add "" or '' around it (so we know when it starts and ends)
		if (strpos($q_poster, '[') !== false || strpos($q_poster, ']') !== false)
		{
			if (strpos($q_poster, '\'') !== false)
				$q_poster = '"'.$q_poster.'"';
			else
				$q_poster = '\''.$q_poster.'\'';
		}
		else
		{
			// Get the characters at the start and end of $q_poster
			$ends = substr($q_poster, 0, 1).substr($q_poster, -1, 1);

			// Deal with quoting "Username" or 'Username' (becomes '"Username"' or "'Username'")
			if ($ends == '\'\'')
				$q_poster = '"'.$q_poster.'"';
			else if ($ends == '""')
				$q_poster = '\''.$q_poster.'\'';
		}

		$quote = '[quote='.$q_poster.']'.$q_message.'[/quote]'."\n";
	}
}
// If a forum ID was specified in the url (new topic)
else if ($fid)
{
	$action = $lang_post['Post new topic'];
	$form = '<form id="post" method="post" action="post.php?action=post&amp;fid='.$fid.'" onsubmit="return process_form(this)">';
}
else
	fama_message($lang_common['Bad request']);


$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $action);
$required_fields = array('req_email' => $lang_common['Email'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('post');

if (!$pun_user['is_guest'])
	$focus_element[] = ($fid) ? 'req_subject' : 'req_message';
else
{
	$required_fields['req_username'] = $lang_post['Guest name'];
	$focus_element[] = 'req_username';
}

define('PUN_ACTIVE_PAGE', 'index');
require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<ul class="crumbs">
			<li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li>
			<li><span>»&#160;</span><a href="viewforum.php?id=<?php echo $cur_posting['id'] ?>"><?php echo fama_htmlspecialchars($cur_posting['forum_name']) ?></a></li>
<?php if (isset($cur_posting['subject'])): ?>			<li><span>»&#160;</span><a href="viewtopic.php?id=<?php echo $tid ?>"><?php echo fama_htmlspecialchars($cur_posting['subject']) ?></a></li>
<?php endif; ?>			<li><span>»&#160;</span><strong><?php echo $action ?></strong></li>
		</ul>
	</div>
</div>

<?php

// If there are errors, we display them
if (!empty($errors))
{

?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul class="error-list">
<?php

	foreach ($errors as $cur_error)
		echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>

<?php

}
else if (isset($_POST['preview']))
{
	require_once PUN_ROOT.'include/parser.php';
	$preview_message = parse_message($message);

?>
<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_post['Post preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postbody">
				<div class="postright">
					<div class="postmsg">
						<?php echo $preview_message."\n" ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

}


$cur_index = 1;

?>
<div id="postform" class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
		<?php echo $form."\n" ?>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Write message legend'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
<?php

if ($pun_user['is_guest'])
{
	$email_label = '<strong>'.$lang_common['Email'].' <span>'.$lang_common['Required'].'</span></strong>';
	$email_form_name = 'req_email';

?>
						<label class="conl required"><strong><?php echo $lang_post['Guest name'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="text" name="req_username" value="<?php if (isset($_POST['req_username'])) echo fama_htmlspecialchars($username); ?>" size="25" maxlength="25" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<label class="conl required"><?php echo $email_label ?><br /><input type="text" name="<?php echo $email_form_name ?>" value="<?php if (isset($_POST[$email_form_name])) echo fama_htmlspecialchars($email); ?>" size="50" maxlength="80" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
						<div class="clearer"></div>
<?php

}

if ($fid): ?>
						<label class="required"><strong><?php echo $lang_common['Subject'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input class="longinput" type="text" name="req_subject" value="<?php if (isset($_POST['req_subject'])) echo fama_htmlspecialchars($subject); ?>" size="80" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
<?php endif; ?>						<label class="required"><strong><?php echo $lang_common['Message'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<textarea name="req_message" rows="20" cols="95" tabindex="<?php echo $cur_index++ ?>"><?php echo isset($_POST['req_message']) ? fama_htmlspecialchars($orig_message) : (isset($quote) ? $quote : ''); ?></textarea><br /></label>
						<ul class="bblinks">
							<li><span><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a> <?php echo $lang_common['on']; ?></span></li>
							<li><span><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a> <?php echo $lang_common['on']; ?></span></li>
						</ul>
					</div>
				</fieldset>
<?php

$checkboxes = array();
if ($is_admmod && $fid)
	$checkboxes[] = '<label><input type="checkbox" name="stick_topic" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['stick_topic']) ? ' checked="checked"' : '').' />'.$lang_common['Stick topic'].'<br /></label>';

if (!empty($checkboxes))
{

?>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Options'] ?></legend>
					<div class="infldset">
						<div class="rbox">
							<?php echo implode("\n\t\t\t\t\t\t\t", $checkboxes)."\n" ?>
						</div>
					</div>
				</fieldset>
<?php

}

?>
			</div>
			<p class="buttons"><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /> <input type="submit" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>

<?php

require PUN_ROOT.'footer.php';
