# discordpost.ini.php

 discordの webhookを使って、discordへのポストを実施するpukiwikiプラグイン。
 webhookの仕組み・仕様をもとに、次のような仕様動作とする

 ・ポスト動作は、このプラグインが置かれたページに表示される「ボタンの押下」をトリガーに実施される。   
 ・ポストの内容は、このプラグインが置かれているページのURLのみ。   
 ・いたずら防止のため、ログイン状態でのみ有効（webhookはdiscord上で個人が指定するものであるため、その「特定の人」でないとダメという考え方）   
  上記を踏まえ、プラグイン内に「ユーザ」と「対応するwebhook」を持たせる。

 書式
 #discordpost( [id] [,sent_state])
 
 id : webhookの識別名。任意に決めてよい。省略時は'default'.   
 sent_state：状態。次の値のいずれかをとる    
  '_ready' ,NULL： 未送信。「送信」ボタンを表示する。   
  '_sent' ： 送信済。そもそも#discordpostに関する表示を一切しない。   
  '_err' （他任意値） : 送信したがエラー。ボタン出さない。

単純にpluginディレクトリに設置するだけでは動きません。  
定義用のphpファイル「discord_webhookinfo.ini.php」内、次のようにある部分を編集したうえで設置してください。

 webhook登録。書式は次のとおり。  
 			// '識別名' => array(   
			//	'user'	=> '利用できるユーザ名',  
			//	'url'	=> 'webhookurl',  
			//	),  
			//	識別名：任意文字列。プラグイン引数に使う.  
			//	利用できるユーザ名： pukiwikiのユーザ名  
			//	webhook url：対応するwebhook url  
			// 最低でも１つ、識別名が'default'の設定が必要。  
 
 array形式になっているので、複数のwebhookを登録可能。
 pukiwiki側で『&discordpost(xxxx)』というように指定することで、識別名 xxxx のwebhookを叩く。  
 discord側に書き込む内容は、vars[$refer] に基づいた当該ページのURLです（通常、actionを呼び出したページのURL）  


また、他のポスト処理からこのプラグインを呼び出すことも想定しています。  
例えば commentプラグインの actionの最後のほう（書き込み処理が終わってreturnする前に）  
以下のように記載することで、当該コメントに書き込みがなされたタイミングでdiscordで通知を行うことができます。
>  if(file_exists(PLUGIN_DIR.'discordpost.inc.php')){  
      require_once PLUGIN_DIR.'discordpost.inc.php';  
      plugin_discordpost_init();  
      plugin_discordpost_post('default',1);  
  }  



 

