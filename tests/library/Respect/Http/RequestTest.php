<?php

namespace Respect\Http;

/** 
 * This function act as a mock for the original file_get_contents. It works using
 * the namespace lookup rules, so Respect\Http\file_get_contents() should be called
 * instead of file_get_contents from the global scope.
 */
function file_get_contents()
{
	//These globals are fed by mockFileGetContents in the class below
	global $TEST_RESPECT_HTTP_BODY,
		   $TEST_RESPECT_HTTP_HEADERS,
		   $TEST_RESPECT_HTTP_CALLED, 
	       $http_response_header; 
	
	//this is a true PHP predefined variable, we override it here
	$http_response_header = $TEST_RESPECT_HTTP_HEADERS;
	$TEST_RESPECT_HTTP_CALLED = true;
	return $TEST_RESPECT_HTTP_BODY;
}

class ClientTest extends \PHPUnit_Framework_TestCase
{
	protected function tearDown() 
	{
		//see the function on top of this file
		global $TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS, $TEST_RESPECT_HTTP_CALLED;

		unset($TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS, $GLOBALS['http_response_header']);
		$TEST_RESPECT_HTTP_CALLED = false;
		Request::$globalHeaders = false;
	}
	protected function mockFileGetContents($contents, array $headers=array())
	{
		global $TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS;
		Request::$globalHeaders = true;
		list($TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS) = func_get_args();
		return $contents;
	}
	function test_client_can_be_used_statically()
	{
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));

		$r = Request::get('http://foobarsample.com');
		$this->assertEquals('<html>FooBody', (string) $r);
		$this->assertEquals($r['Host'], 'example.com');
	}
	function test_client_response_is_lazy_unti_body_or_headers_are_requested()
	{
		global $TEST_RESPECT_HTTP_CALLED; //see the function on top of this file
		$this->mockFileGetContents('<html>This should not happen', array('Host'=>'thisshouldnothappen.com'));
		$r = Request::get('http://foobarsample.com');
		$this->assertFalse($TEST_RESPECT_HTTP_CALLED);
	}
}