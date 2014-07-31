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
	'restApiTesting' => 'REST API Testing',
);
$html = new HtmlOutputter();
$html->startHtml()->startBody();

$html->para("<a href=\"{$script}\">home</a>");

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

function restApiTesting() {
	error_log('restApiTesting called');
	global $html, $script;
	$callbackUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '/f/restApiTesting';
	$temporaryCredentialsRequestUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/oauth/initiate?oauth_callback=' . urlencode($callbackUrl);
	$adminAuthorizationUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/admin/oauth_authorize';
	$accessTokenRequestUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/oauth/token';
	$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/api/rest';
	$consumerKey = 'nsdzw5xdw5gamn877kr3l3kkizq4ikbw';
	$consumerSecret = 'nr0wd0kxbtade23ekmw031f9icl27nl1';

	session_start();
	if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
		$_SESSION['state'] = 0;
		//error_log('setting session state to zero');
	}
	try {

		$authType = (isset($_SESSION['state']) && $_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
		$oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
		$oauthClient->enableDebug();

		if (!isset($_GET['oauth_token']) && !isset($_SESSION['state'])) {
			//error_log('no oauth token and no session state');
			$requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
			$_SESSION['secret'] = $requestToken['oauth_token_secret'];
			$_SESSION['state'] = 1;
			header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
			exit;
		} else if ($_SESSION['state'] == 1) {
			//error_log('session state is 1');
			$oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
			$accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
			$_SESSION['state'] = 2;
			$_SESSION['token'] = $accessToken['oauth_token'];
			$_SESSION['secret'] = $accessToken['oauth_token_secret'];
			header('Location: ' . $callbackUrl);
			exit;
		} else {
			//error_log('session state: ' . $_SESSION['state']);
			/*
			 * ok, so this is the point where we can start interacting with
			 * the api. The token and secret are in the session, so lets list
			 * out the available actions and see if we can't get them running
			 */
			$html->para("Using API: <b>$apiUrl</b>\n");
			$html->para("Using token <b>{$_SESSION['token']}</b>, secret <b>{$_SESSION['secret']}</b>");

			$oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
			$actions = array(
				'restCatalogProductList'
			);
			$html->para("Available Tests:");
			$html->startList();
			foreach ($actions as $action) {
				$html->listItem("<a href=\"{$script}/f/restApiTesting?a={$action}\">" . $action . '</a>');
			}
			$html->endList();
			$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
			if (isset($a) && in_array($a, $actions)) {
				call_user_func($a, $apiUrl, $oauthClient);
			}
		}
	} catch (OAuthException $e) {
		$html->pre(print_r($e, true));
	}
}

function restCatalogProductList($apiUrl, $oauthClient) {
	global $html;
	$resourceUrl = "$apiUrl/products";
	$query = http_build_query(array('filter' => array(array('attribute' => 'color', 'in' => '16'))));
	$headers = array('Content-Type' => 'application/json');
	$oauthClient->fetch(implode("?", array($resourceUrl, $query)), array(), OAUTH_HTTP_METHOD_GET, $headers);
	$html->pre(json_decode($oauthClient->getLastResponse()));

}

function soapV2Testing() {
	global $html;
	global $script;

	$sessionId = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;
	$apiUser = 'magentoapi';
	$apiKey = 'magentoapi';
	$api = 'http://' . $_SERVER['SERVER_NAME'] . '/magento/api/v2_soap/';

	$html->para("Using API: <b>$api</b>\n");

	$client = new SoapClient($api . '?wsdl');
	if(!isset($sessionId)) {
		$sessionId = $client->login($apiUser, $apiKey);
	}
	$html->para("Using Session Id: <b>$sessionId</b>");
	$actions = array(
		'soapV2ShowFunctions',
		'soapV2CatalogProductList'
	);
	$html->para("Available Tests:");
	$html->startList();
	foreach ($actions as $action) {
		$html->listItem("<a href=\"{$script}/f/soapV2Testing?t={$sessionId}&a={$action}\">" . $action . '</a>');
	}
	$html->endList();
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
	if (isset($a) && in_array($a, $actions)) {
		call_user_func($a, $client, $sessionId);
	}

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
function soapV2CatalogProductList($client, $sessionId) {
	$res = $client->catalogProductList($sessionId, array('filter' => array(array('key' => 'color', 'value' => '16'))));
	//$res = $client->catalogProductList($sessionId, array('complex_filter' => array(array('key' => 'color', 'value' => array('key' => 'like', 'value' => 'red')))));
	global $html;
	$html->pre(print_r($res, true));
}
function soapV2ShowFunctions($client, $sessionId) {
	global $html;
	$html->startList();
	foreach($client->__getFunctions() as $func) {
		preg_match('/^(.*?)\s+(.*?)\((.*?)\)$/',$func, $m);
		$html->listItem("{$m[1]} <b>{$m[2]}</b> ( {$m[3]} )");
	}
	$html->endList();
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


