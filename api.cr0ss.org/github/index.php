<?php
require "../vendor/autoload.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../..');
$dotenv->load();

set_error_handler(
	function($severity, $message, $file, $line) {
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
);

set_exception_handler(
	function($e) {
		header('HTTP/1.1 500 Internal Server Error');
		echo "Error on line {$e->getLine()}: " . htmlSpecialChars($e->getMessage());
		die();
	}
);

$rawPost = NULL;
if ($_ENV['cr0ssGithubHookSecret'] !== NULL) {
	if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
		throw new \Exception("'X-Hub-Signature' header missing.");
	} elseif (!extension_loaded('hash')) {
		throw new \Exception("'Hash' not supported.");
	}

	list($algorithm, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
	if (!in_array($algorithm, hash_algos(), TRUE)) {
		throw new \Exception("Hash algorithm '$algorithm' is not supported.");
	}

	$rawPost = file_get_contents('php://input');
	if (!hash_equals($hash, hash_hmac($algorithm, $rawPost, $_ENV['cr0ssGithubHookSecret']))) {
		throw new \Exception('Secret does not match.');
	}
};

if (!isset($_SERVER['CONTENT_TYPE'])) {
	throw new \Exception("'Content-Type' header missing.");
} elseif (!isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
	throw new \Exception("'X-Github-Event' header missing.");
}

switch ($_SERVER['CONTENT_TYPE']) {
	case 'application/json':
		$json = $rawPost ?: file_get_contents('php://input');
		break;

	case 'application/x-www-form-urlencoded':
		$json = $_POST['payload'];
		break;

	default:
		throw new \Exception("Unsupported content type: $_SERVER[CONTENT_TYPE]");
}

// Load the significant payload from the JSON object.
$payload = json_decode($json);

switch (strtolower($_SERVER['HTTP_X_GITHUB_EVENT'])) {
	case 'release':
		header('HTTP/1.0 200 OK');
			echo "Message sent to space.";
		if ($payload->action == 'released') {
			shell_exec('../../scripts/deploy.sh');
		}
		die();
	default:
		header('HTTP/1.0 404 Not Found');
		echo "Event:$_SERVER[HTTP_X_GITHUB_EVENT] Payload:\n";
		print_r($payload);
		die();
}
