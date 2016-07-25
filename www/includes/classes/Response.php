<?php

	declare(strict_types=1);

	class Response {
		static function Fail(string $message = '', $data = array()){
			if (empty($message)){
				global $signedIn;

				$message = $signedIn ? 'Insufficient permissions.' : '<p>You are not signed in (or your session expired).</p><p class="align-center"><button class="typcn green da-login" id="turbo-sign-in" data-url="'.OAUTH_AUTHORIZATION_URL.'">Sign back in</button></p>';
			}

			self::_respond(false, $message, $data);
		}
		static function DBError(string $message = ''){
			global $Database;

			$message .= rtrim(': Error while saving to database: '.$Database->getLastError(), ': ');

			self::_respond(false, $message, array());
		}

		static function Success(string $message, $data = array()){
			self::_respond(true, $message, $data);
		}
		static function Done(array $data = array()){
			self::_respond(true, '', $data);
		}

		static private function _respond(bool $status, string $message, $data){
			header('Content-Type: application/json');
			$response = array('status' => $status);
			if (!empty($message))
				$response['message'] = $message;
			if (!empty($data) && is_array($data))
				$response = array_merge($data, $response);
			echo JSON::Encode($response);
			exit;
		}
	}
