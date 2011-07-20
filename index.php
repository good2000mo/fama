<?php

// $page_title (for header.php)
// 設置<title>
// 
// PUN_ALLOW_INDEX (for header.php)
// 跟搜索引擎有關
// 
// PUN_ACTIVE_PAGE (for header.php)
// 說明現在是什麼頁面
// 

// 1. 載入common
define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';

// 2. 檢查讀取權限
if ($pun_user['g_read_board'] == '0')
	fama_message($lang_common['No view']);

// 3. 載入語言檔
require PUN_ROOT.'lang/'.$pun_user['language'].'/index.php';

// 4. 載入header
$page_title = array(fama_htmlspecialchars($pun_config['o_board_title']));
define('PUN_ALLOW_INDEX', 1);
define('PUN_ACTIVE_PAGE', 'index');
require PUN_ROOT.'header.php';

// 5. 印公告板出來
// f.id as fid
// f.forum_name
// f.forum_desc
// f.moderators
// f.num_topics
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.forum_desc, f.moderators, f.num_topics FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY f.disp_position'.' -- sqlcomment: '.__FILE__.' line:'.__LINE__.' --', true) or fama_error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result) > 0)
{

?>
<div id="idx1" class="blocktable">
	<h2><span><?php echo $lang_common['Forum'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<ul>
<?php

while ($cur_forum = $db->fetch_assoc($result))
{
	$forum_field = '<h3><a href="viewforum.php?id='.$cur_forum['fid'].'">'.fama_htmlspecialchars($cur_forum['forum_name']).'</a>'.'</h3>';

	if ($cur_forum['forum_desc'] != '')
		$forum_field .= "\n\t\t\t\t\t".'<div class="forumdesc">'.$cur_forum['forum_desc'].'</div>';

?>
				<li>
					<?php echo $forum_field ?>
				</li>
<?php

}

?>
			<ul>
		</div>
	</div>
</div>
<?php

}
else
	echo '<div id="idx0" class="block"><div class="box"><div class="inbox"><p>'.$lang_index['Empty board'].'</p></div></div></div>';

?>

<?php

$footer_style = 'index';
require PUN_ROOT.'footer.php';
