<?php

namespace GigyaHelper;

use Gigya\PHP\GSKeyNotFoundException;
use Gigya\PHP\GSRequest;

abstract class Application {
	protected $result;
	
	private $cursor_num = 0; 

	public function __construct() {
	}

	protected function get_GSRequest(string $method): GSRequest {
		$request = new GSRequest(
			$_ENV['API_KEY'],
			$_ENV['SECRET_KEY'],
			$method,
			null,
			true,
			$_ENV['APP_KEY']
		);
		$request->setAPIDomain($_ENV['API_DOMAIN']);

		return $request;
	}

	/**
	 * Get Gigya user-object by email.
	 *
	 * @param GSRequest $request
	 * @param bool $next_cursor
	 * @return \stdClass
	 * @throws GSKeyNotFoundException
	 */
	protected function cursor_query(string $method, string $query, string $next_cursor = ''): \stdClass {
		$request = $this->get_GSRequest($method);
		$request->setParam('query', $query);
		
		if ($next_cursor) {
			if ($request->getParams()->getString('query', '') !== '') {
				$request->getParams()->remove('query');
			}
			$request->setParam('cursorId', $next_cursor);
		}
		else {
			$request->setParam('openCursor', 'true');
		}
		
		$response = $request->send();
		if ($response->getErrorCode() !== 0) {
			throw new \Exception($response->getErrorMessage(), $response->getErrorCode());
		}

		echo 'Page ' . ++$this->cursor_num . ' done.' . "\n";
		$result = json_decode($response->getResponseText());
		return $result ?: [];
	}

	/**
	 * @param string $query
	 * @param string $endpoint
	 * @param bool $as_cursor
	 * @return array
	 */
	public function simple_query(string $query, string $endpoint = 'accounts.search', bool $as_cursor = true): array {
		$result = [];
		try {

			if ($as_cursor) {
				$next_cursor = false;
				while (true) {
					$page = $this->cursor_query($endpoint, $query, $next_cursor);
					$result[] = $page;

					if (empty($page->nextCursorId)) {
						break;
					}
					$next_cursor = $page->nextCursorId;
				}
			} else {
				$request = $this->get_GSRequest($endpoint);
				$request->setParam('query', $query);
				$response = $request->send();

				if ($response->getErrorCode() !== 0) {
					var_dump($response);
					throw new \Exception($response->getErrorMessage(), $response->getErrorCode());
				}

				$result[] = json_decode($response->getResponseText());
			}
		} catch (\Exception $e) {
			die($e->getMessage() . '(#' . $e->getCode() . ')');
		}

		return $result;
	}
	
	abstract public function run();
	
	public function __destruct() {
		if (!empty($this->result)) {
			$class_name = get_class($this);
			$class_name = explode('\\', $class_name);
			$class_name = array_pop($class_name);
			
			$file_name = './reports/' . $class_name.'_'.date('Y-m-d_H-i').'.json';
			
			file_put_contents($file_name, json_encode($this->result));
			echo "$class_name report done. \n";
			echo "Dest: $file_name. \n";
		}
	}
}