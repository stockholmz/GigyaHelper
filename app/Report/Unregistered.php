<?php


namespace GigyaHelper\Report;


use GigyaHelper\Application;

class Unregistered extends Application {
	
	public function run($reg_source = '') {
		if (empty($reg_source)) {
			throw new \Exception('reg_source is required');
		}
		
		$query = "
			SELECT * FROM accounts
			WHERE 
				isRegistered = false
				AND regSource = regex('.*" . $reg_source . "*')
			LIMIT 5000
		";

		$next_cursor = false;
		while (true) {
			try {
				$accounts = $this->cursor_query('accounts.search', $query, $next_cursor);
			} catch (\Exception $e) {
				break;
			}

			foreach ( $accounts->results as $item ) {
				$email = false;
				$email = $item->profile->email ?: $email;
				$email = $item->emails->unverified[0] ?: $email;
				$email = $item->identities[0]->email ?: $email;
				$email = $item->emails->verified[0] ?: $email;

				$this->result[$email] = [
					'email' => $email,
					'uid' => $item->UID,
					'regSource' => $item->regSource,
					'created' => $item->created
				];
			}

			if ( empty($accounts->nextCursorId)) {
				break;
			}

			$next_cursor = $accounts->nextCursorId;
		}
	}
}