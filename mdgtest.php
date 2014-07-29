<?php ob_start();
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/2/14
 * Time: 2:17 PM
 */

header("Content-Type: text/html");
ini_set('display_errors', true);

require_once 'app/Mage.php';
Mage::setIsDeveloperMode(true);
$app = Mage::app();
$script = $_SERVER['SCRIPT_NAME'];

$f = $app->getRequest()->getParam('f');
if (empty($f)) {
	$pi = $app->getRequest()->getPathInfo();
	preg_match('#^/f/([^/]+)$#', $pi, $match);
	if (count($match) > 0) {
		$f = $match[1];
	}
}
$allowedFunctions = array(
	'xmlRpcTesting' => 'XML-RPC Testing',
	'soapV1Testing' => 'Soap Version 1 Testing',
	'soapV2Testing' => 'Soap Version 2 Testing',
	'getRestSessionToken' => 'get rest session token',
	'oAuthCallback' => 'hidden',
);
$html = new HtmlOutputter();
$html->startHtml()->startBody();

$html->para("<a href=\"$script\">home</a>");

if (isset($f) && array_key_exists($f, $allowedFunctions)) {
	try {
		$html->para("Running tests for <b>{$allowedFunctions[$f]}</b>");
		call_user_func($f);

	} catch (Exception $e) {
		$html->para("failed to run function $f");
		$html->para($e->getMessage());
		$html->pre($e->getTraceAsString());
	}
} else {
	$html->para("Available Protocols:");
	showAllowedFunctions();
	exit;
}


function oAuthCallback() {
	global $html;
	global $app;
	$param = $app->getRequest()->getParam('oauth_token');
	$html->para('got oauth_token: ' . $param);
	$param = $app->getRequest()->getParam('oauth_verifier');
	$html->para('got oauth_verifier: ' . $param);
	getRestSessionToken();

}

function getRestSessionToken() {
	global $html;
	$callbackUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '/f/oAuthCallback';
	$temporaryCredentialsRequestUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/oauth/initiate?oauth_callback=' . urlencode($callbackUrl);
	$adminAuthorizationUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/admin/oauth_authorize';
	$accessTokenRequestUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/oauth/token';
	$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/api/rest';
	$consumerKey = 'nsdzw5xdw5gamn877kr3l3kkizq4ikbw';
	$consumerSecret = 'nr0wd0kxbtade23ekmw031f9icl27nl1';

	session_start();
	if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
		$_SESSION['state'] = 0;
	}
	try {

		$authType = (isset($_SESSION['state']) && $_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
		$oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
		$oauthClient->enableDebug();

		if (!isset($_GET['oauth_token']) && !isset($_SESSION['state'])) {
			$html->para("here");
			$requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
			$_SESSION['secret'] = $requestToken['oauth_token_secret'];
			$_SESSION['state'] = 1;
			header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
			exit;
		} else if ($_SESSION['state'] == 1) {
			$oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
			$accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
			$_SESSION['state'] = 2;
			$_SESSION['token'] = $accessToken['oauth_token'];
			$_SESSION['secret'] = $accessToken['oauth_token_secret'];
			header('Location: ' . $callbackUrl);
			exit;
		} else {
			$oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
			$resourceUrl = "$apiUrl/products";
			$productData = json_encode(array(
				'type_id' => 'simple',
				'attribute_set_id' => 4,
				'sku' => 'simple' . uniqid(),
				'weight' => 1,
				'status' => 1,
				'visibility' => 4,
				'name' => 'Simple Product',
				'description' => 'Simple Description',
				'short_description' => 'Simple Short Description',
				'price' => 99.95,
				'tax_class_id' => 0,
			));
			$headers = array('Content-Type' => 'application/json');
			$oauthClient->fetch($resourceUrl, $productData, OAUTH_HTTP_METHOD_POST, $headers);
			print_r($oauthClient->getLastResponseInfo());
		}
	} catch (OAuthException $e) {
		print_r($e);
	}
}

function soapV2Testing() {
	global $html;
	$apiUser = 'magentoapi';
	$apiKey = 'magentoapi';
	$client = new SoapClient('http://' . $_SERVER['SERVER_NAME'] . '/magento/api/v2_soap/?wsdl');
	$sessionId = $client->login($apiUser, $apiKey);

	$html->para('found soap session id: ' . $sessionId);
}

function soapV1Testing() {
	global $html;
	global $script;

	$sessionId = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;
	$api = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/api/soap/';
	$apiUser = 'magentoapi';
	$apiKey = 'magentoapi';
	$html->para("Using API: <b>$api</b>\n");

	$client = new SoapClient($api . '?wsdl');
	if(!isset($sessionId)) {
		$sessionId = $client->login($apiUser, $apiKey);
	}
	$html->para("Using Session Id: <b>$sessionId</b>");

	$actions = array(
		'soapV1CatalogProductList'
	);
	$html->para("Available Tests:");
	$html->startList();
	foreach ($actions as $action) {
		$html->listItem("<a href=\"$script/f/soapV1Testing?t={$sessionId}&a={$action}\">" . $action . '</a>');
	}
	$html->endList();
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
	if (isset($a) && in_array($a, $actions)) {
		// execute action...
		call_user_func($a, $client, $sessionId);
	}
}

function xmlRpcTesting() {
	global $html;
	global $script;
	$sessionId = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;
	$api = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/api/xmlrpc/';
	$apiUser = 'magentoapi';
	$apiKey = 'magentoapi';
	$html->para("Using API: <b>$api</b>\n");

	$client = new Zend_XmlRpc_Client($api);
	if (!isset($sessionId)) {
		$sessionId = $client->call('login', array($apiUser, $apiKey));
	}
	$html->para("Using Session Id: <b>$sessionId</b>");
	$actions = array(
		'xmlCatalogProductList'
	);
	$html->para("Available Tests:");
	$html->startList();
	foreach ($actions as $action) {
		$html->listItem("<a href=\"$script/f/xmlRpcTesting?t={$sessionId}&a={$action}\">" . $action . '</a>');
	}
	$html->endList();
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
	if (isset($a) && in_array($a, $actions)) {
		// execute action...
		call_user_func($a, $client, $sessionId);
	}

}

function soapV1CatalogProductList($client, $sessionId) {
	$res = $client->call($sessionId, 'catalog_product.list');
	global $html;
	$html->pre(print_r($res, true));
}

function xmlCatalogProductList($client, $sessionId) {
	$res = $client->call('call', array($sessionId, 'catalog_product.list'));
	global $html;
	$html->pre(print_r($res, true));
}


function showAllowedFunctions() {
	global $html;
	global $allowedFunctions;
	global $script;
	foreach ($allowedFunctions as $func => $label) {
		 if ($label == 'hidden')
		 	continue;
		$html->para("<a href=\"$script?f=$func\">" . $label . '</a>');
	}
}



class HtmlOutputter {
	public function __construct() {

	}

	public function startHtml() {
		echo "<html>\n";
		return $this;
	}

	public function startHead() {
		echo "<head>\n";
		return $this;
	}

	public function endHead() {
		echo "</head>\n";
		return $this;
	}

	public function startBody() {
		echo "<body>\n";
		return $this;
	}

	public function endBody() {
		echo "</body>\n";
		return $this;
	}

	public function endHtml() {
		echo "</html>\n";
		return $this;
	}

	public function para($content) {
		echo '<p>', $content, "</p>\n";
		return $this;
	}

	public function pre($content) {
		echo '<pre>', print_r($content, true), "</pre>\n";
		return $this;
	}

	public function code($content) {
		echo '<code>', $content, "</code>\n";
		return $this;
	}

	public function startList() {
		echo '<ul>', "\n";
		return $this;
	}

	public function endList() {
		echo "</ul>\n";
		return $this;
	}

	public function listItem($content) {
		echo '<li>' . $content . "</li>\n";
		return $this;
	}
}


