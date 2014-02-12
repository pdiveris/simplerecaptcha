<?php

namespace Bentleysoft\Simplerecaptcha;

use Config, Input, Request;

/**
 *
 * Laravel 4 Simplerecaptcha package
 * @version 0.0.1
 * @copyright Copyright (c) 2014 Petros Diveris
 * @author Petros Diveris (fork)
 * @author Israel Parra (original)
 * @contact petros@diveris.org
 * @link http://www.diveris.org
 * @date 2013-03-27
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */

class Simplerecaptcha
{

	private $recaptcha_api_server;
	private $recaptcha_api_secure_server;
	private $recaptcha_verify_server;
	private $server;
	private $errorpart;
	public $is_valid;
	public $error;

	/**
	 * Set some variables needed
	 */
	public function __construct()
	{
		$this->recaptcha_api_server = "http://www.google.com/recaptcha/api";
		$this->recaptcha_api_secure_server = "https://www.google.com/recaptcha/api";
		$this->recaptcha_verify_server = "www.google.com";
	}

	/**
	 * Encodes the given data into a query string format
	 * @access private
	 * @param $data - array of string elements to be encoded
	 * @return string - encoded request
	 */
	private function _recaptcha_qsencode($data) 
	{
	    $req = "";
	    foreach ( $data as $key => $value )
	        $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

	    // Cut the last '&'
	    $req=substr($req,0,strlen($req)-1);
	    return $req;
	}

	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 * @access private
	 * @param string $host
	 * @param string $path
	 * @param array $data
	 * @param int $port
	 * @return array $response
	 */
	private function _recaptcha_http_post($host, $path, $data, $port = 80) {

	    $req = $this->_recaptcha_qsencode ($data);

	    $http_request  = "POST $path HTTP/1.0\r\n";
	    $http_request .= "Host: $host\r\n";
	    $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
	    $http_request .= "Content-Length: " . strlen($req) . "\r\n";
	    $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
	    $http_request .= "\r\n";
	    $http_request .= $req;

	    $response = '';
	    if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
	        die ('Could not open socket');
	    }

	    fwrite($fs, $http_request);

	    while (!feof($fs))
	        $response .= fgets($fs, 1160); // One TCP-IP packet
	        fclose($fs);
	        $response = explode("\r\n\r\n", $response, 2);

	        return $response;
	}


	/**
	 * Gets the challenge HTML (javascript and non-javascript version).
	 * This is called from the browser, and the resulting reCAPTCHA HTML widget
	 * is embedded within the HTML form it was called from.
	 * @access public
	 * @param string $pubkey A public key for reCAPTCHA
	 * @param string $error The error given by reCAPTCHA (optional, default is null)
	 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
	 * @return string - The HTML to be embedded in the user's form.
	 */
	public function recaptcha_get_html($error = null, $use_ssl = false)
	{
		if (Config::get('simplerecaptcha::public_key') == null || Config::get('simplerecaptcha::public_key') == '') {
			die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
		}
		
		if ($use_ssl) {
	                $this->server = $this->recaptcha_api_secure_server;
	        } else {
	                $this->server = $this->recaptcha_api_server;
	        }

	        $errorpart = "";
	        if ($error) {
	           $this->errorpart = "&amp;error=" . $error;
	        }

	        $js = '<script type="text/javascript">';
	        $js .= 'var RecaptchaOptions = {';
	        $js .= 'theme: "'.Config::get('simplerecaptcha::theme').'",';
	        $js .= 'custom_translations : { ';
	        $js .= 'instructions_visual : "'.Config::get('simplerecaptcha::textfield').'",';
	        $js .= 'instructions_audio : "'.Config::get('simplerecaptcha::soundfield').'",';  
	        $js .= 'play_again : "'.Config::get('simplerecaptcha::play_again').'",';
            $js .= 'visual_challenge : "'.Config::get('simplerecaptcha::visual_challenge').'",';
            $js .= 'audio_challenge : "'.Config::get('simplerecaptcha::audio_challenge').'",';
            $js .= 'refresh_btn : "'.Config::get('simplerecaptcha::refresh_btn').'",';
            $js .= 'help_btn : "'.Config::get('simplerecaptcha::help_btn').'",';
            $js .= 'incorrect_try_again : "'.Config::get('simplerecaptcha::incorrect_try_again').'"'; 
	        $js .= '},';
	        $js .= '};';
	        $js .= '</script>';
	        $js .= ' <script type="text/javascript" src="'. $this->server . '/challenge?k=' . Config::get('simplerecaptcha::public_key') . $this->errorpart . '"></script>';
	        $js .= '<noscript>';
	        $js .= '<iframe src="'. $this->server . '/noscript?k=' . Config::get('simplerecaptcha::public_key') . $this->errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>';
	        $js .= '<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>';
	        $js .= '<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>';
	        $js .= '</noscript>';
	        
	        return $js;
	}
   

	/**
	* Calls an HTTP POST function to verify if the user's guess was correct
	* @access public  
	* @param string $remoteip
	* @param string $challenge
	* @param string $response
	* @return ReCaptchaResponse 
	**/
	public function recaptcha_check_answer($remoteip, $challenge, $response)
	{
		if (Config::get('simplerecaptcha::private_key') == null || Config::get('simplerecaptcha::private_key') == '') {
			die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
		}

		if ($remoteip == null || $remoteip == '') {
			die ("For security reasons, you must pass the remote ip to reCAPTCHA");
		}
	
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
                $this->is_valid = false;
                $this->error = 'incorrect-captcha-sol';
                return $this;
        }

        $response = $this->_recaptcha_http_post(
        					$this->recaptcha_verify_server, "/recaptcha/api/verify",
                            array(
                                'privatekey' 	=> Config::get('simplerecaptcha::private_key'),
                                'remoteip' 		=> $remoteip,
                                'challenge' 	=> $challenge,
                                'response' 		=> $response,
                            )
                        );

        $answers = explode ("\n", $response [1]);

        if (trim ($answers [0]) == 'true') {
                $this->is_valid = true;
        }
        else {
                $this->is_valid = false;
                $this->error = $answers [1];
        }
        return $this;

	}

	/**
	* Check if captcha is correct
	* @access public  
	* @param  string   	$recaptcha
	* @return  boolean 
	**/
	public function check($recaptcha)
	{
        $resp = $this->recaptcha_check_answer(
            Request::getClientIp(),
            Input::get("recaptcha_challenge_field"),
            Input::get("recaptcha_response_field")
        );
        
	    if(!$resp->is_valid)
	    {
	     	return false;
	    }

	    return true;            
	}

}