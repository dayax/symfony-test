<?php

/*
 * This file is part of the symfony-test package.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dayax\symfony\test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;
use \PHPUnit_Framework_ExpectationFailedException;

abstract class WebTestCase extends BaseTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;
    
    protected $isOpen = false;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->isOpen = false;
    }

    public function open($uri, $method = "GET", array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        $this->crawler = $this->client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->request = $this->client->getRequest();
        $this->response = $this->client->getResponse();
        $this->isOpen = true;
    }
    
    private function getControllerAttributes()
    {
        $attribute = $this->client->getRequest()->attributes->get('_controller');
        $exp = explode("::", $attribute);
        return array($exp[0], $exp[1]);
    }
    
    public function validateWebTypeAssert()
    {        
        $trace = debug_backtrace();
        $function = $trace[1]['function'];
        if(false===$this->isOpen){
            throw new \LogicException('You have to open url first before run "'.$function.'" method.');
        }
    }

    public function assertResponseStatus($code)
    {
        $this->validateWebTypeAssert();
        $actual = $this->response->getStatusCode();
        if($code != $actual){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that code "%s", actual status code is "%s"', $code, $actual
            ));
        }
        $this->assertEquals($code, $actual);
    }

    public function assertHasElement($selector)
    {
        $this->validateWebTypeAssert();
        $actual = $this->crawler->filter($selector)->count();
        if($actual <= 0){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that element with "%s" selector is exist.', $selector
            ));
        }
        $this->assertTrue($actual > 0);
    }

    public function assertAction($expected)
    {
        $this->validateWebTypeAssert();
        $attributes = $this->getControllerAttributes();
        $actual = $attributes[1];
        $substr = strtolower(substr($actual, 0, strpos($actual, 'Action')));

        if(strtolower($expected) === $substr){
            $this->assertEquals(strtolower($expected), strtolower($substr));
        }
        elseif(strtolower($expected) === strtolower($actual)){
            $this->assertEquals(strtolower($expected), strtolower($actual));
        }
        else{
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that action is "%s", actual action is "%s"', $expected, $actual
            ));
        }
    }

    public function assertController($expected)
    {
        $this->validateWebTypeAssert();
        $attributes = $this->getControllerAttributes();
        $namespaced = $attributes[0];
        $exp = explode("\\", $namespaced);
        $class = $exp[count($exp) - 1];
        if(strpos($expected, '\\') !== false){
            if($expected != $namespaced){
                throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting that controller is "%s", actual controller is "%s"', $expected, $namespaced
                ));
            }
            $this->assertEquals(strtolower($expected), strtolower($namespaced));
        }
        else{
            if(strtolower($expected) != strtolower($class)){
                throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting that controller is "%s", actual controller is "%s"', $expected, $class
                ));
            }
            $this->assertEquals(strtolower($expected), strtolower($class));
        }
    }

    /**
     * Get response header by key
     *
     * @param  string   $header
     * @return mixed    Header content
     */
    protected function getResponseHeader($header)
    {
        $headers = $this->response->headers;
        $responseHeader = $headers->get($header,false);
        return $responseHeader;        
    }

    /**
     * Assert response header exists
     *
     * @param  string $header
     */
    public function assertHasResponseHeader($header)
    {
        $this->validateWebTypeAssert();
        $responseHeader = $this->getResponseHeader($header);
        if(false===$this->getResponseHeader($header)){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" found',
                $header
            ));
        }
        $this->assertNotEquals(false, $responseHeader);
    }
    
    /**
     * Assert response header does not exist
     *
     * @param  string $header
     */
    public function assertNotHasResponseHeader($header)
    {
        $this->validateWebTypeAssert();
        $responseHeader = $this->getResponseHeader($header);
        
        if(false!==$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that response header "%s" was not found',$header
            ));
        }
        
        $this->assertFalse($responseHeader);
    }
    
    /**
     * Assert response header exists and contains the given string
     *
     * @param  string $header
     * @param  string $match
     */
    public function assertResponseHeaderContains($header,$match)
    {
        $responseHeader = $this->getResponseHeader($header);
        if(!$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists',
                $header
            ));
        }
        if($match!=$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that response header for "%s" contains "%s". Actual content is "%s"',
                $header,$match,$responseHeader
            ));
        }
        
        $this->assertEquals($match, $responseHeader);
    }
    
    /**
     * Assert response header exists and DOES NOT CONTAIN the given string
     *
     * @param  string $header
     * @param  string $match
     */
    public function assertNotResponseHeaderContains($header, $match)
    {
        $responseHeader = $this->getResponseHeader($header);
        if(!$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists', $header
            ));
        }
        if($match == $responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" does not contain "%s"', $header, $match
            ));
        }
        $this->assertNotEquals($match, $responseHeader);
    }  
    
    /**
     * Assert response header exists and matches the given pattern
     *
     * @param  string $header
     * @param  string $pattern
     */    
    public function assertResponseHeaderRegex($header,$pattern)       
    {
        $responseHeader = $this->getResponseHeader($header);
        if(!$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists',
                $header
            ));
        }
        if(!preg_match($pattern, $responseHeader)){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" exists and matches regex "%s", actual content is "%s"',
                $header, $pattern, $responseHeader
            ));
        }
        $this->assertTrue((boolean) preg_match($pattern,$responseHeader));
    }
    
    /**
     * Assert response header does not exist and/or does not match the given regex
     *
     * @param  string $header
     * @param  string $pattern
     */
    public function assertNotResponseHeaderRegex($header,$pattern)
    {
        $responseHeader = $this->getResponseHeader($header);
        if(!$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header, header "%s" do not exists', $header
            ));
        }
        if(preg_match($pattern, $responseHeader)){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response header "%s" does not match regex "%s"', $header, $pattern
            ));
        }
        $this->assertFalse((boolean) preg_match($pattern, $responseHeader));
    }
    
    /**
     * Assert that response is a redirect
     */
    public function assertRedirect()
    {
        $responseHeader = $this->getResponseHeader('Location');
        if(false===$responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is a redirect'
            ));
        }
        $this->assertNotEquals(false, $responseHeader);
    }

    /**
     * Assert that response is NOT a redirect
     *
     * @param  string $message
     */
    public function assertNotRedirect()
    {
        $responseHeader = $this->getResponseHeader('Location');
        if(false !== $responseHeader){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is a redirect, actual redirection is "%s"', $responseHeader
            ));
        }
        $this->assertFalse($responseHeader);
    }
    
    /**
     * Assert that response redirects to given URL
     *
     * @param  string $url
     */
    public function assertRedirectTo($url)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if(!$responseHeader){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is a redirect'
            ));
        }
        if($url != $responseHeader){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to "%s", actual redirection is "%s"', $url, $responseHeader
            ));
        }
        $this->assertEquals($url, $responseHeader);
    }

    /**
     * Assert that response does not redirect to given URL
     *
     * @param  string $url
     * @param  string $message
     */
    public function assertNotRedirectTo($url)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if(!$responseHeader){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is a redirect'
            ));
        }
        if($url == $responseHeader){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to "%s"', $url
            ));
        }
        $this->assertNotEquals($url, $responseHeader);
    }
    
    /**
     * Assert that redirect location matches pattern
     *
     * @param  string $pattern
     */
    public function assertRedirectRegex($pattern)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if(!$responseHeader){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is a redirect'
            ));
        }
        if(!preg_match($pattern, $responseHeader)){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to URL MATCHING "%s", actual redirection is "%s"',
                $pattern,
                $responseHeader
            ));
        }
        $this->assertTrue((boolean) preg_match($pattern, $responseHeader));
    }

    /**
     * Assert that redirect location does not match pattern
     *
     * @param  string $pattern
     */
    public function assertNotRedirectRegex($pattern)
    {
        $responseHeader = $this->getResponseHeader('Location');
        if(!$responseHeader){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response is a redirect'
            ));
        }
        if(preg_match($pattern, $responseHeader)){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response DOES NOT redirect to URL MATCHING "%s"', $pattern
            ));
        }
        $this->assertFalse((boolean) preg_match($pattern, $responseHeader));
    }

}