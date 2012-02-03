<?php

namespace Respect\Http;

function file_get_contents()
{
	global $TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS, $http_response_header;
	$http_response_header = $TEST_RESPECT_HTTP_HEADERS;
	return $TEST_RESPECT_HTTP_BODY;
}

class ClientTest extends \PHPUnit_Framework_TestCase
{
	protected function tearDown() 
	{
		Client::$globalHeaders = false;
	}
	protected function mockFileGetContents($contents, array $headers=array())
	{
		global $TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS;
		Client::$globalHeaders = true;
		list($TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS) = func_get_args();
		return $contents;
	}
	function test_client_can_be_used_statically()
	{
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));

		//the test itself
		$r = Client::get('/foo');
		$this->assertEquals('<html>FooBody', (string) $r);
		$this->assertEquals($r['Host'], 'example.com');
	}
}