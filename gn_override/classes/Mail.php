<?php
class Mail extends MailCore
{
	public static function Send($id_lang, $template, $subject, $templateVars, $to, $toName = null, $from = null, $fromName = null, $fileAttachment = null, $modeSMTP = null, $templatePath = _PS_MAIL_DIR_, $die = false, $id_shop = null) {
		if (Module::isInstalled('germanext')) {
                	require_once(_PS_MODULE_DIR_ . 'germanext/germanext.php');
            
			$params = array(
				'id_lang'        => $id_lang,
				'template'       => $template,
				'subject'        => $subject,
				'templateVars'   => $templateVars,
				'to'             => $to,
				'toName'         => $toName,
				'from'           => $from,
				'fromName'       => $fromName,
				'fileAttachment' => $fileAttachment,
				'modeSMTP'       => $modeSMTP,
				'templatePath'   => $templatePath,
				'die'            => $die
			);
            
			Germanext::prepareMailSend($params);

			return parent::Send(
				$params['id_lang'],
				$params['template'],
				$params['subject'],
				$params['templateVars'],
				$params['to'],
				$params['toName'],
				$params['from'],
				$params['fromName'],
				$params['fileAttachment'],
				$params['modeSMTP'],
				$params['templatePath'],
				$params['die']
			);
		}
		
		return parent::Send($id_lang, $template, $subject, $templateVars, $to, $toName, $from, $fromName, $fileAttachment, $modeSMTP, $templatePath, $die, $id_shop);
	}
}
