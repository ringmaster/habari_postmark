<?php

class PostmarkPlugin extends Plugin
{
	/**
	 * Simple plugin configuration
	 * @return FormUI The configuration form
	 **/
	public function configure() {
		$form = new FormUI( 'postmark' );
		$form->append( new FormControlText('apikey', 'postmark__apikey', 'API Key'));
		$form->append( new FormControlSubmit('save', _t( 'Save' )));
		return $form;
	}

	public function filter_send_mail($handled, $mail) {
		if(!$handled) {

			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'X-Postmark-Server-Token: ' . Options::get('postmark__apikey'),
			);

			$data = array(
				'To' => $mail['to'],
				'subject' => $mail['subject'],
				'TextBody' => $mail['message'],
				'From' => $mail['headers']['From'],
				//'HtmlBody' => '<html><body>' . $mail['message'] . '</body></html>',
			);

			$rr = new RemoteRequest('http://api.postmarkapp.com/email', 'POST');
			$rr->set_body(json_encode($data));
			$rr->add_headers($headers);
			try {
				$rr->execute();
				EventLog::log(_t('Send message to %s via Postmark', array($mail['to'])), 'info', 'default', null, array($data, $headers));
				Session::notice(var_export($rr->get_response_headers(),1));
			}
			catch(Exception $e) {
				EventLog::log(_t('Failed to send message to %s via Postmark', array($mail['to'])), 'err', 'default', null, array($e->getMessage(), $data, $headers));
				Session::error('There was a problem sending your message.  Please contact the site administrators directly.');
			}
		}

		return true;
	}

}
?>