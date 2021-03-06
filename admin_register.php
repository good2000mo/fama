<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


// If we are logged in, we shouldn't be here
if (!$pun_user['is_guest'])
{
	header('Location: index.php');
	exit;
}

// Load the register.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';

// Load the register.php/profile.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';


// User pressed the cancel button
if (isset($_GET['cancel']))
	redirect('index.php', $lang_register['Reg cancel redirect']);


// Start with a clean slate
$errors = array();

if (isset($_POST['form_sent']))
{
	// Check that someone from this IP didn't register a user within the last hour (DoS prevention)
	$result = $db->query('SELECT 1 FROM '.$db->prefix.'users WHERE registration_ip=\''.get_remote_address().'\' AND registered>'.(time() - 3600).' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result))
		fama_message($lang_register['Registration flood']);


	$username = pun_trim($_POST['req_user']);
	$email1 = strtolower(trim($_POST['req_email1']));
	$password1 = pun_trim($_POST['req_password1']);
	$password2 = pun_trim($_POST['req_password2']);

	// Validate username and passwords
	check_username($username);

	if (pun_strlen($password1) < 4)
		$errors[] = $lang_prof_reg['Pass too short'];
	else if ($password1 != $password2)
		$errors[] = $lang_prof_reg['Pass not match'];

	// Validate email
	require PUN_ROOT.'include/email.php';

	if (!is_valid_email($email1))
		$errors[] = $lang_common['Invalid email'];

	// Make sure we got a valid language string
	if (isset($_POST['language']))
	{
		$language = preg_replace('%[\.\\\/]%', '', $_POST['language']);
		if (!file_exists(PUN_ROOT.'lang/'.$language.'/common.php'))
			fama_message($lang_common['Bad request']);
	}
	else
		$language = $pun_config['o_default_lang'];

	// Did everything go according to plan?
	if (empty($errors))
	{
		// Insert the new user into the database. We do this now to get the last inserted ID for later use
		$now = time();

		$intial_group_id = $pun_config['o_default_user_group'];
		$password_hash = pun_hash($password1);

		// Add the user
		$db->query('INSERT INTO '.$db->prefix.'users (username, group_id, password, email, language, registered, registration_ip, last_visit) VALUES(\''.$db->escape($username).'\', '.$intial_group_id.', \''.$password_hash.'\', \''.$db->escape($email1).'\', \''.$db->escape($language).'\', '.$now.', \''.get_remote_address().'\', '.$now.')'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or fama_error('Unable to create user', __FILE__, __LINE__, $db->error());
		$new_uid = $db->insert_id();

		pun_setcookie($new_uid, $password_hash, time() + $pun_config['o_timeout_visit']);

		redirect('index.php', $lang_register['Reg complete']);
	}
}


$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']), $lang_register['Register']);
$required_fields = array('req_user' => $lang_common['Username'], 'req_password1' => $lang_common['Password'], 'req_password2' => $lang_prof_reg['Confirm pass'], 'req_email1' => $lang_common['Email'], 'req_email2' => $lang_common['Email'].' 2');
$focus_element = array('register', 'req_user');
define('PUN_ACTIVE_PAGE', 'register');
require PUN_ROOT.'header.php';

$timezone = $pun_config['o_default_timezone'];

// If there are errors, we display them
if (!empty($errors))
{

?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_register['Registration errors'] ?></span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p><?php echo $lang_register['Registration errors info'] ?></p>
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
?>
<div id="regform" class="blockform">
	<h2><span><?php echo $lang_register['Register'] ?></span></h2>
	<div class="box">
		<form id="register" method="post" action="register.php?action=register" onsubmit="this.register.disabled=true;if(process_form(this)){return true;}else{this.register.disabled=false;return false;}">
			<div class="inform">
				<div class="forminfo">
					<h3><?php echo $lang_common['Important information'] ?></h3>
					<p><?php echo $lang_register['Desc 1'] ?></p>
					<p><?php echo $lang_register['Desc 2'] ?></p>
				</div>
				<fieldset>
					<legend><?php echo $lang_register['Username legend'] ?></legend>
					<div class="infldset">
						<input type="hidden" name="form_sent" value="1" />
						<label class="required"><strong><?php echo $lang_common['Username'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="text" name="req_user" value="<?php if (isset($_POST['req_user'])) echo fama_htmlspecialchars($_POST['req_user']); ?>" size="25" maxlength="25" /><br /></label>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_register['Pass legend'] ?></legend>
					<div class="infldset">
						<label class="conl required"><strong><?php echo $lang_common['Password'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="password" name="req_password1" value="<?php if (isset($_POST['req_password1'])) echo fama_htmlspecialchars($_POST['req_password1']); ?>" size="16" /><br /></label>
						<label class="conl required"><strong><?php echo $lang_prof_reg['Confirm pass'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="password" name="req_password2" value="<?php if (isset($_POST['req_password2'])) echo fama_htmlspecialchars($_POST['req_password2']); ?>" size="16" /><br /></label>
						<p class="clearb"><?php echo $lang_register['Pass info'] ?></p>
					</div>
				</fieldset>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_prof_reg['Email legend'] ?></legend>
					<div class="infldset">
						<label class="required"><strong><?php echo $lang_common['Email'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
						<input type="text" name="req_email1" value="<?php if (isset($_POST['req_email1'])) echo fama_htmlspecialchars($_POST['req_email1']); ?>" size="50" maxlength="80" /><br /></label>
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
							<br /><select name="language">
<?php

			foreach ($languages as $temp)
			{
				if ($pun_config['o_default_lang'] == $temp)
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
			<p class="buttons"><input type="submit" name="register" value="<?php echo $lang_register['Register'] ?>" /></p>
		</form>
	</div>
</div>
<?php

require PUN_ROOT.'footer.php';
