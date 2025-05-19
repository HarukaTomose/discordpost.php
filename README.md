# discordpost.php.ini
// discordの webhookを使って、discordへのポストを実施するpukiwikiプラグイン。

// webhookの仕組み・仕様をもとに、次のような仕様動作とする

// ・ポスト動作は、このプラグインが置かれたページに表示される「ボタンの押下」をトリガーに実施される。
// ・ポストの内容は、このプラグインが置かれているページのURLのみ。
// ・いたずら防止のため、ログイン状態でのみ有効（webhookはdiscord上で個人が指定するものであるため、その「特定の人」でないとダメという考え方）
//  上記を踏まえ、プラグイン内に「ユーザ」と「対応するwebhook」を持たせる。

// 書式
// #discordpost( [id] [,sent_state])
// id : webhookの識別名。任意に決めてよい。省略時は'default'.
//  <sent_state>の値域
// '_ready' ,NULL： 未送信。「送信」ボタンを表示する。
// '_sent' ： 送信済。そもそも#discordpostに関する表示を一切しない。
// '_err' （他任意値） : 送信したがエラー。ボタン出さない。

単純にpluginディレクトリに設置するだけでは動きません。
プラグインのファイル内、次のようにある部分を編集したうえで設置してください。

	// webhook登録。書式は次のとおり。
	// '識別名' => '利用できるユーザ名|webhook url'
	//	識別名：任意の名称。これをプラグインの引数にする. 下記例では 'default' となっている。
	//	利用できるユーザ名： pukiwikiのユーザ名。下記例では 'username' となっている。
	//	webhook url：対応するwebhook url。
	// array形式になっているので、複数のwebhookを登録可能。
 	// pukiwiki側で『&discordpost(xxxx)』というように指定することで、識別名 xxxx のwebhookを叩く。
	static $discordpost_list = array(
		// 'default'=> 'username|https://discord.com/api/webhooks/xxxxxxxxxxxxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
	);

