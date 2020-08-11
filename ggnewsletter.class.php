<?php
/**
 * @class ggnewsletter
 * @author GG
 * @brief gg newsletter widget
 * @version 0.1
 **/

class ggnewsletter extends WidgetHandler {
	function proc($args) {
		$logged_info = Context::get('logged_info');

		if(Context::get('ggstatus') == 'newsletter_insert' && Context::get('email_address')) {

			$obj->ggmailing_email = Context::get('email_address');
			$obj->ggmailing_module_srl = $args->module_srl;
			$output = executeQueryArray('ggmailing.getBoardMember',$obj); // 중복 이메일 체크

			if($output->data) {
				$args->is_Member = 'A';
				$returnUrl = getNotEncodedUrl('', 'mid', Context::get('mid'), 'document_srl', Context::get('document_srl'));
				echo '<script>alert("이미 가입이 되어 있는 이메일입니다.");location.href="'.$returnUrl.'";</script>';
			}
			else $args->is_Member = 'N';

			$args->ggmailing_member_srl = $logged_info->member_srl ? $logged_info->member_srl : ''; //비회원도 가능 
			$args->ggmailing_nickname = $logged_info->nick_name ? $logged_info->nick_name : '뉴스레터';
			$args->ggmailing_email = $logged_info->email_address ? $logged_info->email_address : Context::get('email_address');
			$args->ggmailing_member_regdate = $logged_info->regdate ? $logged_info->regdate : date('YmdHis');
			
			$args->ggmailing_module_srl = $args->module_srl;// 선택한 게시판 모듈
			$oModuleModel = getModel('module');
			$ggmodule_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
			$args->ggmailing_mid = $ggmodule_info->mid;
			$args->regdate = date('YmdHis');

			if($args->is_Member == 'N') executeQuery('ggmailing.insertGgmailingBoardMember',$args);

			$args->is_Member = 'A';
			$returnUrl = getNotEncodedUrl('', 'mid', Context::get('mid'), 'document_srl', Context::get('document_srl'));
			echo '<script>alert("뉴스레터 가입이 완료되었습니다.");location.href="'.$returnUrl.'";</script>';
		}
		//위젯 옵션 설정
		if(!$args->btn_name) $args->btn_name = '뉴스레터 가입';

		Context::set('widget_info', $args);

		$tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
		$tpl_file = 'newsletter';
		$oTemplate = &TemplateHandler::getInstance();
		return $oTemplate->compile($tpl_path, $tpl_file);
	}
}
?>
