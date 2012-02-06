<?php

namespace Respect\Http;

/** 
 * This function act as a mock for the original file_get_contents. It works using
 * the namespace lookup rules, so Respect\Http\file_get_contents() should be called
 * instead of file_get_contents from the global scope.
 */
function stream_get_contents()
{
	//These globals are fed by mockFileGetContents in the class below
	global $TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_CALLED;
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
function stream_get_meta_data() 
{
	global $TEST_RESPECT_HTTP_HEADERS,
		   $TEST_RESPECT_HTTP_CALLED; 
	$TEST_RESPECT_HTTP_CALLED = true;
	return array('wrapper_data' => $TEST_RESPECT_HTTP_HEADERS);
}
function fopen(){}

class MessageTest extends \PHPUnit_Framework_TestCase
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
	}
	protected function mockFileGetContents($contents, array $headers=array())
	{
		global $TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS;
		list($TEST_RESPECT_HTTP_BODY, $TEST_RESPECT_HTTP_HEADERS) = func_get_args();
		return $contents;
	}
	function test_client_can_be_used_statically()
	{
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));

		$r = Message::get('http://foobarsample.com');
		$this->assertEquals('<html>FooBody', (string) $r);
		$this->assertEquals($r['Host'], 'example.com');
	}
	function test_client_response_is_lazy_unti_body_or_headers_are_requested()
	{
		global $TEST_RESPECT_HTTP_CALLED; //see the function on top of this file
		$this->mockFileGetContents('<html>This should not happen', array('Host'=>'thisshouldnothappen.com'));
		$r = Message::get('http://foobarsample.com');
		$this->assertFalse($TEST_RESPECT_HTTP_CALLED);
	}
	function test_client_should_send_headers()
	{
		global $TEST_RESPECT_HTTP_HEADERS_SENT; //see the function on top of this file
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));
		$r = Message::get('http://example.com')
		            ->host('foobar.com')
		            ->repeatingHeader('oops')
		            ->repeatingHeader('uups');
        $r->send();
		$this->assertContains('Host: foobar.com', $r->context['header']);
		$this->assertContains('Repeating-Header: oops', $r->context['header']);
		$this->assertContains('Repeating-Header: uups', $r->context['header']);
		$this->assertContains('Host: foobar.com', $TEST_RESPECT_HTTP_HEADERS_SENT);
		$this->assertContains('Repeating-Header: oops', $TEST_RESPECT_HTTP_HEADERS_SENT);
		$this->assertContains('Repeating-Header: uups', $TEST_RESPECT_HTTP_HEADERS_SENT);
        
	}
	function test_client_should_send_body()
	{
		global $TEST_RESPECT_HTTP_BODY_SENT; //see the function on top of this file
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));
		$r = Message::post('http://example.com')
					->content('Foo');
        $r->send();
		$this->assertEquals('Foo', $r->context['content']);
		$this->assertEquals('Foo', $TEST_RESPECT_HTTP_BODY_SENT);
	}

	function test_context_options()
	{
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));
		$r = Message::get('http://example.com')
		            ->proxy('foo')
		            ->followRedirects(5)
		            ->protocolVersion(1.1)
		            ->timeout(10)
		            ->ignoreErrors(true);
        $r->send();
	    $this->assertEquals('foo', $r->context['proxy']);
	    $this->assertEquals(true, $r->context['follow_redirects']);
	    $this->assertEquals(5, $r->context['max_redirects']);
	    $this->assertEquals(1.1, $r->context['protocol_version']);
	    $this->assertEquals(true, $r->context['ignore_errors']);
	    $this->assertEquals(10, $r->context['timeout']);
	}
	function text_zero_redirects()
	{
		$this->mockFileGetContents('<html>FooBody', array('Host'=>'example.com'));
		$r = Message::get('http://example.com')
		            ->followRedirects(0);
        $r->send();
	    $this->assertEquals(false, $r->context['follow_redirects']);
	    $this->assertArrayNotHasKey('max_redirects', $r->context);
	}
}








