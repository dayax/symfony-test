Symfony Test
=====
Symfony WebTestCase class with advanced assertion

Build Status
----
* Master: [![Build Status](https://secure.travis-ci.org/dayax/symfony-test.png?branch=master)](http://travis-ci.org/dayax/symfony-test)
* Develop: [![Build Status](https://secure.travis-ci.org/dayax/symfony-test.png?branch=develop)](http://travis-ci.org/dayax/symfony-test)


Installation
----
Include dayax/symfony test to your composer.json file.
    
    // composer.json
    "require-dev":{
        "dayax/symfony-test": "@dev"
    }

Basic Usage
----
    
    use dayax\symfony\test\WebTestCase;

    class MyTestCase extends WebTestCase
    {
        public function testIndexAction()
        {
            $this->open("/");
            $this->assertResponseStatus(200);
            $this->assertController("MyController");
            $this->assertAction("MyAction");
        }
    }

Assertion List
-----
    
    $this->open("/complete_url_to_test");

    // controller
    $this->assertController("DemoController");
    $this->assertController("demo");
    
    
    // action
    $this->assertAction("indexAction");
    $this->assertAction("index");

    
    //response status
    $this->assertResponseStatus(200);
    $this->assertNotResponseStatus(500);

    
    // http header
    $this->assertHasResponseHeader('Content-Type');
    $this->assertNotHasResponseHeader('Unknown-Header');

    $this->assertResponseHeaderContains("Content-Type","text/html; charset=UTF-8");
    $this->assertNotResponseHeaderContains("Content-Type","text/json");
    
    $this->assertResponseHeaderRegex('Content-Type','#charset#');
    $this->assertResponseHeaderRegex('Content-Type','#text#');
    $this->assertResponseHeaderRegex('Content-Type','#html#');

    $this->assertNotResponseHeaderRegex('Content-Type','#json#');

    
    // redirect
    $this->open('/redirect');
    $this->assertRedirect();

    $this->open('/');
    $this->assertNotRedirect();


    // element
    $this->open('/');
    $this->assertHasElement('h1');
    $this->assertElementContains('h1', 'Header h1');
    $this->assertElementContentRegex('h1', '#Header#');
    $this->assertNotElementContentRegex('h1', '#foo#');


Form Helper
----
    
    $this->open("/new_data");
    
    $form = $this->getForm("Save");
    $form = $this->getForm("#form_html_id");
    $form = $this->getForm("form_name");
    $form->setValues(array(
        "form[firstname]"=>"Hello World",
    ));
    $this->submitForm($form);
    
    $this->assertResponseStatus(200);
    $this->assertElementContains(".flash","Form Saved!");