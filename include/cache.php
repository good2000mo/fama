<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


//
// Generate the config cache PHP script
//
function generate_config_cache()
{
	global $db;

	// Get the forum config from the DB
	$result = $db->query('SELECT * FROM '.$db->prefix.'config'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --', true) or error('Unable to fetch forum config', __FILE__, __LINE__, $db->error());
	while ($cur_config_item = $db->fetch_row($result))
		$output[$cur_config_item[0]] = $cur_config_item[1];

	// Output config as PHP code
	$fh = @fopen(FORUM_CACHE_DIR.'cache_config.php', 'wb');
	if (!$fh)
		error('Unable to write configuration cache file to cache directory. Please make sure PHP has write access to the directory \''.pun_htmlspecialchars(FORUM_CACHE_DIR).'\'', __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'define(\'PUN_CONFIG_LOADED\', 1);'."\n\n".'$pun_config = '.var_export($output, true).';'."\n\n".'?>');

	fclose($fh);

	if (function_exists('apc_delete_file'))
		@apc_delete_file(FORUM_CACHE_DIR.'cache_config.php');
}


//
// Generate the bans cache PHP script
//
function generate_bans_cache()
{
	global $db;

	// Get the ban list from the DB
	$result = $db->query('SELECT * FROM '.$db->prefix.'bans'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --', true) or error('Unable to fetch ban list', __FILE__, __LINE__, $db->error());

	$output = array();
	while ($cur_ban = $db->fetch_assoc($result))
		$output[] = $cur_ban;

	// Output ban list as PHP code
	$fh = @fopen(FORUM_CACHE_DIR.'cache_bans.php', 'wb');
	if (!$fh)
		error('Unable to write bans cache file to cache directory. Please make sure PHP has write access to the directory \''.pun_htmlspecialchars(FORUM_CACHE_DIR).'\'', __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'define(\'PUN_BANS_LOADED\', 1);'."\n\n".'$pun_bans = '.var_export($output, true).';'."\n\n".'?>');

	fclose($fh);

	if (function_exists('apc_delete_file'))
		@apc_delete_file(FORUM_CACHE_DIR.'cache_bans.php');
}


//
// Generate the ranks cache PHP script
//
function generate_ranks_cache()
{
	global $db;

	// Get the rank list from the DB
	$result = $db->query('SELECT * FROM '.$db->prefix.'ranks ORDER BY min_posts'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --', true) or error('Unable to fetch rank list', __FILE__, __LINE__, $db->error());

	$output = array();
	while ($cur_rank = $db->fetch_assoc($result))
		$output[] = $cur_rank;

	// Output ranks list as PHP code
	$fh = @fopen(FORUM_CACHE_DIR.'cache_ranks.php', 'wb');
	if (!$fh)
		error('Unable to write ranks cache file to cache directory. Please make sure PHP has write access to the directory \''.pun_htmlspecialchars(FORUM_CACHE_DIR).'\'', __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'define(\'PUN_RANKS_LOADED\', 1);'."\n\n".'$pun_ranks = '.var_export($output, true).';'."\n\n".'?>');

	fclose($fh);

	if (function_exists('apc_delete_file'))
		@apc_delete_file(FORUM_CACHE_DIR.'cache_ranks.php');
}


//
// Generate the censoring cache PHP script
//
function generate_censoring_cache()
{
	global $db;

	$result = $db->query('SELECT search_for, replace_with FROM '.$db->prefix.'censoring'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --') or error('Unable to fetch censoring list', __FILE__, __LINE__, $db->error());
	$num_words = $db->num_rows($result);

	$search_for = $replace_with = array();
	for ($i = 0; $i < $num_words; $i++)
	{
		list($search_for[$i], $replace_with[$i]) = $db->fetch_row($result);
		$search_for[$i] = '%(?<=[^\p{L}\p{N}])('.str_replace('\*', '[\p{L}\p{N}]*?', preg_quote($search_for[$i], '%')).')(?=[^\p{L}\p{N}])%iu';
	}

	// Output censored words as PHP code
	$fh = @fopen(FORUM_CACHE_DIR.'cache_censoring.php', 'wb');
	if (!$fh)
		error('Unable to write censoring cache file to cache directory. Please make sure PHP has write access to the directory \''.pun_htmlspecialchars(FORUM_CACHE_DIR).'\'', __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'define(\'PUN_CENSOR_LOADED\', 1);'."\n\n".'$search_for = '.var_export($search_for, true).';'."\n\n".'$replace_with = '.var_export($replace_with, true).';'."\n\n".'?>');

	fclose($fh);

	if (function_exists('apc_delete_file'))
		@apc_delete_file(FORUM_CACHE_DIR.'cache_censoring.php');
}


//
// Generate the stopwords cache PHP script
//
function generate_stopwords_cache()
{
	$stopwords = array();

	$d = dir(PUN_ROOT.'lang');
	while (($entry = $d->read()) !== false)
	{
		if ($entry{0} == '.')
			continue;

		if (is_dir(PUN_ROOT.'lang/'.$entry) && file_exists(PUN_ROOT.'lang/'.$entry.'/stopwords.txt'))
			$stopwords = array_merge($stopwords, file(PUN_ROOT.'lang/'.$entry.'/stopwords.txt'));
	}
	$d->close();

	// Tidy up and filter the stopwords
	$stopwords = array_map('pun_trim', $stopwords);
	$stopwords = array_filter($stopwords);

	// Output stopwords as PHP code
	$fh = @fopen(FORUM_CACHE_DIR.'cache_stopwords.php', 'wb');
	if (!$fh)
		error('Unable to write stopwords cache file to cache directory. Please make sure PHP has write access to the directory \''.pun_htmlspecialchars(FORUM_CACHE_DIR).'\'', __FILE__, __LINE__);

	fwrite($fh, '<?php'."\n\n".'$cache_id = \''.generate_stopwords_cache_id().'\';'."\n".'if ($cache_id != generate_stopwords_cache_id()) return;'."\n\n".'define(\'PUN_STOPWORDS_LOADED\', 1);'."\n\n".'$stopwords = '.var_export($stopwords, true).';'."\n\n".'?>');

	fclose($fh);

	if (function_exists('apc_delete_file'))
		@apc_delete_file(FORUM_CACHE_DIR.'cache_stopwords.php');
}


define('FORUM_CACHE_FUNCTIONS_LOADED', true);
