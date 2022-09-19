<?php


namespace GigyaHelper\Report;


use GigyaHelper\Application;

class Duplicates extends Application {
	public function run() {
		$ex = null;
		$user_mapping = [];

		$next_cursor = false;
		while (true) {
			try {
				$accounts = $this->cursor_query('accounts.search', 'SELECT * FROM accounts LIMIT 5000', $next_cursor);
			} catch (\Exception $e) {
				$ex = $e;
				break;
			}

			foreach ( $accounts->results as $account ) {
				$mails = array_merge($account->emails->verified, $account->emails->unverified);
				foreach ($mails as $mail) {
					if ( !isset($user_mapping[$mail])) {
						$user_mapping[$mail] = [];
					}
					$user_mapping[$mail][] = $account->UID;
				}
			}
			if ( empty($accounts->nextCursorId)) {
				break;
			}

			$next_cursor = $accounts->nextCursorId;
		}

		foreach ( $user_mapping as $key => $user ) {
			if (count($user) === 1) {
				unset($user_mapping[$key]);
			}
		}
		$this->result = $user_mapping;
		if ($ex) {
			echo "Error: " . $ex->getMessage() . "(#" . $ex->getCode() . ") \n";
		}
	}
}