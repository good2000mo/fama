<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (isset($_GET['action']))
	define('PUN_QUIET_VISIT', 1);

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


// Load the misc.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/misc.php';

$action = isset($_GET['action']) ? $_GET['action'] : null;


if (isset($_GET['email']))
{
	if ($pun_user['is_guest'] || $pun_user['g_send_email'] == '0')
		fama_message($lang_common['No permission']);

	$recipient_id = intval($_GET['email']);
	if ($recipient_id < 2)
		fama_message($lang_common['Bad request']);

	$result = $db->query('SELECT username, email FROM '.$db->prefix.'users WHERE id='.$recipient_id.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		fama_message($lang_common['Bad request']);

	list($recipient, $recipient_email) = $db->fetch_row($result);


	if (isset($_POST['form_sent']))
	{
		// Clean up message and subject from POST
		$subject = pun_trim($_POST['req_subject']);
		$message = pun_trim($_POST['req_message']);

		if ($subject == '')
			fama_message($lang_misc['No email subject']);
		else if ($message == '')
			fama_message($lang_misc['No email message']);
		else if (pun_strlen($message) > PUN_MAX_POSTSIZE)
			fama_message($lang_misc['Too long email message']);

		if ($pun_user['last_email_sent'] != '' && (time() - $pun_user['last_email_sent']) < $pun_user['g_email_flood'] && (time() - $pun_user['last_email_sent']) >= 0)
			fama_message(sprintf($lang_misc['Email flood'], $pun_user['g_email_flood']));

		// Load the "form email" template
		$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pun_user['language'].'/mail_templates/form_email.tpl'));

		// The first row contains the subject
		$first_crlf = strpos($mail_tpl, "\n");
		$mail_subject = pun_trim(substr($mail_tpl, 8, $first_crlf-8));
		$mail_message = pun_trim(substr($mail_tpl, $first_crlf));

		$mail_subject = str_replace('<mail_subject>', $subject, $mail_subject);
		$mail_message = str_replace('<sender>', $pun_user['username'], $mail_message);
		$mail_message = str_replace('<board_title>', $pun_config['o_board_title'], $mail_message);
		$mail_message = str_replace('<mail_message>', $message, $mail_message);
		$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'].' '.$lang_common['Mailer'], $mail_message);

		require_once PUN_ROOT.'include/email.php';

		pun_mail($recipient_email, $mail_subject, $mail_message, $pun_user['email'], $pun_user['username']);

		$db->query('UPDATE '.$db->prefix.'users SET last_email_sent='.time().' WHERE id='.$pun_user['id'].' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to update user', __FILE__, __LINE__, $db->error());

		redirect(fama_htmlspecialchars($_POST['redirect_url']), $lang_misc['Email sent redirect']);
	}


	// Try to determine if the data in HTTP_REFERER is valid (if not, we redirect to the users profile after the email is sent)
	if (!empty($_SERVER['HTTP_REFERER']))
	{
		$referrer = parse_url($_SERVER['HTTP_REFERER']);
		// Remove www subdomain if it exists
		if (strpos($referrer['host'], 'www.') === 0)
			$referrer['host'] = substr($referrer['host'], 4);

		$valid = parse_url(get_base_url());
		// Remove www subdomain if it exists
		if (strpos($valid['host'], 'www.') === 0)
			$valid['host'] = substr($valid['host'], 4);

		if ($referrer['host'] == $valid['host'] && preg_match('%^'.preg_quote($valid['path'], '%').'/(.*?)\.php%i', $referrer['path']))
			$redirect_url = $_SERVER['HTTP_REFERER'];
	}

	if (!isset($redirect_url))
		$redirect_url = 'profile.php?id='.$recipient_id;

	$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_misc['Send email to'].' '.fama_htmlspecialchars($recipient));
	$required_fields = array('req_subject' => $lang_misc['Email subject'], 'req_message' => $lang_misc['Email message']);
	$focus_element = array('email', 'req_subject');
	define('PUN_ACTIVE_PAGE', 'index');
	require PUN_ROOT.'header.php';

?>
<div id="emailform" class="blockform">
	<h2><span><?php echo $lang_misc['Send email to'] ?> <?php echo fama_htmlspecialchars($recipient) ?></span></h2>
	<div class="box">
		<form id="email" method="post" action="misc.php?email=<?php echo $recipient_id ?>" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_misc['Write email'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="redirect_url" value="<?php echo fama_htmlspecialchars($redirect_url) ?>" />
						<label class="required"><strong><?php echo $lang_misc['Email subject'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<input class="longinput" type="text" name="req_subject" size="75" maxlength="70" tabindex="1" /><br /></label>
						<label class="required"><strong><?php echo $lang_misc['Email message'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<textarea name="req_message" rows="10" cols="75" tabindex="2"></textarea><br /></label>
						<p><?php echo $lang_misc['Email disclosure note'] ?></p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="3" accesskey="s" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


else
	fama_message($lang_common['Bad request']);
