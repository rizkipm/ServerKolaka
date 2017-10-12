<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Build to support email system.
 *
 * @author pulung
 */

if ( ! function_exists('send_email'))
{
	/**
	 * Modified send email from native helper.
	 * @param string $recipient recipient email address.
	 * @param string $subject email subject.
	 * @param string $message email message.
	 * @return boolean successful or failed attempt to send email.
	 */
	function send_email($recipient, $subject, $message) {
		//get an instance of CI so we can access our configuration
		$CI =& get_instance();

		#pre("lewat sini");
		
		//$CI->load->library('email');
		
		// $config_email = array();
		// $config_email['protocol'] = 'smtp';
		// $config_email['mailtype'] = 'html';
		// $config_email['priority'] = '1';
		// $config_email['wordwrap'] = FALSE;
  //       $config_email['smtp_host'] = 'ssl://smtp.mail.yahoo.com';
  //       $config_email['smtp_port'] = 465;
  //       $config_email['smtp_user'] = 'maragaapp@yahoo.com';
  //       $config_email['smtp_pass'] = 'Bismillah123';
		// $config_email['charset'] = 'utf-8';


		
		// $CI->load->library('email', $config_email);
		// $CI->email->set_newline("\r\n");
  //       $CI->email->set_crlf( "\r\n" );
		
		// $CI->email->from($config_email['smtp_user'], "Maraga Startup Incubator");


		$config = Array(
				'protocol' => 'smtp',
				'smtp_host' => 'mail.transfood.web.id',
				'smtp_port' => 26,
				'smtp_user' => 'possible@transfood.web.id',
				'smtp_pass' => 'bismillah123',
				'mailtype' => 'html',
				'charset' => 'iso-8859-1',
				'newline' => "\r\n"
			 );
		$CI->load->library('email', $config);
		$CI->email->set_newline("\r\n");
		$CI->email->set_crlf( "\r\n" );


		$CI->email->to($recipient);
		$CI->email->from('possible@transfood.web.id', 'Transfood');
		$CI->email->subject($subject);
		$CI->email->message($message);
	

		$hasil = $CI->email->send();
		#pre($CI->email->print_debugger());
		if (!$hasil) {

 			log_message("error", "Email is not sent.");
			return FALSE;

		} else {

			return TRUE;
		}
	}
}

if ( ! function_exists('save_to_email_spooler'))
{
	/**
	 * Save about-to-send-email to spooler to make them more 
	 * controllable in sending. (expected failure and success)
	 * @param string $recipient destination of email.
	 * @param string $subject subject of email.
	 * @param string $message content of email.
	 */
	function save_to_email_spooler($recipient, $subject, $message) {
		//get an instance of CI so we can access our configuration
		$CI =& get_instance();
		
		$email_spool_conf = array(
				"recipient" => $recipient, 
				"subject" => $subject, 
				"message" => $message,
				"status" => EMAIL_READY
		);
		
		$CI->generic_model->create("email_spool", $email_spool_conf);
	}
}

if ( ! function_exists('get_oldest_email'))
{
	/**
	 * Get oldest email in spooler.
	 * @return array email data.
	 */
	function get_oldest_email() {
		// get an instance of CI so we can access our configuration
		$CI =& get_instance();
		
		// retrieve email from spooler
		$email_data = $CI->generic_model->retrieve_one("email_spool", array("status" => EMAIL_READY));
		
		if (!$email_data) {
			return NULL;
		}
		
		// block any attempt to send the same email.
		$CI->generic_model->update_with_transaction(
				"email_spool", 
				array("status" => EMAIL_BEING_SENT), 
				array("id" => $email_data["id"])
		);
		
		return $email_data;
	}
}

if ( ! function_exists('revert_email_back_to_spooler'))
{
	/**
	 * Revert a failed-to-send email back to spooler.
	 * @param int $email_id email ID.
	 */
	function revert_email_back_to_spooler($email_id = -1) {
		// get an instance of CI so we can access our configuration
		$CI =& get_instance();
		
		// retrieve email from spooler
		$email_data = $CI->generic_model->retrieve_one("email_spool", array("id" => $email_id));
		
		if ($email_data) {
			
			$email_spool_conf = array(
					"recipient" => $email_data["recipient"],
					"subject" => $email_data["subject"],
					"message" => $email_data["message"],
					"status" => EMAIL_READY
			);
			
			$CI->generic_model->create("email_spool", $email_spool_conf);
			
			// block any attempt to send the same email.
			$CI->generic_model->delete(
					"email_spool",
					array("id" => $email_id)
			);
		}
	}
}

if ( ! function_exists('cleanup_sent_email'))
{
	/**
	 * Delete sent email from spooler.
	 * @param int $email_id email ID.
	 */
	function cleanup_sent_email($email_id = -1) {
		// get an instance of CI so we can access our configuration
		$CI =& get_instance();

		// block any attempt to send the same email.
		$CI->generic_model->delete(
				"email_spool",
				array("id" => $email_id)
		);
	}
}
