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
//Same rationale as the function above.
function stream_context_create($contextArray)
{
	global $TEST_RESPECT_HTTP_HEADERS_SENT, $TEST_RESPECT_HTTP_BODY_SENT;

	if (isset($contextArray['http']['header']))
		$TEST_RESPECT_HTTP_HEADERS_SENT = explode("\r\n", $contextArray['http']['header']);

	if (isset($contextArray['http']['content']))
		$TEST_RESPECT_HTTP_BODY_SENT = $contextArray['http']['content'];
}

class ClientTest extends \PHPUnit_Framework_TestCase
{
	protected function tearDown() 
	{
		//see the function on top of this file
		global $TEST_RESPECT_HTTP_BODY, 
			   $TEST_RESPECT_HTTP_HEADERS, 
			   $TEST_RESPECT_HTTP_CALLED,
			   $TEST_RESPECT_HTTP_HEADERS_SENT,
			   $TEST_RESPECT_HTTP_BODY_SENT;

		unset(
			$TEST_RESPECT_HTTP_BODY, 
			$TEST_RESPECT_HTTP_HEADERS, 
			$TEST_RESPECT_HTTP_HEADERS_SENT,
			$TEST_RESPECT_HTTP_BODY_SENT,
			$GLOBALS['http_response_header']
		);
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
	function test_client_should_send_headers()
	{
		global $TEST_RESPECT_HTTP_HEADERS_SENT; //see the function on top of this file
		$r = Request::get('http://example.com')
		            ->host('foobar.com')
		            ->repeatingHeader('oops')
		            ->repeatingHeader('uups');
		Request::$globalHeaders = true;
        $r->send();
		$this->assertContains('Host: foobar.com', $r->headersSent);
		$this->assertContains('Repeating-Header: oops', $r->headersSent);
		$this->assertContains('Repeating-Header: uups', $r->headersSent);
		$this->assertContains('Host: foobar.com', $TEST_RESPECT_HTTP_HEADERS_SENT);
		$this->assertContains('Repeating-Header: oops', $TEST_RESPECT_HTTP_HEADERS_SENT);
		$this->assertContains('Repeating-Header: uups', $TEST_RESPECT_HTTP_HEADERS_SENT);
        
	}
	function test_client_should_send_body()
	{
		global $TEST_RESPECT_HTTP_BODY_SENT; //see the function on top of this file
		$r = Request::post('http://example.com')
					->body('Foo');
		Request::$globalHeaders = true;
        $r->send();
		$this->assertEquals('Foo', $r->bodySent);
		$this->assertEquals('Foo', $TEST_RESPECT_HTTP_BODY_SENT);
		
	 
	}
}








