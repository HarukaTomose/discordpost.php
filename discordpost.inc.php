<?php
// Plugin for PukiWiki
// $Id: discordpost.inc.php,v 0.11  2025.Mar.19  Haruka Tomose  pukiwiki1.5.向け
// http://tomose.net/junk/index.php?Top%20Page.
//
// discordの webhookを使って、discordへのポストを実施するプラグイン。
// 以前に作ったトラックバック送信プラグインをもとに、
//	送信部分を discord用に書き換えた感じ。
// webhookの仕組み・仕様をもとに、次のような仕様動作とする

// ・ポスト動作は、「当該ページの宣伝を意図したもの」。
//	そのページのリンクをポストする。
//	そのページにogタグで情報をつけてあれば、詳細はdicordが勝手に表示する
// ・ポストは自動ではなく、利用者による「ボタン押下」をトリガーにする。
// ・いたずら防止のため、ログイン状態でのみ有効。
//　　（webhookは人が指定するものであるため、「特定の人」でないとダメ。）
//  上記を踏まえ、プラグイン内に「ユーザ」と「対応するwebhook」を持たせる。


// 書式
// #discordpost( [id] [,sent_state])
// id : webhookの識別名。任意に決めてよい。省略時は'default'.
//  <sent_state>の値域
// '_ready' ,NULL： 未送信。「送信」ボタンを表示する。
// '_sent' ： 送信済。そもそも#discordpostに関する表示を一切しない。
// '_err' （他任意値） : 送信したがエラー。ボタン出さない。



function plugin_discordpost_init()
{

	$messages = array(
		'_discordpost_mes'	=> array(
			'premessage'	=> '#discordpost(): targetID=',
			'btn_send'	=> 'Post.',
			'msg_badid'	=> '#discordpost(): Selected id isnot available.',
			'msg_permit'	=> '#discordpost(): You donot have parmition to selected ID.'
		),

		'_discordpost_webhookid' => array(
			// webhook登録。書式は次のとおり。
			// '識別名' => '利用できるユーザ名|webhook url'
			//	識別名：任意文字列。プラグイン引数に使う.
			//	利用できるユーザ名： pukiwikiのユーザ名
			//	webhook url：対応するwebhook url
			// 最低でも１つ、識別名が'default'の設定が必要。
			'default'=> 'username|https://discord.com/api/webhooks/xxxxxxxxxxxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
		
		),
		
	);

	set_plugin_messages($messages);
}




function plugin_discordpost_inline()
{

	global $script, $vars,  $digest;
	global $_discordpost_mes;
	global $_discordpost_webhookid;
	static $number = array();

	$page = isset($vars['page']) ? $vars['page'] : '';

	// 1ページ内での複数使用対応のお約束。
	if (! isset($number[$page])) $number[$page] = 0; // Init
	$discordpost_no = $number[$page]++; // これがこのdiscordpost の識別番号になる。
	
	// パラメータナシならエラー・・・は、本プラグインでは存在しない。
	//if (! func_num_args()) return $_discordpost_mes['msg_no_arg'] . "\n";

	$args     = func_get_args();
	$s_page   = htmlsc($page);
	$s_digest = htmlsc($digest);
	
	$ttext = array_shift($args); //送信対象の WebhookへのID

	if (! isset($number[$page])) $number[$page] = 0; // Init

	if($ttext=='') { $ttext='default'; }

	if(!array_key_exists($ttext,$_discordpost_webhookid)) {
		//指定されたIDに対応する登録がない。
		return $_discordpost_mes['msg_badid'] . "\n";
	}

	$tmp= explode('|',$_discordpost_webhookid[$ttext]);
		// $tmp[0] : user名
		// $tmp[1] : webhook url

	// 指定されたwebhook名が現在のユーザ名に対応していなければ、やはりアウト。	
	$curusr = get_auth_user();

	if( ($curusr=='')or($curusr!=$tmp[0])){
		return $_discordpost_mes['msg_permit'] . "\n";

	}

	$trslt = (count($args)) ? array_shift($args): "_ready";
		if(! $trslt ) $trslt= "_ready";

	//すでに送信すみなら、画面上にはなにも表示しない。
	if(	$trslt=='_sent'){ return ''; }
	if(	$trslt!='_ready'){
		// _readyではないならなんらかのエラーが書いてある。そのまま表示。
		 return $trslt;
	}

	//以下 _ready==送信していない場合.「このボタンを押すと送信する」旨の表示。	$rslt = "";
	$rslt .= $_discordpost_mes['premessage'].$ttext."";
	$btntxt = $_discordpost_mes['btn_send'];
	if ( $trslt === '_ready' ) {
		// トラックバック送信するためのボタン埋め込み。

		$rslt = <<<EOD
<form action="$_script" method="post">$rslt
    <input type="hidden" name="plugin"  value="discordpost" />
    <input type="hidden" name="refer"   value="$s_page" />
    <input type="hidden" name="discordpost_no" value="$discordpost_no" />
    <input type="hidden" name="digest"  value="$s_digest" />
    <input type="hidden" name="target"  value="$turl" />
    <input type="hidden" name="webhookid"  value="$ttext" />
    <input type="submit" name="discordpost_btn" value="$btntxt" class="submit" />
</form>
EOD;
	}

	return $rslt;
}

function plugin_discordpost_action()
{
	global $vars, $script, $cols,$rows;
	global $_vote_plugin_votes;
	global $_discordpost_webhookid;

	//編集権限がなければ、即アウト。
	if (!check_editable($vars['refer'])) return;

	//引数から今回のwebhookを特定する。
	$tmp=$vars['webhookid'];
	//入り口側で防いでいるので論理的にはありえないが、一応バカ除け。
	//知らないID指定してきたらエラー。
	if ($tmp=='') { $tmp='default'; }
	if(!array_key_exists($tmp,$_discordpost_webhookid)) return; //対応する登録なし。

	$tmp= explode('|',$_discordpost_webhookid[$tmp]);
		// $tmp[0] : user名
		// $tmp[1] : webhook url

	//入り口側で防いでいるので論理的にはありえないが、一応バカ除け。
	// 指定されたwebhook名が現在のユーザ名に対応していなければ、やはりアウト。
	$curusr = get_auth_user();
	if($curusr=='') return; 
	if($curusr!=$tmp[0]) return;

	
	$body ="";
	$title = "";
	$matches = array();
	$discordpost_ct = 0;
	$discordpost_no = $vars['discordpost_no'];


	// 送信！
	$rsend = sendToDiscord($tmp[1]);
	//
	$msg= "&discordpost(".$vars['webhookid'].",".$rsend.")";	

	// 送信結果を送信元ページに反映する。
	// 以前の元ページのデータを取り出す。
	$postdata_old  = get_source($vars['refer']);

	// そこにある「今回の対象」を探し出す。
	// 受け取ったパラメータから「$discordpost_no番目」のデータなのはわかっている。

	$skipflag = 0;
	foreach ($postdata_old as $line)
	{
		if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
			// コメント行、PRE行、および「スキップする」条件ではスキップ。
			$postdata .= $line;
			continue;
		}
		$ct = preg_match_all('/&discordpost(\([^(){};]*\))/',$line, $out);
		if ( $ct ){
		// 今注目している行に $discordpost があることが分かったので、チェックしていく。
		for($i=0; $i < $ct; $i++){
		    if ($discordpost_ct++ == $discordpost_no ){
			// まさに今見ているのが書き換え対象。置き換える。
			$line = preg_replace('/&discordpost(\([^(){};]*\))?/',$msg,$line,1);
			$skipflag = 1; //置換えたので、以降はすべてスキップする。
			break;
		    }
		    else {
			// 今いる行に複数&discordpostがあって、まだたどり着いていない。
			// 最初のほうに近いもの１つを __discordpost__に書き換えて退避、次へ。
			$line = preg_replace('/&discordpost(\([^(){};]*\))?;/','&___discordpost$1___;',$line,1);
		    }
		}
		// 全部終わったので、退避していた __discordpost__を元に戻す。
		$line = preg_replace('/&___discordpost(\([^(){};]*\))?___;/','&discordpost$1;',$line);
	    }
	    $postdata .= $line;
	}

	// ここまでで、新しいpostdata ができている。

	// 編集衝突調査

	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $_title_collided;
		$s_refer          = htmlsc($vars['refer']);
		$s_digest         = htmlsc($vars['digest']);
		$s_postdata_input = htmlsc($postdata_input);
		$body = <<<EOD
$_msg_collided
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer"  value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata_input</textarea><br />
 </div>
</form>

EOD;
	} else {
		page_write($vars['refer'], $postdata,TRUE);
		$title = $_title_updated;
	}
	$vars['page'] = $vars['refer'];

	//return false;
	return array('msg'=>$title, 'body'=>$body);

}



function sendToDiscord($webhookUrl) {
	global $vars, $script, $cols,$rows;
	global $_vote_plugin_votes;

		//通知メセッジを設定
		//ユーザーIDでメンションしたい場合は、<@discord_user_id>で設定
		//eg.,'content'  => "<@1095224863111111111> Hello, this message is coming from PHP!",
	$rslt="";

	$content= $script."?".rawurlencode($vars['refer']);

    $message = [
        'content'  => $content,
    ];

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $rslt= 'Curl error: ' . curl_error($ch);
    } else {
        $rslt= '_sent';
    }

    curl_close($rslt);

	return $rslt;

}

?>
