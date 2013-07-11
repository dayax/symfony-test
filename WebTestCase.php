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
use PHPUnit_Framework_ExpectationFailedException;
use Symfony\Component\DomCrawler\Form;

abstract class WebTestCase extends BaseTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;
    
    private $is_open = false;

    
    public function setUp()
    {
        if(!is_object($this->client)){
            $this->client = static::createClient();
        }
        $this->is_open = false;
    }

    /**
     * Open an URI
     * @param string    $uri
     * @param string    $method
     * @param array     $parameters
     * @param array     $files
     * @param array     $server
     * @param mixed     $content
     * @param mixed     $changeHistory
     */
    public function open($uri, $method = "GET", array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        $this->getClient()->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
        $this->is_open = true;
    }
    
    /**
     * Return a controller attributes for current request
     * @return array
     */
    private function getControllerAttributes()
    {
        $attribute = $this->getClient()->getRequest()->attributes->get('_controller');
        $exp = explode("::", $attribute);
        return array($exp[0], $exp[1]);
    }
    
    /**
     * Check if open method is already called
     * @throws \LogicException
     */
    public function validateWebTypeAssert()
    {        
        $trace = debug_backtrace();
        $function = $trace[1]['function'];
        if(false===$this->is_open){
            throw new \LogicException('You have to call open first before run "'.$function.'" method.');
        }
    }

    /**
     * Return active response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->getClient()->getResponse();
    }
    
    /**
     * Return Client
     * @return \Symfony\Component\HttpKernel\Client
     */
    public function getClient()
    {
        return $this->client;
    }
    
    public function getCrawler()
    {
        $this->validateWebTypeAssert();
        return $this->getClient()->getCrawler();
    }
    
    /**
     * Assert response status code
     *
     * @param  int $code
     */
    public function assertResponseStatus($code)
    {
        $this->validateWebTypeAssert();
        $actual = $this->getResponse()->getStatusCode();
        if($code != $actual){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that code "%s", actual status code is "%s"', $code, $actual
            ));
        }
        $this->assertEquals($code, $actual);
    }
    
    /**
     * Assert not response status
     *
     * @param  int $code
     */
    public function assertNotResponseStatus($code)
    {           
        $this->validateWebTypeAssert();
        $match = $this->getResponse()->getStatusCode();
        if($code == $match){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response code was NOT "%s"', $code
            ));
        }
        $this->assertNotEquals($code, $match);
    }

    public function assertAction($expected)
    {
        if(false===strpos($expected, 'Action')){
            $expected = $expected."Action";
        }
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
        if(false===strpos($expected, 'Controller')){
            $expected = $expected."Controller";
        }
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
        $headers = $this->getResponse()->headers;
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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
        $this->validateWebTypeAssert();
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

    /**
     * Get content with given selector
     * @param   string $selector
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function filter($selector)
    {
        $this->validateWebTypeAssert();
        $crawler = $this->getClient()->getCrawler();
        $method = 'filter';
        if(substr($selector, 0,1)==='/'){
            $method = 'filterXPath';
        }        
        return $crawler->$method($selector);
    }
    
    /**
     * Assert that response content contains an element determined by $selector
     * @param   string $selector
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertHasElement($selector)
    {
        $this->validateWebTypeAssert();
        $actual = $this->filter($selector)->count();
        if($actual <= 0){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that element with "%s" selector is exist.', $selector
            ));
        }
        $this->assertTrue($actual > 0);
    }
    
    /**
     * Assert that response content DOES NOT CONTAIN an element determined by $selector
     * @param   string $selector
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertNotHasElement($selector)
    {
        $this->validateWebTypeAssert();
        $actual = $this->filter($selector)->count();
        if($actual !== 0){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that element with "%s" selector is exist.', $selector
            ));
        }
        $this->assertEquals(0,$actual);
    }
    
   
    /**
     * Assert against DOM selection; should contain exact number of nodes
     *
     * @param  string $selector         CSS/XPath selector path
     * @param  string $expectedCount    Number of nodes that should match
     */
    public function assertElementCount($selector,$expectedCount)
    {
        $this->validateWebTypeAssert();
        $actual = $this->filter($selector)->count();
        if($expectedCount!=$actual){
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting that current response contain "%s" element, with "%s" count. Actual element count is "%s"'
                , $selector,$expectedCount,$actual
            ));
        }
        $this->assertEquals($expectedCount,$actual);
    }
    
    public function assertNotElementCount($selector,$expectedCount)
    {
        $this->validateWebTypeAssert();
        $actual = $this->filter($selector)->count();
        if($expectedCount===$actual){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY "%s" DOES NOT OCCUR EXACTLY "%d" times',
                $selector, $expectedCount
            ));
        }
        $this->assertNotEquals($expectedCount, $actual);
    }
    
    public function assertElementContains($selector,$match)
    {
        $this->validateWebTypeAssert();
        
        $result = $this->filter($selector);
        if($result->count()===0){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $selector
            ));
        }
        
        if(false===strpos($result->text(),$match)){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting node denoted by "%s" CONTAINS content "%s", actual content is "%s"', $selector, $match, $result->text()
            ));
        }
        $this->assertContains($match,$result->text());
    }
    
    public function assertNotElementContains($selector,$match)
    {
        $this->validateWebTypeAssert();
        $result = $this->filter($selector);
        if($result->count()==0){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY "%s" EXISTS', $selector
            ));
        }
        if(false!==strpos($result->text(),$match)){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content "%s"',
                $selector,$match
            ));
        }
        $this->assertContains($selector, $result->text());
    }        

    /**
     * Assert against DOM selection; node should match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern Pattern that should be contained in matched nodes
     */
    public function assertElementContentRegex($path, $pattern)
    {
        $this->validateWebTypeAssert();
        $result = $this->filter($path);
        if($result->count() == 0){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $path
            ));
        }
        if(!preg_match($pattern, $result->text())){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node denoted by "%s" CONTAINS content MATCHING "%s", actual content is "%s"',
                $path, $pattern, $result->text()
            ));
        }
        $this->assertTrue((boolean) preg_match($pattern, $result->text()));
    }
    
    /**
     * Assert against DOM selection; node should NOT match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern pattern that should NOT be contained in matched nodes
     */
    public function assertNotElementContentRegex($selector, $pattern)
    {
        $this->validateWebTypeAssert();
        
        $result = $this->filter($selector);
        if($result->count() == 0){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $selector
            ));
        }
        if(preg_match($pattern, $result->text())){
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY "%s" DOES NOT CONTAIN content MATCHING "%s", actual content is "%s"',
                $selector, $pattern,$result->text()
            ));
        }
        $this->assertFalse((boolean) preg_match($pattern, $result->text()));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager($name=null)
    {
        //if(!class_exists('Doctrine\ORM\EntityManager')){
        //    throw new \Exception('You have to install and configure doctrine ORM first.');
        //}
        $c = static::$kernel->getContainer();
        if(!$c->has('doctrine')){
            throw new \LogicException('Can not get Entity Manager, be sure that you have Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle() registered in AppKernel::registerBundles().');
        }
        $em = static::$kernel->getContainer()->get('doctrine')->getManager($name);
        return $em;
    }
    
    /**
     * Simulate http authentication
     * @param string $username
     * @param string $password
     */
    public function logIn($username="admin",$password="admin")
    {        
        $this->getClient()->setServerParameters(array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW' => $password,
        ));
    }
    
    /**
     * Get form by selector.
     * 
     * Selector can be in format form[name="product_form"]
     * or by form value (Save, Submit, etc)
     * @return \Symfony\Component\DomCrawler\Form A form Instance
     */
    public function getForm($selector)
    {
        $this->validateWebTypeAssert();
        
        $form =null;
        try{
            $c = $this->getCrawler()->filter($selector);
            return $this->getCrawler()->selectButton($selector)->form();
        }catch(\Exception $e){            
        }
        
        $c = $this->getCrawler()->filter($selector);
        if($c->count()>=1){
            return $c->form();
        }
        
        $c = $this->getCrawler()->filter('form[name="'.$selector.'"]');
        if($c->count()>=1){
            return $c->form();
        }
        throw new \Exception('Can not find form with "'.$selector.'"');
        return $form;
    }
    
    /**
     * 
     * @param \Symfony\Component\DomCrawler\Form $form
     * @param string    $method     The Request Method
     */
    public function submitForm(Form $form,$method="POST")
    {
        $this->getClient()->request($method, $form->getUri(),$form->getPhpValues());
    }
    
    /**
     * Reset client with the fresh new client
     */
    public function refreshClient()
    {        
        $this->client->restart();
        $this->is_open = false;        
    }
    
    /**
     * Remove entity from database
     * 
     * @param string    $entityName AcmeDemoBundle:Product
     * @param string    $col        name
     * @param string    $key        Acme Demo Product
     */
    public function removeEntity($entityName,$col,$key)
    {
        $em = $this->getEntityManager();
        
        $data = $em->getRepository($entityName)->findBy(array(
            $col=>$key,
        ));
        
        foreach($data as $entity){
            if(is_object($entity)){
                $em->remove($entity);
                $em->flush();
            }
        }
    }
    
    /**
     * Generates a URL or path for a specific route based on the given parameters.
     *
     * Parameters that reference placeholders in the route pattern will substitute them in the
     * path or host. Extra params are added as query string to the URL.
     *
     * When the passed reference type cannot be generated for the route because it requires a different
     * host or scheme than the current one, the method will return a more comprehensive reference
     * that includes the required params. For example, when you call this method with $referenceType = ABSOLUTE_PATH
     * but the route requires the https scheme whereas the current scheme is http, it will instead return an
     * ABSOLUTE_URL with the https scheme and the current host. This makes sure the generated URL matches
     * the route in any case.
     *
     * If there is no route with the given name, the generator must throw the RouteNotFoundException.     
     * @param   string  $name
     * @param   array   $parameters
     * @param   array   $referenceType The type of reference to be generated (one of the constants: UrlGeneratorInterface: ABSOLUTE_URL, ABSOLUTE_PATH, RELATIVE_PATH, NETWORK_PATH)
     * @return  string  The generated URL
     */
    public function generateUrl($name,array $parameters=array(),$referenceType=false)
    {
        //$router = new \Symfony\Component\Routing\Generator\UrlGenerator;  
        $router = $this->getClient()->getKernel()->getContainer()->get('router');
        return $router->generate($name,$parameters,$referenceType);
    }
}