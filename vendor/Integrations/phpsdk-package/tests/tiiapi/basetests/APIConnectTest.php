<?php

require_once(__DIR__ . '/../utilmethods.php');
require_once(__DIR__ . '/../testconsts.php');
require_once(__DIR__ . '/../../../vendor/autoload.php');

use Integrations\PhpSdk\Logger;
use Integrations\PhpSdk\Response;
use Integrations\PhpSdk\TiiUser;
use Integrations\PhpSdk\TurnitinAPI;
use Integrations\PhpSdk\TiiClass;
use Integrations\PhpSdk\TurnitinSDKException;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-15 at 16:24:56.
 */
class ApiConnectTest extends PHPUnit_Framework_TestCase
{

    private $object;
    private static $tempdir;

    const ERROR_LOG = 'error_log';

    public static function setUpBeforeClass()
    {
        Logger::$keeplogs = 0;
        self::$tempdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        ini_set(self::ERROR_LOG, self::$tempdir . self::ERROR_LOG);
    }

    public static function tearDownAfterClass()
    {
        $files = glob(self::$tempdir . DIRECTORY_SEPARATOR . '*', GLOB_MARK);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            unlink($file);
        }
        rmdir(self::$tempdir);
        ini_set(self::ERROR_LOG, null);
    }

    public function testXmlPretty()
    {
        $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
        $this->object->setDebug(false);
        // Do a Simple Always Works Call
        $class = new TiiClass();
        $class->setTitle(uniqid()); // We don't expect to find anything
        $response = $this->object->findClasses($class);
        UtilMethods::invokeMethod($response, 'xmlPretty', array('<xml><some><d></d><d></d></some></xml>'));
        UtilMethods::invokeMethod($response, 'xmlPretty', array('<xml><d></some></xml>'));
    }

    public function testOutputDebug()
    {
        $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
        $this->object->setDebug(false);
        // Do a Simple Always Works Call
        $class = new TiiClass();
        $class->setTitle(uniqid()); // We don't expect to find anything
        $response = $this->object->findClasses($class);
        $this->setOutputCallback(function () {
            // Noop
        });
        UtilMethods::invokeMethod($response, 'outputDebug', array('<xml></xml>', 'test'));
    }

    public function testConnectToApi()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
            $this->object->setDebug(false);
            $this->object->setLogPath(self::$tempdir);
            // Do a Simple Always Works Call
            $class = new TiiClass();
            $class->setTitle(uniqid()); // We don't expect to find anything
            $find_response = $this->object->findClasses($class);
            $this->assertEquals($find_response->getStatus(), 'warning');
            $this->assertEquals($find_response->getStatusCode(), 'nosourcedids');
        } catch (TurnitinSDKException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testConnectToApiAccountIdIncorrect()
    {
        try {
            $this->object = new TurnitinAPI('nonsense', TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
            $this->object->setDebug(false);
            $this->object->setLogPath(self::$tempdir);
            // Do a Simple Always Works Call
            $class = new TiiClass();
            $class->setTitle(uniqid()); // We don't expect to find anything
            $this->object->findClasses($class);
        } catch (TurnitinSDKException $e) {
            $this->assertEquals($e->getFaultCode(), 'Authentication Fault');
            $this->assertEquals($e->getMessage(), '"oauth_consumer_key" value is missing or not valid.');
        }
    }

    public function testConnectToApiSecretIncorrect()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, 'nonsense', TII_APIPRODUCT);
            $this->object->setDebug(false);
            $this->object->setLogPath(self::$tempdir);
            // Do a Simple Always Works Call
            $class = new TiiClass();
            $class->setTitle(uniqid()); // We don't expect to find anything
            $this->object->findClasses($class);
        } catch (TurnitinSDKException $e) {
            $this->assertEquals('Authentication Fault', $e->getFaultCode());
            $this->assertregExp('/API Login failed/', $e->getMessage());
        }
    }

    public function testConnectToApiBaseUrlIncorrect()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, 'http://www.google.com', TII_SECRET, TII_APIPRODUCT);
            $this->object->setDebug(false);
            $this->object->setLogPath(self::$tempdir);
            // Do a Simple Always Works Call
            $class = new TiiClass();
            $class->setTitle(uniqid()); // We don't expect to find anything
            $this->object->findClasses($class);
        } catch (TurnitinSDKException $e) {
            $log_message = file_get_contents(self::$tempdir . self::ERROR_LOG);
            // Should log to error_log if we can't parse xml
            $this->assertRegExp('/Turnitin SDK Exception.*Request.*Response/s', $log_message);
            $this->assertEquals($e->getFaultCode(), 'Client');
            $this->assertEquals($e->getMessage(), 'looks like we got no XML document');
        }
    }

    public function testTruncateLog()
    {
        $message = 'TESTMESSAGE';
        $exception = new TurnitinSDKException('Test Exception', '');
        $truncated = $exception->truncateLog($message, 4);
        $this->assertRegExp('/truncated/', $truncated);
        $substr = substr($message, 0, 4);
        $this->assertRegExp("/$substr \(/", $truncated);
    }

    public function testConnectToApiProductIncorrect()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, 'nonsense');
            $this->object->setDebug(false);
            $this->object->setLogPath(self::$tempdir);
            // Do a Simple Always Works Call
            $class = new TiiClass();
            $class->setTitle(uniqid()); // We don't expect to find anything
            $this->object->findClasses($class);
        } catch (TurnitinSDKException $e) {
            $this->assertEquals('Authentication Fault', $e->getFaultCode());
            $this->assertRegExp('/The integration ID \(Source\) is not enabled/', $e->getMessage());
        }
    }

    /**
     * @expectedException Integrations\PhpSdk\TurnitinSDKException
     * @expectedExceptionMessage Integration API Product ID Not Set
     */
    public function testConnectToApiProductNull()
    {
        $api = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, null);
        $class = new TiiClass();
        $class->setTitle(uniqid()); // We don't expect to find anything, we expect an exception
        $api->findClasses($class);
    }

    /**
     * @expectedException Integrations\PhpSdk\TurnitinSDKException
     * @expectedExceptionMessage Account ID Not Set
     */
    public function testConnectToApiAccountNull()
    {
        $api = new TurnitinAPI(null, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
        $class = new TiiClass();
        $class->setTitle(uniqid()); // We don't expect to find anything, we expect an exception
        $api->findClasses($class);
    }

    /**
     * @expectedException Integrations\PhpSdk\TurnitinSDKException
     * @expectedExceptionMessage Shared Key / Secret Not Set
     */
    public function testConnectToApiSecretNull()
    {
        $api = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, null, TII_APIPRODUCT);
        $class = new TiiClass();
        $class->setTitle(uniqid()); // We don't expect to find anything, we expect an exception
        $api->findClasses($class);
    }

    public function testConnectToApiSetGetDebug()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
            $this->object->setDebug(true);
            $this->object->setLogPath(self::$tempdir);
            $this->assertTrue($this->object->getDebug());
        } catch (TurnitinSDKException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testGetSetVersions()
    {
        $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
        $this->object->setDebug(false);
        $this->object->setLogPath(self::$tempdir);

        $testpluginversion = 'test';
        $testintegrationversion = 'test';

        $this->object->setPluginVersion($testpluginversion);
        $this->object->setIntegrationVersion($testintegrationversion);
        $this->assertEquals($testpluginversion, $this->object->getPluginVersion());
        $this->assertEquals($testintegrationversion, $this->object->getIntegrationVersion());
    }

    /**
     * @expectedException Integrations\PhpSdk\TurnitinSDKException
     */
    public function testSetGetProxy()
    {
        $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
        $this->object->setDebug(false);
        $this->object->setLogPath(self::$tempdir);

        $host     = 'localhost';
        $port     = 8080;
        $user     = 'test';
        $password = 'test';
        $bypass   = 'test';
        $type     = 'test';
        $sslcert  = 'test';

        $this->object->setProxyHost($host);
        $this->object->setProxyPort($port);
        $this->object->setProxyUser($user);
        $this->object->setProxyPassword($password);
        $this->object->setProxyBypass($bypass);
        $this->object->setProxyType($type);
        $this->object->setSSLCertificate($sslcert);

        $this->assertEquals($host, $this->object->getProxyHost());
        $this->assertEquals($port, $this->object->getProxyPort());
        $this->assertEquals($user, $this->object->getProxyUser());
        $this->assertEquals($password, $this->object->getProxyPassword());
        $this->assertEquals($bypass, $this->object->getProxyBypass());
        $this->assertEquals($type, $this->object->getProxyType());
        $this->assertEquals($sslcert, $this->object->getSSLCertificate());

        // Do a Simple Always Works Call
        $class = new TiiClass();
        $class->setTitle(uniqid()); // We don't expect to find anything
        $find_response = $this->object->findClasses($class);
        $this->assertEquals($find_response->getStatus(), 'warning');
        $this->assertEquals($find_response->getStatusCode(), 'nosourcedids');
    }

    public function testGetVersion()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
            $this->assertEquals($this->object->getVersion(), TurnitinAPI::VERSION);
        } catch (TurnitinSDKException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testSetLogPath()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
            $path = 'testpath';
            $this->object->setLogPath($path);
            $this->assertEquals($path, $this->object->logpath);
        } catch (TurnitinSDKException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testDebug()
    {
        try {
            $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
            $this->object->setDebug(true);
            $this->assertTrue($this->object->getDebug());
            $this->object->setDebug(null);
            $this->assertFalse($this->object->getDebug());
        } catch (TurnitinSDKException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @expectedException Integrations\PhpSdk\TurnitinSDKException
     */
    public function testOptions()
    {

        $this->object = new TurnitinAPI(TII_ACCOUNT, TII_APIBASEURL, TII_SECRET, TII_APIPRODUCT);
        $this->object->setProxyHost('localhost');
        $this->object->setProxyPort(8080);
        $this->object->setProxyBypass('test');
        $this->object->setProxyType('test');
        $this->object->setProxyUser('test');
        $this->object->setProxyPassword('test');
        $this->object->setSSLCertificate('test');
        $this->object->setIntegrationVersion('test');
        $this->object->setPluginVersion('test');

        $user = new TiiUser();
        $user->setUserId(0);
        $this->object->readUser($user);
    }
}
