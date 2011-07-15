<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';


if ($pun_user['g_id'] != PUN_ADMIN)
	message($lang_common['No permission']);

// Load the admin_options.php language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_options.php';

if (isset($_POST['form_sent']))
{
	confirm_referrer('admin_options.php', $lang_admin_options['Bad HTTP Referer message']);

	$form = array(
		'board_title'			=> pun_trim($_POST['form']['board_title']),
		'board_desc'			=> pun_trim($_POST['form']['board_desc']),
		'default_timezone'		=> floatval($_POST['form']['default_timezone']),
		'default_lang'			=> pun_trim($_POST['form']['default_lang']),
		'time_format'			=> pun_trim($_POST['form']['time_format']),
		'date_format'			=> pun_trim($_POST['form']['date_format']),
		'timeout_visit'			=> intval($_POST['form']['timeout_visit']),
		'timeout_online'		=> intval($_POST['form']['timeout_online']),
		'redirect_delay'		=> intval($_POST['form']['redirect_delay']),
		'disp_topics_default'	=> intval($_POST['form']['disp_topics_default']),
		'disp_posts_default'	=> intval($_POST['form']['disp_posts_default']),
		'indent_num_spaces'		=> intval($_POST['form']['indent_num_spaces']),
		'quote_depth'			=> intval($_POST['form']['quote_depth']),
		'gzip'					=> $_POST['form']['gzip'] != '1' ? '0' : '1',
		'admin_email'			=> strtolower(pun_trim($_POST['form']['admin_email'])),
		'webmaster_email'		=> strtolower(pun_trim($_POST['form']['webmaster_email'])),
		'smtp_host'				=> pun_trim($_POST['form']['smtp_host']),
		'smtp_user'				=> pun_trim($_POST['form']['smtp_user']),
		'smtp_ssl'				=> $_POST['form']['smtp_ssl'] != '1' ? '0' : '1',
	);

	if ($form['board_title'] == '')
		message($lang_admin_options['Must enter title message']);

	$languages = forum_list_langs();
	if (!in_array($form['default_lang'], $languages))
		message($lang_common['Bad request']);

	if ($form['time_format'] == '')
		$form['time_format'] = 'H:i:s';

	if ($form['date_format'] == '')
		$form['date_format'] = 'Y-m-d';


	require PUN_ROOT.'include/email.php';

	if (!is_valid_email($form['admin_email']))
		message($lang_admin_options['Invalid e-mail message']);

	if (!is_valid_email($form['webmaster_email']))
		message($lang_admin_options['Invalid webmaster e-mail message']);

	// Change or enter a SMTP password
	if (isset($_POST['form']['smtp_change_pass']))
	{
		$smtp_pass1 = isset($_POST['form']['smtp_pass1']) ? pun_trim($_POST['form']['smtp_pass1']) : '';
		$smtp_pass2 = isset($_POST['form']['smtp_pass2']) ? pun_trim($_POST['form']['smtp_pass2']) : '';

		if ($smtp_pass1 == $smtp_pass2)
			$form['smtp_pass'] = $smtp_pass1;
		else
			message($lang_admin_options['SMTP passwords did not match']);
	}

	// Make sure the number of displayed topics and posts is between 3 and 75
	if ($form['disp_topics_default'] < 3)
		$form['disp_topics_default'] = 3;
	else if ($form['disp_topics_default'] > 75)
		$form['disp_topics_default'] = 75;

	if ($form['disp_posts_default'] < 3)
		$form['disp_posts_default'] = 3;
	else if ($form['disp_posts_default'] > 75)
		$form['disp_posts_default'] = 75;

	if ($form['timeout_online'] >= $form['timeout_visit'])
		message($lang_admin_options['Timeout error message']);

	foreach ($form as $key => $input)
	{
		// Only update values that have changed
		if (array_key_exists('o_'.$key, $pun_config) && $pun_config['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input))
				$value = '\''.$db->escape($input).'\'';
			else
				$value = 'NULL';

			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\''.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}

	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();

	redirect('admin_options.php', $lang_admin_options['Options updated redirect']);
}

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_admin_common['Admin'], $lang_admin_common['Options']);
define('PUN_ACTIVE_PAGE', 'admin');
require PUN_ROOT.'header.php';

generate_admin_menu('options');

?>
	<div class="blockform">
		<h2><span><?php echo $lang_admin_options['Options head'] ?></span></h2>
		<div class="box">
			<form method="post" action="admin_options.php">
				<p class="submittop"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></p>
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend><?php echo $lang_admin_options['Essentials subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Board title label'] ?></th>
									<td>
										<input type="text" name="form[board_title]" size="50" maxlength="255" value="<?php echo pun_htmlspecialchars($pun_config['o_board_title']) ?>" />
										<span><?php echo $lang_admin_options['Board title help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Board desc label'] ?></th>
									<td>
										<input type="text" name="form[board_desc]" size="50" maxlength="255" value="<?php echo pun_htmlspecialchars($pun_config['o_board_desc']) ?>" />
										<span><?php echo $lang_admin_options['Board desc help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Timezone label'] ?></th>
									<td>
										<select name="form[default_timezone]">
											<option value="-12"<?php if ($pun_config['o_default_timezone'] == -12) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-12:00'] ?></option>
											<option value="-11"<?php if ($pun_config['o_default_timezone'] == -11) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-11:00'] ?></option>
											<option value="-10"<?php if ($pun_config['o_default_timezone'] == -10) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-10:00'] ?></option>
											<option value="-9.5"<?php if ($pun_config['o_default_timezone'] == -9.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-09:30'] ?></option>
											<option value="-9"<?php if ($pun_config['o_default_timezone'] == -9) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-09:00'] ?></option>
											<option value="-8.5"<?php if ($pun_config['o_default_timezone'] == -8.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-08:30'] ?></option>
											<option value="-8"<?php if ($pun_config['o_default_timezone'] == -8) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-08:00'] ?></option>
											<option value="-7"<?php if ($pun_config['o_default_timezone'] == -7) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-07:00'] ?></option>
											<option value="-6"<?php if ($pun_config['o_default_timezone'] == -6) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-06:00'] ?></option>
											<option value="-5"<?php if ($pun_config['o_default_timezone'] == -5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-05:00'] ?></option>
											<option value="-4"<?php if ($pun_config['o_default_timezone'] == -4) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-04:00'] ?></option>
											<option value="-3.5"<?php if ($pun_config['o_default_timezone'] == -3.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-03:30'] ?></option>
											<option value="-3"<?php if ($pun_config['o_default_timezone'] == -3) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-03:00'] ?></option>
											<option value="-2"<?php if ($pun_config['o_default_timezone'] == -2) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-02:00'] ?></option>
											<option value="-1"<?php if ($pun_config['o_default_timezone'] == -1) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC-01:00'] ?></option>
											<option value="0"<?php if ($pun_config['o_default_timezone'] == 0) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC'] ?></option>
											<option value="1"<?php if ($pun_config['o_default_timezone'] == 1) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+01:00'] ?></option>
											<option value="2"<?php if ($pun_config['o_default_timezone'] == 2) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+02:00'] ?></option>
											<option value="3"<?php if ($pun_config['o_default_timezone'] == 3) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+03:00'] ?></option>
											<option value="3.5"<?php if ($pun_config['o_default_timezone'] == 3.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+03:30'] ?></option>
											<option value="4"<?php if ($pun_config['o_default_timezone'] == 4) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+04:00'] ?></option>
											<option value="4.5"<?php if ($pun_config['o_default_timezone'] == 4.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+04:30'] ?></option>
											<option value="5"<?php if ($pun_config['o_default_timezone'] == 5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+05:00'] ?></option>
											<option value="5.5"<?php if ($pun_config['o_default_timezone'] == 5.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+05:30'] ?></option>
											<option value="5.75"<?php if ($pun_config['o_default_timezone'] == 5.75) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+05:45'] ?></option>
											<option value="6"<?php if ($pun_config['o_default_timezone'] == 6) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+06:00'] ?></option>
											<option value="6.5"<?php if ($pun_config['o_default_timezone'] == 6.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+06:30'] ?></option>
											<option value="7"<?php if ($pun_config['o_default_timezone'] == 7) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+07:00'] ?></option>
											<option value="8"<?php if ($pun_config['o_default_timezone'] == 8) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+08:00'] ?></option>
											<option value="8.75"<?php if ($pun_config['o_default_timezone'] == 8.75) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+08:45'] ?></option>
											<option value="9"<?php if ($pun_config['o_default_timezone'] == 9) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+09:00'] ?></option>
											<option value="9.5"<?php if ($pun_config['o_default_timezone'] == 9.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+09:30'] ?></option>
											<option value="10"<?php if ($pun_config['o_default_timezone'] == 10) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+10:00'] ?></option>
											<option value="10.5"<?php if ($pun_config['o_default_timezone'] == 10.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+10:30'] ?></option>
											<option value="11"<?php if ($pun_config['o_default_timezone'] == 11) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+11:00'] ?></option>
											<option value="11.5"<?php if ($pun_config['o_default_timezone'] == 11.5) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+11:30'] ?></option>
											<option value="12"<?php if ($pun_config['o_default_timezone'] == 12) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+12:00'] ?></option>
											<option value="12.75"<?php if ($pun_config['o_default_timezone'] == 12.75) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+12:45'] ?></option>
											<option value="13"<?php if ($pun_config['o_default_timezone'] == 13) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+13:00'] ?></option>
											<option value="14"<?php if ($pun_config['o_default_timezone'] == 14) echo ' selected="selected"' ?>><?php echo $lang_admin_options['UTC+14:00'] ?></option>
										</select>
										<span><?php echo $lang_admin_options['Timezone help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Language label'] ?></th>
									<td>
										<select name="form[default_lang]">
<?php

		$languages = forum_list_langs();

		foreach ($languages as $temp)
		{
			if ($pun_config['o_default_lang'] == $temp)
				echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
			else
				echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
		}

?>
										</select>
										<span><?php echo $lang_admin_options['Language help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php

	$diff = $pun_user['timezone'] * 3600;
	$timestamp = time() + $diff;

?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_options['Timeouts subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Time format label'] ?></th>
									<td>
										<input type="text" name="form[time_format]" size="25" maxlength="25" value="<?php echo pun_htmlspecialchars($pun_config['o_time_format']) ?>" />
										<span><?php printf($lang_admin_options['Time format help'], gmdate($pun_config['o_time_format'], $timestamp), '<a href="http://www.php.net/manual/en/function.date.php">'.$lang_admin_options['PHP manual'].'</a>') ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Date format label'] ?></th>
									<td>
										<input type="text" name="form[date_format]" size="25" maxlength="25" value="<?php echo pun_htmlspecialchars($pun_config['o_date_format']) ?>" />
										<span><?php printf($lang_admin_options['Date format help'], gmdate($pun_config['o_date_format'], $timestamp), '<a href="http://www.php.net/manual/en/function.date.php">'.$lang_admin_options['PHP manual'].'</a>') ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Visit timeout label'] ?></th>
									<td>
										<input type="text" name="form[timeout_visit]" size="5" maxlength="5" value="<?php echo $pun_config['o_timeout_visit'] ?>" />
										<span><?php echo $lang_admin_options['Visit timeout help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Online timeout label'] ?></th>
									<td>
										<input type="text" name="form[timeout_online]" size="5" maxlength="5" value="<?php echo $pun_config['o_timeout_online'] ?>" />
										<span><?php echo $lang_admin_options['Online timeout help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Redirect time label'] ?></th>
									<td>
										<input type="text" name="form[redirect_delay]" size="3" maxlength="3" value="<?php echo $pun_config['o_redirect_delay'] ?>" />
										<span><?php echo $lang_admin_options['Redirect time help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_options['Display subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Topics per page label'] ?></th>
									<td>
										<input type="text" name="form[disp_topics_default]" size="3" maxlength="3" value="<?php echo $pun_config['o_disp_topics_default'] ?>" />
										<span><?php echo $lang_admin_options['Topics per page help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Posts per page label'] ?></th>
									<td>
										<input type="text" name="form[disp_posts_default]" size="3" maxlength="3" value="<?php echo $pun_config['o_disp_posts_default'] ?>" />
										<span><?php echo $lang_admin_options['Posts per page help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Indent label'] ?></th>
									<td>
										<input type="text" name="form[indent_num_spaces]" size="3" maxlength="3" value="<?php echo $pun_config['o_indent_num_spaces'] ?>" />
										<span><?php echo $lang_admin_options['Indent help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Quote depth label'] ?></th>
									<td>
										<input type="text" name="form[quote_depth]" size="3" maxlength="3" value="<?php echo $pun_config['o_quote_depth'] ?>" />
										<span><?php echo $lang_admin_options['Quote depth help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_options['Features subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_options['GZip label'] ?></th>
									<td>
										<input type="radio" name="form[gzip]" value="1"<?php if ($pun_config['o_gzip'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong>&#160;&#160;&#160;<input type="radio" name="form[gzip]" value="0"<?php if ($pun_config['o_gzip'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong>
										<span><?php echo $lang_admin_options['GZip help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_options['E-mail subhead'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Admin e-mail label'] ?></th>
									<td>
										<input type="text" name="form[admin_email]" size="50" maxlength="80" value="<?php echo $pun_config['o_admin_email'] ?>" />
										<span><?php echo $lang_admin_options['Admin e-mail help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['Webmaster e-mail label'] ?></th>
									<td>
										<input type="text" name="form[webmaster_email]" size="50" maxlength="80" value="<?php echo $pun_config['o_webmaster_email'] ?>" />
										<span><?php echo $lang_admin_options['Webmaster e-mail help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['SMTP address label'] ?></th>
									<td>
										<input type="text" name="form[smtp_host]" size="30" maxlength="100" value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_host']) ?>" />
										<span><?php echo $lang_admin_options['SMTP address help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['SMTP username label'] ?></th>
									<td>
										<input type="text" name="form[smtp_user]" size="25" maxlength="50" value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_user']) ?>" />
										<span><?php echo $lang_admin_options['SMTP username help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['SMTP password label'] ?></th>
									<td>
										<span><input type="checkbox" name="form[smtp_change_pass]" value="1" />&#160;&#160;<?php echo $lang_admin_options['SMTP change password help'] ?></span>
<?php $smtp_pass = !empty($pun_config['o_smtp_pass']) ? random_key(pun_strlen($pun_config['o_smtp_pass']), true) : ''; ?>
										<input type="password" name="form[smtp_pass1]" size="25" maxlength="50" value="<?php echo $smtp_pass ?>" />
										<input type="password" name="form[smtp_pass2]" size="25" maxlength="50" value="<?php echo $smtp_pass ?>" />
										<span><?php echo $lang_admin_options['SMTP password help'] ?></span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_options['SMTP SSL label'] ?></th>
									<td>
										<input type="radio" name="form[smtp_ssl]" value="1"<?php if ($pun_config['o_smtp_ssl'] == '1') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['Yes'] ?></strong>&#160;&#160;&#160;<input type="radio" name="form[smtp_ssl]" value="0"<?php if ($pun_config['o_smtp_ssl'] == '0') echo ' checked="checked"' ?> />&#160;<strong><?php echo $lang_admin_common['No'] ?></strong>
										<span><?php echo $lang_admin_options['SMTP SSL help'] ?></span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
