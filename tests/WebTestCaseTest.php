<?php

/*
 * This file is part of the symfony-test package.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dayax\symfony\test\tests;

use dayax\symfony\test\WebTestCase;

class WebTestCaseTest extends WebTestCase
{
    static public function getKernelClass()
    {
        require_once __DIR__.'/fixtures/app/AppKernel.php';
        return "dayax\\symfony\\test\\tests\\fixtures\\app\\AppKernel";
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage actual status code is "200"
     */
    public function testAssertResponseStatus()
    {
        $this->open('/');
        $this->assertResponseStatus(200);
        
        $this->assertResponseStatus(500);
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage was NOT "200"
     */
    public function testAssertNotResponseStatus()
    {
        $this->open('/');
        $this->assertNotResponseStatus(500);

        $this->assertNotResponseStatus(200);
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that controller is "FooController", actual controller is "DefaultController"
     */
    public function testAssertController()
    {
        $this->open('/');
        $this->assertController('DefaultController');
        $this->assertController('Demo\\Controller\\DefaultController');
        $this->assertController('FooController');
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that controller is "Demo\Controller\FooController", actual controller is "Demo\Controller\DefaultController"
     */
    public function testAssertControllerWithNamespacedClass()
    {
        $this->open('/');    
        $this->assertController('Demo\\Controller\\DefaultController');
        
        $this->assertController('Demo\\Controller\\FooController');
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that action is "FooAction", actual action is "IndexAction"
     */
    public function testAssertAction()
    {
        $this->open('/');
        $this->assertAction('indexAction');
        $this->assertAction('index');
        
        $this->assertAction('FooAction');
    }
    
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You have to open url first before run "assertHasElement" method.
     */
    public function testValidateWebTypeAssert()
    {                
        $this->assertHasElement('foo');
    }
    
    /**
     * @covers \dayax\symfony\test\WebTestCase::assertHasResponseHeader
     * @covers \dayax\symfony\test\WebTestCase::getResponseHeader
     */
    public function testAssertHasResponseHeader()
    {
        $this->open('/');
        $this->assertHasResponseHeader('Content-Type');
        
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertHasResponseHeader('Unknown-Header');
    }
    
    public function testAssertNotHasResponseHeader()
    {
        $this->open('/');
        
        $this->assertNotHasResponseHeader('Unknown-Header');
        
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotHasResponseHeader('Content-Type');
    }
    
    public function testAssertResponseHeaderContains()
    {
        $this->open('/');        
        $this->assertResponseHeaderContains('Content-Type','text/html; charset=UTF-8');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
            'Failed asserting that response header for "Content-Type" contains "text/json". Actual content is "text/html; charset=UTF-8"'
        );
        
        $this->assertResponseHeaderContains('Content-Type', 'text/json');
    }
    
    public function testAssertNotResponseHeaderContains()
    {
        $this->open('/');
        $this->assertNotResponseHeaderContains('Content-Type', 'text/json');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
            'Failed asserting response header "Content-Type" does not contain "text/html; charset=UTF-8"'
        );
        
        $this->assertNotResponseHeaderContains('Content-Type', 'text/html; charset=UTF-8');
    }
          
    public function testAssertResponseHeaderRegex()
    {
        $this->open('/');
        $this->assertResponseHeaderRegex('Content-Type','#charset#');
        $this->assertResponseHeaderRegex('Content-Type','#text#');
        $this->assertResponseHeaderRegex('Content-Type','#html#');
        
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
            'actual content is "text/html; charset=UTF-8"'
        );
        $this->assertResponseHeaderRegex('Content-Type','#json#');
    }
    
    public function testAssertNotResponseHeaderRegex()
    {
        $this->open('/');
        $this->assertNotResponseHeaderRegex('Content-Type','#json#');
        
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
            'Failed asserting response header "Content-Type" does not match regex "#html#'
        );
        $this->assertNotResponseHeaderRegex('Content-Type','#html#');
    }
    
    
    /**
     * @dataProvider getTestShouldThrowException
     * @expectedException \PHPUnit_Framework_ExpectationFailedException     
     */
    public function testShouldThrowWhenResponseHeaderNotExist($method, $header)
    {
        $this->open('/');
        $this->$method($header, null, null);
    }

    public function getTestShouldThrowException()
    {
        return array(
            array('assertResponseHeaderContains', 'foo-bar'),
            array('assertNOtResponseHeaderContains','foo-bar'),
            array('assertResponseHeaderRegex', 'foo-bar'),
            array('assertNotResponseHeaderRegex', 'foo-bar'),
        );
    }

    public function testAssertRedirect()
    {
        $this->open('/redirect');
        $this->assertRedirect();
        
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
            'actual redirection is "http://www.example.com'
        );
        
        $this->assertNotRedirect();
    }
    
    public function testAssertNotRedirect()
    {
        $this->open('/');
        $this->assertNotRedirect();

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertRedirect();
    }
    
    public function testAssertRedirectTo()
    {
        $this->open('/redirect');
        $this->assertRedirectTo('http://www.example.com');
        
        $this->setExpectedException(
            'PHPUnit_Framework_ExpectationFailedException', 'actual redirection is "http://www.example.com"' // check actual redirection is display
        );
        $this->assertRedirectTo('http://www.symfony.com');
    }

    public function testAssertNotRedirectTo()
    {
        $this->open('/redirect');
        $this->assertNotRedirectTo('http://www.symfony.com');

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotRedirectTo('http://www.example.com');
    }        
    
    public function testAssertRedirectRegex()
    {
        $this->open('/redirect');
        $this->assertRedirectRegex('#example\.com$#');

        $this->setExpectedException(
            'PHPUnit_Framework_ExpectationFailedException', 'actual redirection is "http://www.example.com"' // check actual redirection is display
        );
        $this->assertRedirectRegex('#example\.id$#');
    }

    public function testAssertNotRedirectRegex()
    {
        $this->open('/redirect');
        $this->assertNotRedirectRegex('#example\.id#');

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotRedirectRegex('#example\.com$#');
    }
    
    /**
     * @dataProvider getTestShouldThrowWhenNotRedirect
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     */
    public function testShouldThrowWhenNotRedirect($method)
    {
        $this->open('/');
        $this->$method('http://foo.com');
    }
    
    public function getTestShouldThrowWhenNotRedirect()
    {
        return array(
            array('assertRedirectTo'),
            array('assertNotRedirectTo'),
            array('assertRedirectRegex'),
            array('assertNotRedirectRegex'),
        );
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that element with ".non-existent-css-class" selector is exist.
     */
    public function testAssertHasElement()
    {
        $this->open('/');
        $this->assertHasElement('h1');
        $this->assertHasElement('h2');

        $this->assertHasElement('.non-existent-css-class');
    }
    
    public function testAssertNotHasElement()
    {
        $this->open('/');
        $this->assertNotHasElement('h1.foo');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotHasElement('h1');
    }
    
    public function testAssertElementCount()
    {
        $this->open('/');
        $this->assertElementCount('h1', 1);
                
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
            'Failed asserting that current response contain "h1" element, with "200" count. Actual element count is "1"'
        );
        $this->assertElementCount('h1', 200);        
    }
}