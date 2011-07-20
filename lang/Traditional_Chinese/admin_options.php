<?php

// Language definitions used in admin-options.php
$lang_admin_options = array(

'Bad HTTP Referer message'			=>	'HTTP_REFERER錯誤。如果您將公告板從一個目錄轉到另一個目錄或是變更了域名，您需要在數據庫中手動更新根目錄URL(base_url)然後刪除/cache目錄下的所有.php文件以清空cache。',
'Must enter title message'			=>	'您必須填寫公告板標題。',
'Invalid e-mail message'			=>	'您填寫了一個無效的管理信箱地址。',
'Invalid webmaster e-mail message'	=>	'您填寫了一個無效的郵件服務信箱地址。',
'SMTP passwords did not match'		=>	'您需要填寫並確認有效的SMTP密碼以變更密碼。',
'Enter announcement here'			=>	'請在此填寫公告。',
'Enter rules here'					=>	'請在此填寫條款。',
'Default maintenance message'		=>	'公告板維護臨時關閉。請稍後訪問。',
'Timeout error message'				=>	'「在線超時」的數值必須比「訪問超時」小。',
'Options updated redirect'			=>	'配置已更新。正在跳轉 …',
'Options head'						=>	'選項',

// Essentials section
'Essentials subhead'				=>	'概要',
'Board title label'					=>	'公告板標題',
'Board title help'					=>	'該公告板的名稱(將會顯示在每個頁面的頂部)。該欄<strong>不可</strong>包含HTML標記。',
'Board desc label'					=>	'公告板描述',
'Board desc help'					=>	'關於該公告板的簡短描述(將會顯示在每個頁面的頂部)。該欄可包含HTML標記。',
'Base URL label'					=>	'根目錄網址',
'Base URL help'						=>	'不包含結尾斜槓的完整網址(例如：http://www.mydomain.com/forums)。此處<strong>必須</strong>填寫正確才能使所有管理及版主功能運行。如果您收到「錯誤引用」的報錯，此處很可能未填寫正確。',
'Timezone label'					=>	'默認時區',
'Timezone help'						=>	'訪客以及用戶準備在公告板註冊時看到的公告板顯示時區。',
'DST label'							=>	'夏令時調整',
'DST help'							=>	'檢查夏令時是否生效(時間提前1小時)。',
'Language label'					=>	'默認語言',
'Language help'						=>	'訪客以及未在其個人資料中更改過語言設置的用戶所看到的默認語言。如果您移除了一個語言包，此處必須更新。',
'Default style label'				=>	'默認風格',
'Default style help'				=>	'訪客以及未在其個人資料中更改過風格設置的用戶所看到的默認風格。',

// Essentials section timezone options
'UTC-12:00'							=>	'(UTC-12:00) International Date Line West',
'UTC-11:00'							=>	'(UTC-11:00) Niue, Samoa',
'UTC-10:00'							=>	'(UTC-10:00) Hawaii-Aleutian, Cook Island',
'UTC-09:30'							=>	'(UTC-09:30) Marquesas Islands',
'UTC-09:00'							=>	'(UTC-09:00) Alaska, Gambier Island',
'UTC-08:30'							=>	'(UTC-08:30) Pitcairn Islands',
'UTC-08:00'							=>	'(UTC-08:00) Pacific',
'UTC-07:00'							=>	'(UTC-07:00) Mountain',
'UTC-06:00'							=>	'(UTC-06:00) Central',
'UTC-05:00'							=>	'(UTC-05:00) Eastern',
'UTC-04:00'							=>	'(UTC-04:00) Atlantic',
'UTC-03:30'							=>	'(UTC-03:30) Newfoundland',
'UTC-03:00'							=>	'(UTC-03:00) Amazon, Central Greenland',
'UTC-02:00'							=>	'(UTC-02:00) Mid-Atlantic',
'UTC-01:00'							=>	'(UTC-01:00) Azores, Cape Verde, Eastern Greenland',
'UTC'								=>	'(UTC) Western European, Greenwich',
'UTC+01:00'							=>	'(UTC+01:00) Central European, West African',
'UTC+02:00'							=>	'(UTC+02:00) Eastern European, Central African',
'UTC+03:00'							=>	'(UTC+03:00) Moscow, Eastern African',
'UTC+03:30'							=>	'(UTC+03:30) Iran',
'UTC+04:00'							=>	'(UTC+04:00) Gulf, Samara',
'UTC+04:30'							=>	'(UTC+04:30) Afghanistan',
'UTC+05:00'							=>	'(UTC+05:00) Pakistan, Yekaterinburg',
'UTC+05:30'							=>	'(UTC+05:30) India, Sri Lanka',
'UTC+05:45'							=>	'(UTC+05:45) Nepal',
'UTC+06:00'							=>	'(UTC+06:00) Bangladesh, Bhutan, Novosibirsk',
'UTC+06:30'							=>	'(UTC+06:30) Cocos Islands, Myanmar',
'UTC+07:00'							=>	'(UTC+07:00) Indochina, Krasnoyarsk',
'UTC+08:00'							=>	'(UTC+08:00) Greater China, Australian Western, Irkutsk',
'UTC+08:45'							=>	'(UTC+08:45) Southeastern Western Australia',
'UTC+09:00'							=>	'(UTC+09:00) Japan, Korea, Chita',
'UTC+09:30'							=>	'(UTC+09:30) Australian Central',
'UTC+10:00'							=>	'(UTC+10:00) Australian Eastern, Vladivostok',
'UTC+10:30'							=>	'(UTC+10:30) Lord Howe',
'UTC+11:00'							=>	'(UTC+11:00) Solomon Island, Magadan',
'UTC+11:30'							=>	'(UTC+11:30) Norfolk Island',
'UTC+12:00'							=>	'(UTC+12:00) New Zealand, Fiji, Kamchatka',
'UTC+12:45'							=>	'(UTC+12:45) Chatham Islands',
'UTC+13:00'							=>	'(UTC+13:00) Tonga, Phoenix Islands',
'UTC+14:00'							=>	'(UTC+14:00) Line Islands',

// Timeout Section
'Timeouts subhead'					=>	'時間與超時',
'Time format label'					=>	'時間格式',
'PHP manual'						=>	'PHP手冊',
'Time format help'					=>	'[當前格式： %s]. 請至%s查看格式選項。',
'Date format label'					=>	'日期格式',
'Date format help'					=>	'[當前格式： %s]. 請至%s查看格式選項。',
'Visit timeout label'				=>	'訪問超時',
'Visit timeout help'				=>	'用戶前次訪問數據更新後要計算新訪問必須空閒的秒數(主要影響新文章提示功能)。',
'Online timeout label'				=>	'在線超時',
'Online timeout help'				=>	'用戶被移出在線列表前必須空閒的秒數。',
'Redirect time label'				=>	'重定向時間',
'Redirect time help'				=>	'跳轉前必須等待的秒數。如果設置為0，跳轉頁面將不會出現(不推薦)。',

// Display Section
'Display subhead'					=>	'顯示',
'Version number label'				=>	'版本號',
'Version number help'				=>	'在頁腳顯示FluxBB的版本號。',
'Info in posts label'				=>	'發文章用戶相關信息',
'Info in posts help'				=>	'在用戶所發文章中的用戶名下顯示發文章用戶的相關信息。這些信息包括所在地，註冊日期，發文章數以及聯繫鏈接(電子信箱和網址)。',
'Post count label'					=>	'用戶發文章數統計',
'Post count help'					=>	'顯示用戶所發表的文章數目統計(涉及瀏覽文章頁面，個人資料頁面以及用戶列表頁面)。',
'Smilies label'						=>	'文章內表情',
'Smilies help'						=>	'將文章中的特定符號轉換為表情小圖標。',
'Smilies sigs label'				=>	'簽名內表情',
'Smilies sigs help'					=>	'將用戶簽名中的特定符號轉換為表情小圖標。',
'Clickable links label'				=>	'轉換網址為鏈接',
'Clickable links help'				=>	'啟用後，FluxBB將自動偵測發文章中包含的所有網址格式並將其轉換為超鏈接。',
'Topic review label'				=>	'通告回顧',
'Topic review help'					=>	'發文章時顯示舊文章回放的最大數目(最新文章靠前)。設為0禁用。',
'Topics per page label'				=>	'每頁通告',
'Topics per page help'				=>	'一個版塊中每頁面默認顯示的通告數目。用戶可自定義該數值。',
'Posts per page label'				=>	'每頁文章',
'Posts per page help'				=>	'一個通告中每頁面默認顯示的文章數目。用戶可自定義該數值。',
'Indent label'						=>	'縮進寬度',
'Indent help'						=>	'如果設為8，在[code][/code]標籤中的文本顯示時將使用一個規則的tab，否則即以設置值數個空格縮進文本。',
'Quote depth label'					=>	'[quote] 標籤引用深度',
'Quote depth help'					=>	'[quote] 標籤能夠包含更多 [quote] 標籤的最大次數，當引用深度超出設置值時將被丟棄。',

// Features section
'Features subhead'					=>	'特徵',
'Quick post label'					=>	'快速回復',
'Quick post help'					=>	'啟用後，FluxBB將在通告下添加一個快速發表回復的表單。通過它用戶便可在查看通告時直接發表回復。',
'Users online label'				=>	'在線統計',
'Users online help'					=>	'在首頁顯示正在瀏覽公告板的訪客及註冊用戶統計信息。',
'Censor words label'				=>	'詞彙過濾',
'Censor words help'					=>	'該功能可幫助公告板過濾敏感詞彙。參見%s查看更多信息。',
'Signatures label'					=>	'簽名',
'Signatures help'					=>	'允許用戶在他們的文章下附加簽名。',
'User ranks label'					=>	'用戶級別',
'User ranks help'					=>	'啟用它以使用戶級別生效。參見%s查看更多信息。',
'User has posted label'				=>	'已參與文章標記',
'User has posted help'				=>	'該功能可實現讓當前登入用戶通過通告前面的小點識別已參與過的通告。服務器負載過高時建議禁用。',
'Topic views label'					=>	'通告瀏覽次數',
'Topic views help'					=>	'跟蹤通告被瀏覽的次數。公告板繁忙服務器負載過高時建議禁用。',
'Quick jump label'					=>	'快速跳轉',
'Quick jump help'					=>	'啟用快速跳轉(跳轉版塊)下拉列表。',
'GZip label'						=>	'GZip壓縮輸出',
'GZip help'							=>	'啟用後，FluxBB會將輸出經gzip壓縮後再發送給瀏覽器。這將會節約寬帶使用，但是會稍耗點CPU。此功能要求PHP以zlib (--with-zlib)配置。注意：如果您已經有一個Apache的mod_gzip模塊或mod_deflate模塊設置為壓縮PHP腳本，您應禁用該功能。',
'Search all label'					=>	'搜索所有版塊',
'Search all help'					=>	'禁用後，每次搜索只可針對一個版塊。當過度搜索導致服務器負載過高時應禁用該功能。',
'Menu items label'					=>	'附加菜單項',
'Menu items help'					=>	'在文本框中填寫HTML超鏈接，可實現將菜單項添加到所有頁面的導航菜單中的任一排位數。添加新鏈接的格式為X = &lt;a href="URL"&gt;LINK&lt;/a&gt;，X表示在第幾位插入該鏈接(例如：0表示插入到最前面而2表示插入到「用戶列表」的後面)。每行填寫一條。',

// Feeds section
'Feed subhead'						=>	'聯合供稿',
'Default feed label'				=>	'默認訂閱類型',
'Default feed help'					=>	'選擇要顯示的供稿訂閱的類型。注意：選擇無並不能關閉訂閱，只表示默認不顯示。',
'None'								=>	'無',
'RSS'								=>	'RSS',
'Atom'								=>	'Atom',
'Feed TTL label'					=>	'訂閱的緩存時間',
'Feed TTL help'						=>	'訂閱可使用緩存，以減少訂閱所消耗的資源。',
'No cache'							=>	'不使用緩存',
'Minutes'							=>	'%d 分',

// Reports section
'Reports subhead'					=>	'舉報',
'Reporting method label'			=>	'接受舉報模式',
'Internal'							=>	'內部',
'By e-mail'							=>	'電子信箱',
'Both'								=>	'兼選',
'Reporting method help'				=>	'選擇通告/文章舉報的處理方式。您可選擇以內部舉報系統處理舉報，也可選擇將舉報發送到信箱列表(參見下文)，或者兩者兼選。',
'Mailing list label'				=>	'信箱列表',
'Mailing list help'					=>	'以逗號分隔的用來接受郵件的信箱列表。這份名單是舉報的收件人。',

// Avatars section
'Avatars subhead'					=>	'頭像',
'Use avatars label'					=>	'用戶頭像',
'Use avatars help'					=>	'啟用後，用戶可上傳頭像，頭像顯示在用戶的頭銜/級別之下。',
'Upload directory label'			=>	'上傳目錄',
'Upload directory help'				=>	'頭像上傳存在目錄(相對於FluxBB的根目錄)。必須為PHP開啟該目錄的寫入權限。',
'Max width label'					=>	'最大寬度',
'Max width help'					=>	'頭像寬度允許的最大像素(建議設60)。',
'Max height label'					=>	'最大高度',
'Max height help'					=>	'頭像高度允許的最大像素(建議設60)。',
'Max size label'					=>	'最大尺寸',
'Max size help'						=>	'頭像尺寸允許的最大字節數(建議設10240)。',

// E-mail section
'E-mail subhead'					=>	'電子郵件',
'Admin e-mail label'				=>	'管理信箱',
'Admin e-mail help'					=>	'站點管理員電子信箱地址。',
'Webmaster e-mail label'			=>	'郵件服務信箱',
'Webmaster e-mail help'				=>	'網站發出的所有郵件將會使用該地址為發件人。',
'Forum subscriptions label'			=>	'訂閱版塊',
'Forum subscriptions help'			=>	'允許用戶訂閱版塊(版塊有新通告時接收到提醒郵件)。',
'Topic subscriptions label'			=>	'訂閱通告',
'Topic subscriptions help'			=>	'允許用戶訂閱通告(通告有新回復時接收到提醒郵件)。',
'SMTP address label'				=>	'SMTP服務器地址',
'SMTP address help'					=>	'用來發送郵件的外部SMTP地址。如果某SMTP服務器沒有使用默認的25端口，您可指定一個自定義端口號(如：mail.myhost.com:3580)。要使用本地郵件程序請留空。',
'SMTP username label'				=>	'SMTP用戶名',
'SMTP username help'				=>	'登入SMTP服務器的用戶名。當SMTP服務器要求您提供用戶名時請填寫(很多服務器<strong>並不</strong>需要身份認證)。',
'SMTP password label'				=>	'SMTP密碼',
'SMTP change password help'			=>	'當您需要準備修改或者刪除當前所存儲的密碼前請查看此項。',
'SMTP password help'				=>	'登入SMTP服務器的密碼。當SMTP服務器要求您提供密碼時請填寫(很多服務器<strong>並不</strong>需要身份認證)。填寫密碼後需重複填寫進行確認。',
'SMTP SSL label'					=>	'SMTP使用SSL加密',
'SMTP SSL help'						=>	'為與SMTP服務器的連接使用SSL加密。僅當您的SMTP服務器要求時才需使用並且要您的PHP版本支持SSL。',

// Registration Section
'Registration subhead'				=>	'註冊',
'Allow new label'					=>	'允許新用戶註冊',
'Allow new help'					=>	'控制公告板是否允許新用戶註冊。若無特殊情況應該開啟。',
'Verify label'						=>	'註冊驗證',
'Verify help'						=>	'啟用後，用戶註冊時將會收到一個隨機密碼。如果他們認為需要，隨後他們便可在其個人資料中修改密碼。當用戶準備將註冊時填寫的電子信箱更換為新信箱時，該功能也會要求用戶驗證新信箱。這是避免濫用註冊的有效方法，並且可保證所有用戶在其個人資料中填寫了「正確的」電子信箱。',
'Report new label'					=>	'報告新註冊',
'Report new help'					=>	'啟用後，當有新用戶在公告板註冊時FluxBB將向信箱列表(參見上文)發送通知郵件。',
'Use rules label'					=>	'公告板服務條款',
'Use rules help'					=>	'啟用後，用戶註冊時將被要求必須接受設定的相關條款(內容請在下方填寫)。可打開該條款的鏈接將一直顯示在每個頁面上方的導航欄中。',
'Rules label'						=>	'請在此處填寫您的條款',
'Rules help'						=>	'您可在此填寫任何需要讓用戶在註冊前閱讀並接受的規則或其他信息。如果您在上面啟用了公告板服務條款功能您至少需在這裡寫點文字，否則它會被關閉。這段文字不會被當作正常的文章解析所以是可以包含HTML語法的。',
'E-mail default label'				=>	'電子信箱默認設置',
'E-mail default help'				=>	'選擇新用戶註冊時默認的隱私設置。',
'Display e-mail label'				=>	'向其他用戶公開您的電子信箱。',
'Hide allow form label'				=>	'隱藏電子信箱，但允許其他用戶通過公告板向您發送郵件。',
'Hide both label'					=>	'隱藏電子信箱，並阻止其他用戶通過公告板向您發送郵件。',

// Announcement Section
'Announcement subhead'				=>	'公告',
'Display announcement label'		=>	'顯示公告',
'Display announcement help'			=>	'啟用該功能可將下列消息發佈在公告板佈告欄。',
'Announcement message label'		=>	'公告內容',
'Announcement message help'			=>	'這段文字不會被當作正常的文章解析所以是可以包含HTML語法的。',

// Maintenance Section
'Maintenance subhead'				=>	'維護',
'Maintenance mode label'			=>	'維護模式',
'Maintenance mode help'				=>	'啟用後，公告板只對管理員開放。該功能用於公告板需要臨時掛起維護之時。<strong>警告！公告板處於維護模式時請勿登出。</strong>那會讓您再也登入不進來。',
'Maintenance message label'			=>	'維護通知',
'Maintenance message help'			=>	'當公告板處於維護模式時用戶訪問公告板會看到該消息。如果留空，將顯示一條默認通知。這段文字不會被當作正常的文章解析所以是可以包含HTML語法的。',

);
