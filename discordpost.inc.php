<?php
// Plugin for PukiWiki
// $Id: discordpost.inc.php,v 0.11  2025.Mar.19  Haruka Tomose  pukiwiki1.5.����
// http://tomose.net/junk/index.php?Top%20Page.
//
// discord�� webhook��Ȥäơ�discord�ؤΥݥ��Ȥ�»ܤ���ץ饰����
// �����˺�ä��ȥ�å��Хå������ץ饰������Ȥˡ�
//	������ʬ�� discord�Ѥ˽񤭴�����������
// webhook�λ��Ȥߡ����ͤ��Ȥˡ����Τ褦�ʻ���ư��Ȥ���

// ���ݥ���ư��ϡ��������ڡ�����������տޤ�����Ρס�
//	���Υڡ����Υ�󥯤�ݥ��Ȥ��롣
//	���Υڡ�����og�����Ǿ����Ĥ��Ƥ���С��ܺ٤�dicord�������ɽ������
// ���ݥ��Ȥϼ�ư�ǤϤʤ������ѼԤˤ��֥ܥ��󲡲��פ�ȥꥬ���ˤ��롣
// �����������ɻߤΤ��ᡢ��������֤ǤΤ�ͭ����
//������webhook�Ͽͤ����ꤹ���ΤǤ��뤿�ᡢ������ο͡פǤʤ��ȥ��ᡣ��
//  �嵭��Ƨ�ޤ����ץ饰������ˡ֥桼���פȡ��б�����webhook�פ�������롣


// ��
// #discordpost( [id] [,sent_state])
// id : webhook�μ���̾��Ǥ�դ˷��Ƥ褤����ά����'default'.
//  <sent_state>���Ͱ�
// '_ready' ,NULL�� ̤�������������ץܥ����ɽ�����롣
// '_sent' �� �����ѡ����⤽��#discordpost�˴ؤ���ɽ������ڤ��ʤ���
// '_err' ��¾Ǥ���͡� : �������������顼���ܥ���Ф��ʤ���



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
			// webhook��Ͽ���񼰤ϼ��ΤȤ��ꡣ
			// '����̾' => '���ѤǤ���桼��̾|webhook url'
			//	����̾��Ǥ��ʸ���󡣥ץ饰��������˻Ȥ�.
			//	���ѤǤ���桼��̾�� pukiwiki�Υ桼��̾
			//	webhook url���б�����webhook url
			// ����Ǥ⣱�ġ�����̾��'default'�����꤬ɬ�ס�
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

	// 1�ڡ�����Ǥ�ʣ�������б��Τ���«��
	if (! isset($number[$page])) $number[$page] = 0; // Init
	$discordpost_no = $number[$page]++; // ���줬����discordpost �μ����ֹ�ˤʤ롣
	
	// �ѥ�᡼���ʥ��ʤ饨�顼�������ϡ��ܥץ饰����Ǥ�¸�ߤ��ʤ���
	//if (! func_num_args()) return $_discordpost_mes['msg_no_arg'] . "\n";

	$args     = func_get_args();
	$s_page   = htmlsc($page);
	$s_digest = htmlsc($digest);
	
	$ttext = array_shift($args); //�����оݤ� Webhook�ؤ�ID

	if (! isset($number[$page])) $number[$page] = 0; // Init

	if($ttext=='') { $ttext='default'; }

	if(!array_key_exists($ttext,$_discordpost_webhookid)) {
		//���ꤵ�줿ID���б�������Ͽ���ʤ���
		return $_discordpost_mes['msg_badid'] . "\n";
	}

	$tmp= explode('|',$_discordpost_webhookid[$ttext]);
		// $tmp[0] : user̾
		// $tmp[1] : webhook url

	// ���ꤵ�줿webhook̾�����ߤΥ桼��̾���б����Ƥ��ʤ���С���Ϥꥢ���ȡ�	
	$curusr = get_auth_user();

	if( ($curusr=='')or($curusr!=$tmp[0])){
		return $_discordpost_mes['msg_permit'] . "\n";

	}

	$trslt = (count($args)) ? array_shift($args): "_ready";
		if(! $trslt ) $trslt= "_ready";

	//���Ǥ��������ߤʤ顢���̾�ˤϤʤˤ�ɽ�����ʤ���
	if(	$trslt=='_sent'){ return ''; }
	if(	$trslt!='_ready'){
		// _ready�ǤϤʤ��ʤ�ʤ�餫�Υ��顼���񤤤Ƥ��롣���Τޤ�ɽ����
		 return $trslt;
	}

	//�ʲ� _ready==�������Ƥ��ʤ����.�֤��Υܥ���򲡤�����������׻ݤ�ɽ����	$rslt = "";
	$rslt .= $_discordpost_mes['premessage'].$ttext."";
	$btntxt = $_discordpost_mes['btn_send'];
	if ( $trslt === '_ready' ) {
		// �ȥ�å��Хå��������뤿��Υܥ��������ߡ�

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

	//�Խ����¤��ʤ���С�¨�����ȡ�
	if (!check_editable($vars['refer'])) return;

	//�������麣���webhook�����ꤹ�롣
	$tmp=$vars['webhookid'];
	//�����¦���ɤ��Ǥ���Τ�����Ū�ˤϤ��ꤨ�ʤ���������Х�������
	//�Τ�ʤ�ID���ꤷ�Ƥ����饨�顼��
	if ($tmp=='') { $tmp='default'; }
	if(!array_key_exists($tmp,$_discordpost_webhookid)) return; //�б�������Ͽ�ʤ���

	$tmp= explode('|',$_discordpost_webhookid[$tmp]);
		// $tmp[0] : user̾
		// $tmp[1] : webhook url

	//�����¦���ɤ��Ǥ���Τ�����Ū�ˤϤ��ꤨ�ʤ���������Х�������
	// ���ꤵ�줿webhook̾�����ߤΥ桼��̾���б����Ƥ��ʤ���С���Ϥꥢ���ȡ�
	$curusr = get_auth_user();
	if($curusr=='') return; 
	if($curusr!=$tmp[0]) return;

	
	$body ="";
	$title = "";
	$matches = array();
	$discordpost_ct = 0;
	$discordpost_no = $vars['discordpost_no'];


	// ������
	$rsend = sendToDiscord($tmp[1]);
	//
	$msg= "&discordpost(".$vars['webhookid'].",".$rsend.")";	

	// ������̤��������ڡ�����ȿ�Ǥ��롣
	// �����θ��ڡ����Υǡ�������Ф���
	$postdata_old  = get_source($vars['refer']);

	// �����ˤ���ֺ�����оݡפ�õ���Ф���
	// ������ä��ѥ�᡼�������$discordpost_no���ܡפΥǡ����ʤΤϤ狼�äƤ��롣

	$skipflag = 0;
	foreach ($postdata_old as $line)
	{
		if ( $skipflag || substr($line,0,1) == ' ' || substr($line,0,2) == '//' ){
			// �����ȹԡ�PRE�ԡ�����ӡ֥����åפ���׾��Ǥϥ����åס�
			$postdata .= $line;
			continue;
		}
		$ct = preg_match_all('/&discordpost(\([^(){};]*\))/',$line, $out);
		if ( $ct ){
		// �����ܤ��Ƥ���Ԥ� $discordpost �����뤳�Ȥ�ʬ���ä��Τǡ������å����Ƥ�����
		for($i=0; $i < $ct; $i++){
		    if ($discordpost_ct++ == $discordpost_no ){
			// �ޤ��˺����Ƥ���Τ��񤭴����оݡ��֤������롣
			$line = preg_replace('/&discordpost(\([^(){};]*\))?/',$msg,$line,1);
			$skipflag = 1; //�ִ������Τǡ��ʹߤϤ��٤ƥ����åפ��롣
			break;
		    }
		    else {
			// ������Ԥ�ʣ��&discordpost�����äơ��ޤ����ɤ��夤�Ƥ��ʤ���
			// �ǽ�Τۤ��˶ᤤ��Σ��Ĥ� __discordpost__�˽񤭴��������򡢼��ء�
			$line = preg_replace('/&discordpost(\([^(){};]*\))?;/','&___discordpost$1___;',$line,1);
		    }
		}
		// ��������ä��Τǡ����򤷤Ƥ��� __discordpost__�򸵤��᤹��
		$line = preg_replace('/&___discordpost(\([^(){};]*\))?___;/','&discordpost$1;',$line);
	    }
	    $postdata .= $line;
	}

	// �����ޤǤǡ�������postdata ���Ǥ��Ƥ��롣

	// �Խ�����Ĵ��

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

		//���Υ᥻�å�������
		//�桼����ID�ǥ�󥷥�󤷤������ϡ�<@discord_user_id>������
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
