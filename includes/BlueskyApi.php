<?php

// namespace cjrasmussen\BlueskyApi;

// use JsonException;
// use RuntimeException;

/**
 * Class for interacting with the Bluesky API/AT protocol
 */
class BlueskyApi {
	private ?string $accountDid = null;
	private ?string $apiKey = null;
	private ?string $refreshToken = null;
	private string $apiUri;

	public function __construct(string $api_uri = 'https://bsky.social/xrpc/') {
		$this->apiUri = $api_uri;
	}

	/**
	 * Authorize a user
	 *
	 * If handle and password are provided, a new session will be created. If a refresh token is provided, the session
	 * will be refreshed.
	 *
	 * @param string $handleOrToken
	 * @param string|null $app_password
	 * @return void
	 * @throws RuntimeException|JsonException
	 */
	public function auth(string $handleOrToken, ?string $app_password = null): void {
		if (($handleOrToken) && ($app_password)) {
			$data = $this->startNewSession($handleOrToken, $app_password);
		} else {
			$data = $this->refreshSession($handleOrToken);
		}

		$this->accountDid = $data->did;
		$this->apiKey = $data->accessJwt;
		$this->refreshToken = $data->refreshJwt;
	}

	/**
	 * Get the current account DID
	 *
	 * @return string
	 */
	public function getAccountDid(): ?string {
		return $this->accountDid;
	}

	/**
	 * Get the refresh token
	 *
	 * @return string
	 */
	public function getRefreshToken(): ?string {
		return $this->refreshToken;
	}

	/**
	 * Make a request to the Bluesky API
	 *
	 * @param string $type
	 * @param string $request
	 * @param array $args
	 * @param string|null $body
	 * @param string|null $content_type
	 * @return object
	 * @throws JsonException
	 */
	public function request(string $type, string $request, array $args = [], ?string $body = null, string $content_type = null): object {
		$url = $this->apiUri . $request;

		if (($type === 'GET') && (count($args))) {
			$url .= '?' . http_build_query($args);
		} elseif (($type === 'POST') && (!$content_type)) {
			$content_type = 'application/json';
		}

		$headers = [];
		if ($this->apiKey) {
			$headers[] = 'Authorization: Bearer ' .$this->apiKey;
		}

		if ($content_type) {
			$headers[] = 'Content-Type: ' . $content_type;

			if (($content_type === 'application/json') && (count($args))) {
				$body = json_encode($args, JSON_THROW_ON_ERROR);
				$args = [];
			}
		}

		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);

		if (count($headers)) {
			curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		}

		switch ($type) {
			case 'POST':
				curl_setopt($c, CURLOPT_POST, 1);
				break;
			case 'GET':
				curl_setopt($c, CURLOPT_HTTPGET, 1);
				break;
			default:
				curl_setopt($c, CURLOPT_CUSTOMREQUEST, $type);
		}

		if ($body) {
			curl_setopt($c, CURLOPT_POSTFIELDS, $body);
		} elseif (($type !== 'GET') && (count($args))) {
			curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($args, JSON_THROW_ON_ERROR));
		} elseif ($type === 'POST') {
			curl_setopt($c, CURLOPT_POSTFIELDS, null);
		}

		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_VERBOSE, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 1);

		$data = curl_exec($c);
		curl_close($c);

		return json_decode($data, false, 512, JSON_THROW_ON_ERROR);
	}

	/**
	 * Start a new user session using handle and app password
	 *
	 * @param string $handle
	 * @param string $app_password
	 * @return object
	 * @throws RuntimeException|JsonException
	 */
	private function startNewSession(string $handle, string $app_password): object {
		$args = [
			'identifier' => $handle,
			'password' => $app_password,
		];
		$data = $this->request('POST', 'com.atproto.server.createSession', $args);

		if (!empty($data->error)) {
			throw new RuntimeException($data->message);
		}

		return $data;
	}

	/**
	 * Refresh a user session using an API key
	 *
	 * @param string $api_key
	 * @return object
	 * @throws RuntimeException|JsonException
	 */
	private function refreshSession(string $api_key): object {
		$this->apiKey = $api_key;
		$data = $this->request('POST', 'com.atproto.server.refreshSession');
		$this->apiKey = null;

		if (!empty($data->error)) {
			throw new RuntimeException($data->message);
		}

		return $data;
	}
}