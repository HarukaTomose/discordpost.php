<?php

function plugin_discord_webhookinfo_init()
{

	$messages = array(
		'_discord_webhookinfo' => array(
			// webhook�o�^�B�����͎��̂Ƃ���B
			// '���ʖ�' => array( 
			//	'user'	=> '���p�ł��郆�[�U��',
			//	'url'	=> 'webhookurl',
			//	),
			//	���ʖ��F�C�ӕ�����B�v���O�C�������Ɏg��.
			//	���p�ł��郆�[�U���F pukiwiki�̃��[�U��
			//	webhook url�F�Ή�����webhook url
			// �Œ�ł��P�A���ʖ���'default'�̐ݒ肪�K�v�B

			// sample start-----
			'default'=> array(
				'user'	=> 'username',
				'url'	=> 'https://discord.com/api/webhooks/xxxxxxxxxxxxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
				),
			// ------- sample end

		),
	);

	set_plugin_messages($messages);

	//�@����ɂ��A�ȍ~�v���O�C���{�̑��ł͎��̂悤�ɂ��ē�����s��
	//
	// $tgtid == �v���O�C�������Ŏw�肵��ID�B
	// ��������Ƃɗv��Webhook���o�^����Ă��邩���`�F�b�N�B
	// if(!array_key_exists($tgtid,$_discordpost_webhookid)) {}
	//
	// ���܂̃��O�C�����[�U������Webhook���g���Ă�����
	// $curuser=get_auth_user();
	// if($_discord_webhookinfo[$tgtid][user]!=$curuser)
	//
	// �ȏ��OK�Ȃ�A
	//$_discord_webhookinfo[$tgtid][url] ���g���Ă悢 webhookURL.


}

?>

