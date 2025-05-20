<?php

function plugin_discord_webhookinfo_init()
{

	$messages = array(
		'_discord_webhookinfo' => array(
			// webhook登録。書式は次のとおり。
			// '識別名' => array( 
			//	'user'	=> '利用できるユーザ名',
			//	'url'	=> 'webhookurl',
			//	),
			//	識別名：任意文字列。プラグイン引数に使う.
			//	利用できるユーザ名： pukiwikiのユーザ名
			//	webhook url：対応するwebhook url
			// 最低でも１つ、識別名が'default'の設定が必要。

			// sample start-----
			'default'=> array(
				'user'	=> 'username',
				'url'	=> 'https://discord.com/api/webhooks/xxxxxxxxxxxxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
				),
			// ------- sample end

		),
	);

	set_plugin_messages($messages);

	//　これにより、以降プラグイン本体側では次のようにして動作を行う
	//
	// $tgtid == プラグイン引数で指定したID。
	// これをもとに要求Webhookが登録されているかをチェック。
	// if(!array_key_exists($tgtid,$_discordpost_webhookid)) {}
	//
	// いまのログインユーザがこのWebhookを使っていいか
	// $curuser=get_auth_user();
	// if($_discord_webhookinfo[$tgtid][user]!=$curuser)
	//
	// 以上でOKなら、
	//$_discord_webhookinfo[$tgtid][url] が使ってよい webhookURL.


}

?>

