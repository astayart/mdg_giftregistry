<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/2/14
 * Time: 2:17 PM
 */

header("Content-Type: text/html");
ini_set('display_errors', true);

require 'app/Mage.php';
Mage::setIsDeveloperMode(true);
$app = Mage::app();
$f = $app->getRequest()->getParam('f');
if (empty($f)) {
	$pi = $app->getRequest()->getPathInfo();
	preg_match('#^/f/([^/]+)$#', $pi, $match);
	if (count($match) > 0) {
		$f = $match[1];
	}
}
$allowedFunctions = array(
	'streamTest',
	'createTabFields',
	'checkCountryCode',
	'checkItemSku',
	'getAttributes',
	'xmlRpcSession',
	'getSoapSessionToken',
	'getRestSessionToken',
	'oAuthCallback'
);
$html = new HtmlOutputter();
$html->startHtml()->startBody();
$html->para("<a href=\"/magento/mtest.php\">home</a>");

if (isset($f) && in_array($f, $allowedFunctions)) {
	try {
		$html->para("executing function: " . $f);
		call_user_func($f);

	} catch (Exception $e) {
		$html->para("failed to run function $f");
		$html->para($e->getMessage());
		$html->pre($e->getTraceAsString());
	}
} else {
	$html->para("Available functions:");
	showAllowedFunctions();
	exit;
}

function oAuthCallback() {
	global $html;
	global $app;
	$param = $app->getRequest()->getParam('test');
	$html->para('got test param: ' . $param);

}

function getRestSessionToken() {
	global $html;
	$callbackUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '/f/oAuthCallback';
	$temporaryCredentialsRequestUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/oauth/initiate?oauth_callback=' . urlencode($callbackUrl);
	$adminAuthorizationUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/admin/oauth_authorize';
	$accessTokenRequestUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/oauth/token';
	$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/api/rest';
	$consumerKey = 'nsdzw5xdw5gamn877kr3l3kkizq4ikbw';
	$consumerSecret = 'nr0wd0kxbtade23ekmw031f9icl27nl1';

	session_start();
	if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
		$_SESSION['state'] = 0;
	}
	try {
		$authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
		$oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
		$oauthClient->enableDebug();

		if (!isset($_GET['oauth_token']) && !$_SESSION['state']) {
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

function getSoapSessionToken() {
	global $html;
	$apiUser = 'magentoapi';
	$apiKey = 'magentoapi';
	$client = new SoapClient('http://' . $_SERVER['SERVER_NAME'] . '/magento/api/v2_soap/?wsdl');
	$sessionId = $client->login($apiUser, $apiKey);

	$html->para('found soap session id: ' . $sessionId);
}

function xmlRpcSession() {
	global $html;
	$sessionId = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;
	$apiUser = 'magentoapi';
	$apiKey = 'magentoapi';
	$client = new Zend_XmlRpc_Client('http://' . $_SERVER['SERVER_NAME'] . '/magento/api/xmlrpc/');

	$html->para('using API: ' . 'http://' . $_SERVER['SERVER_NAME'] . '/magento/api/xmlrpc/');
	if (!isset($sessionId)) {
		$sessionId = $client->call('login', array($apiUser, $apiKey));
		$html->para('received sessionid: ' . $sessionId);
	}
	$actions = array(
		'xmlCatalogProductList'
	);
	foreach ($actions as $action) {
		$html->para("<a href=\"/magento/mtest.php?f=xmlRpcSession&t=${sessionId}&a=${action}\">" . $action . '</a>');
	}
	$a = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
	if (isset($a) && in_array($a, $actions)) {
		// execute action...
		call_user_func($a, $client, $sessionId);
	}

}

function xmlCatalogProductList($client, $sessionId) {
	$res = $client->call('call', array($sessionId, 'catalog_product.list'));
	global $html;
	$html->pre(print_r($res, true));
}

function getAttributes() {
	global $html;

	$handle = fopen("/var/www/magentoee.1-13.local/htdocs/magento/var/hawksearch/feeds/attributes.txt", 'r');
	$keys = fgetcsv($handle, 0, "\t");
	$atts = array();
	while ($vals = fgetcsv($handle, 0, "\t")) {
		if ($vals[0] == 'vin-bw') {
			$atts[] = array('attribute' => $vals[1], 'value' => $vals[2]);
		}
	}
	$html->para('done, found ' . count($atts) . ' attributes');
	$html->startList();
	foreach ($atts as $a) {
		$html->listItem($a['attribute'] . ': ' . $a['value']);
	}
	$html->endList();

	fclose($handle);


	/** @var Mage_Catalog_Model_Product $product */
//	$product = Mage::helper('catalog/product')->getProduct('10780', Mage::app()->getStore()->getId(), 'sku');
//	//$html->para('the guitar: ' . print_r($product, true));
//	$atts = $product->getAttributes();
//
//	ksort($atts);
//	foreach(array_keys($atts) as $a) {
//		$html->para($a);
//	}


}

function checkItemSku() {
	global $html;
	$filename = "/home/magentouser/items.txt";
	$delim = "\t";
	$headerRow = true;
	$csv = new CsvReader($filename, $delim, $headerRow);
	if ($csv !== false) {
		$a = array();
		while ($csv->nextRow()) {
			if (isset($a[$csv->item('sku')]) && is_array($a[$csv->item('sku')])) {
				$a[$csv->item('sku')][] = new HawkItem($csv);
			} else {
				$a[$csv->item('sku')] = array(new HawkItem($csv));
			}
		}
		$csv->close();
		foreach (array_keys($a) as $key) {
			if (count($a[$key]) > 1) {
				if (($col = compareItems($a[$key])) !== false) {
					$map = $csv->getMap();
					$idx = array_keys($map, $col);
					$name = $idx[0];
					$html->para("found sku: $key, " . count($a[$key]) . " times with different values for col $name");
				}
			}
		}
		$html->para("Done checking Item sku");
	}
}

function compareItems($hi) {
	/* start with the first row, and check each other row for differences. if
	 * found, stop and return true
	 */

	for ($i = 1; $i < count($hi); $i++) {
		for ($j = 0; $j < $hi[0]->itemCount(); $j++) {
			if ($hi[0]->item($j) != $hi[$i]->item($j)) {
				return $j;
			}
		}
	}
	return false;
}

function streamTest() {
	$path = Mage::helper('hawksearch_datafeed/feed')->getFeedFilePath();
	/* ok, so the goal here is to test the behavior of the "php://temp/maxmemory:$byteSize" stream
	 * for the purpose of buffering the fputcsv() function call.
	 * so what we want to do is to fill up the buffer, and periodically flush to file.
	 * My typical plan for such a task would be to create a private class that handles
	 * opening, buffering, flushing, and closing of the file, so lets try it. */
	$buffer = new CsvWriteBuffer($path . '/testbuff.csv', "\t", 1024 * 2048, 1023 * 2048);
	$counter = 0;
	while ($counter++ < 1000) {
		$buffer->appendRow(array($counter, 'test, one', 'emedeede "quotes"', "embedded tab->   <-", "another embedded tab->\t<-"));
		$buffer->appendRow(array($counter, 'here', 'are', 'some', 'plain', 'fields'));
		$buffer->appendRow(array($counter, 'with spaces', 'withoutspaces'));
		$buffer->appendRow(array($counter, 'and don\'t forget', "about embedded\nline endings!"));
	}
}

function createTabFields() {
	global $app;

	$out = fopen('php://output', 'a');
	fputcsv($out, array('test, one', 'emedeede "quotes"', "embedded tab->	<-", "another embedded tab->\t<-"), "\t");
	fputcsv($out, array('here', 'are', 'some', 'plain', 'fields'), "\t");
	fputcsv($out, array('plain', 'embedded, comma', 'embedded faux \t tab'), "\t");
	fputcsv($out, array('with spaces', 'withoutspaces'), "\t");
	fputcsv($out, array('and don\'t forget', "about embedded\nline endings!"), "\t");
	fputcsv($out, array('comma sep', 'values', 'with embedded, comma'));
	fclose($out);
}

function mdg_giftregistry() {
	//$registry = Mage::getModel('mdg_giftregistry/entity');
//echo get_class($registry);

}

function checkCountryCode() {
	global $app;
	/** @var $collection Mage_Directory_Model_Resource_Country_Collection */

	$collection = Mage::getModel('directory/country')->getResourceCollection();
	$options = $collection->toOptionArray();

	$countryMap = array();
	foreach ($options as $option) {
		if ($option['value'] != '') {
			$countryMap[$option['value']] = $option['label'];
		}
	}

	$code = $app->getRequest()->getParam('code');
	if (!isset($code)) {
		$code = 'US';
	}

	echo 'itemid US: ' . $countryMap[$code];
}

function getTempDirList() {
	$dir = array();
	if ($handle = opendir(sys_get_temp_dir())) {
		while (false !== ($entry = readdir($handle))) {
			$dir[] = $entry;
		}
	}
	closedir($handle);
	asort($dir);
	return $dir;
}

function showAllowedFunctions() {
	global $html;
	global $allowedFunctions;
	foreach ($allowedFunctions as $func) {
		$html->para("<a href=\"/magento/mtest.php?f=$func\">" . $func . '</a>');
	}
}

class HawkItem {
	private $values;

	/** @var $row CsvReader */
	public function __construct($row) {
		foreach ($row->getMap() as $key => $idx) {
			$this->values[$idx] = $row->item($key);
		}
	}

	public function item($idx) {
		return $this->values[$idx];
	}

	public function itemCount() {
		return count($this->values);
	}
}

class CsvReader {
	private $fileName;
	private $handle;
	private $hMap;
	private $delimiter;
	private $data;

	public function __construct($fn, $d, $head) {
		$this->fileName = $fn;
		$this->delimiter = $d;
		$this->handle = fopen($this->fileName, "r");
		if ($this->handle !== false) {
			if ($head) {
				$map = array();
				$fields = fgetcsv($this->handle, 0, $this->delimiter);
				if ($fields === false) {
					return false;
				}
				for ($i = 0; $i < count($fields); $i++) {
					$map[$fields[$i]] = $i;
				}
				$this->hMap = $map;
			}
		} else {
			return false;
		}
	}

	public function nextRow() {
		$this->data = fgetcsv($this->handle, 0, $this->delimiter);
		return $this->data;
	}

	public function item($field) {
		if (is_array($this->data)) {
			return $this->data[$this->hMap[$field]];
		}
		return false;
	}

	public function close() {
		if ($this->handle) {
			fclose($this->handle);
		}
	}

	public function getMap() {
		return $this->hMap;
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


class CsvWriteBuffer {
	private $tempBuffer;
	private $bufferSize;
	private $finalDestinationPath;
	private $outputFile;
	private $currentSize;
	private $bufferOpen = false;
	private $outputOpen = false;
	private $delimiter;
	private $flushSize;

	public function __construct($destFile, $delim = ",", $buffSize, $flushSize) {
		$this->finalDestinationPath = $destFile;
		if (file_exists($this->finalDestinationPath)) {
			if (false === unlink($this->finalDestinationPath)) {
				throw new Exception("CsvWriteBuffer: unable to remove old file '$this->finalDestinationPath'");
			}
		}
		$this->delimiter = $delim;
		$this->bufferSize = $buffSize;
		$this->flushSize = $flushSize;
	}

	public function __destruct() {
		$this->flushBuffer();
		fclose($this->tempBuffer);
		fclose($this->outputFile);
	}

	public function appendRow(array $fields) {
		if (!$this->bufferOpen) {
			$this->openBuffer();
		}
		$this->currentSize += fputcsv($this->tempBuffer, $fields, $this->delimiter);
		if ($this->currentSize >= $this->flushSize) {
			$this->flushBuffer();
		}
	}

	private function openBuffer() {
		if (false === ($this->tempBuffer = fopen("php://temp/maxmemory:$this->bufferSize", 'r+'))) {
			throw new Exception("CsvWriteBuffer: Failed to open temp buffer");
		}
		$this->bufferOpen = true;
		$this->currentSize = 0;
	}

	private function openOutput() {
		if (false === ($this->outputFile = fopen($this->finalDestinationPath, 'a'))) {
			throw new Exception("CsvWriteBuffer: Failed to open destination file '$this->finalDestinationPath''");
		}
		$this->outputOpen = true;
	}

	private function flushBuffer() {
		if (!$this->outputOpen) {
			$this->openOutput();
		}
		rewind($this->tempBuffer);
		while (!feof($this->tempBuffer)) {
			if (false === fwrite($this->outputFile, fread($this->tempBuffer, 8192))) {
				throw new Exception("CsvWriteBuffer: Error writing to destination file '$this->finalDestinationPath'");
			}
		}
		ftruncate($this->tempBuffer, 0);
		rewind($this->tempBuffer);
		$this->currentSize = 0;
	}
}

class TestObject {
	private $value;
	private $array;

	public function __construct($value) {
		$this->value = $value;
		$this->array['value'] = $value;
		$this->array['ammendedvalue'] = $value . ' ammended';
	}
}