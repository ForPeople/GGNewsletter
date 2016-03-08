<?php
/**
 * @class ggboardmailing_widget
 * @author GG
 * @brief gg board mailing widget
 * @version 0.3
 **/

class ggboardmailing_widget extends WidgetHandler {
	function proc($args) {
		$logged_info = Context::get('logged_info');
		$module_info = Context::get('module_info');

		//로그인 전용
		if(!$logged_info) return;
		
		//게시판 목록 전용노출
		//if(Context::get('act') || Context::get('document_srl')) return;
		
		//현재 게시판에 메일링 가입되었는지 여부 판단
		$obj->ggmailing_member_srl = $logged_info->member_srl;
		$obj->ggmailing_module_srl = $module_info->module_srl;

		$output = executeQueryArray('ggmailing.getBoardMember',$obj);

		if($output->data) $args->is_Member = 'A';
		else $args->is_Member = 'N';

		if(Context::get('ggstatus') == 'insert' && $args->is_Member == 'N') {
			
			$args->ggmailing_member_srl = $logged_info->member_srl;
			$args->ggmailing_nickname = $logged_info->nick_name;
			$args->ggmailing_email = $logged_info->email_address;
			$args->ggmailing_member_regdate = $logged_info->regdate;
			
			$args->ggmailing_module_srl = $module_info->module_srl;
			$args->ggmailing_mid = $module_info->mid;
			//$args->ggmailing_document_srl = Context::get('document_srl');
			//$args->ggmailing_comment_srl = Context::get('comment_srl');
			$args->regdate = date('YmdHis');

			executeQuery('ggmailing.insertGgmailingBoardMember',$args);

			$args->is_Member = 'A';

			$gg = new stdClass();
			$gg->ggmailing_module_srl = $module_info->module_srl;
			$ggoutput = executeQueryArray('ggmailing.getBoardMemberCount',$gg);
			$args->is_Count = count($ggoutput->data);
			$returnUrl = getNotEncodedUrl('', 'mid', Context::get('mid'), 'document_srl', Context::get('document_srl'));
			echo '<script>alert("메일링 가입이 완료되었습니다.");location.href="'.$returnUrl.'";</script>';

		} elseif(Context::get('ggstatus') == 'delete' && $args->is_Member == 'A') {

			$args->ggmailing_member_srl = $logged_info->member_srl;
			$args->ggmailing_document_srl = $module_info->module_srl;
			$ggoutput = executeQueryArray('ggmailing.getBoardMember', $args);
			foreach($ggoutput->data as $key => $val) {
				if(!$val->ggmailing_document_srl) {
					$args->ggmailing_board_srl = $val->ggmailing_board_srl;
					executeQuery('ggmailing.deleteGgmailingBoardMember',$args);
				}
			}

			$args->is_Member = 'N';
			$returnUrl = getNotEncodedUrl('', 'mid', Context::get('mid'), 'document_srl', Context::get('document_srl'));
			echo '<script>alert("메일링 탈퇴가 완료되었습니다.");location.href="'.$returnUrl.'";</script>';
		}
		
		$gg = new stdClass();
		$gg->ggmailing_module_srl = $module_info->module_srl;
		$ggoutput = executeQueryArray('ggmailing.getBoardMemberCount',$gg);
		$args->is_Count = count($ggoutput->data);

		//위젯 옵션 설정
		if(!$args->before_btn_name) $args->before_btn_name = '메일링 가입';
		if(!$args->after_btn_name) $args->after_btn_name = '메일링 탈퇴';
		if(!$args->align) $args->align = 'left';

		// 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
		Context::set('colorset', $args->colorset);
		Context::set('widget_info', $args);

		$tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
		$tpl_file = 'list';
		$oTemplate = &TemplateHandler::getInstance();
		return $oTemplate->compile($tpl_path, $tpl_file);
	}
}
?>
