<?php

namespace WPD\Recaptcha\Forms;

class RetrievePassword extends LostPassword {

	/**
	 * @return string
	 */
	public function validation_action(): string {
		return 'login_form_retrievepassword';
	}
}
