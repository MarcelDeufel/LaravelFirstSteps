<?php

namespace Tests\Browser;
namespace Facebook\WebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Spatie\Async\Process;
    require_once('vendor/autoload.php');
    use Tests\DuskTestCase;
    use Illuminate\Foundation\Testing\DatabaseMigrations;



class MyFirstDuskTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    /*public function testExample()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                    ->type('a', 3)
                    ->type('b', 1)
                    ->press('Execute')
                    ->assertPathIs('/calc')
                    ->assertSee('4');
        });
    }*/
    //function scrapeData($name, $postalCode, $phoneNumber, $adress, $city)
    public function testExample()
    {
        // Setting Character Set
        set_time_limit(0);

        $host = 'http://localhost:4444/wd/hub';
		
		// open a connection
        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($host, $capabilities, 60000, 60000);
        //$driver->get('https://web2.cylex.de/firma-home/jonny-m--club-koenigstrasse-fitnessstudio-stuttgart-11064810.html');
        //$driver->get('https://web2.cylex.de/firma-home/alphatier-gmbh-11294606.html');
        $driver->get('https://www.stadtbranchenbuch.com/');

        $this->setNameInBrowser($driver, 'Alphatier GmbH');
        $this->setAdress($driver, '76133');
        $this->sendRequest($driver);
        $driver->wait(250, 1000)->until (
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('div.mui-panel'))
            );
        $result = $driver->findElement(WebDriverBy::tagName('h1'))->getText();
        if($result != 'Keine Treffer!')
        {
            $href = $this->getElementOfList($driver, 'ALPHATIER GmbH');
            if($href != '' || $href != null){
                $driver->get($href);
                $driver->wait(250, 1000)->until (
                    WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('div.business__details'))
                    );
                $arrayWithResults = $this->getDetails($driver);
                $originalArray = array();
                $originalArray['name'] = 'ALPHATIER GmbH';
                $originalArray['street'] = 'Karlstr. 45a';
                $originalArray['phonenumber'] = '0721 41518';
                $originalArray['postalCode'] = '76133';
                $originalArray['city'] = 'Karlsruhe';
                $this->assertEquals($originalArray['name'], $arrayWithResults['name']);
                $this->assertEquals($originalArray['street'], $arrayWithResults['street']);
                $this->assertEquals($originalArray['postalCode'], $arrayWithResults['postalCode']);
                $this->assertEquals($originalArray['city'], $arrayWithResults['city']);
                $this->assertEquals($originalArray['phonenumber'], $arrayWithResults['phonenumber']);
                $this->compareData($originalArray, $arrayWithResults);
                $driver->quit();
            }
        } else {
            echo 'No data was found with these search criteria!';
        }
        $driver->quit();
    }
    public function setNameInBrowser($driver, $name){
        //get input field for name and fill name into it
        $searchForNameElement = $driver->findElement(WebDriverBy::id('sQuery'));
        $searchForNameElement->click();
        $searchForNameElement->sendKeys($name);
    }
    public function setAdress($driver, $adress){
        //get input field for adress and fill adress into it
        $searchForNameElement = $driver->findElement(WebDriverBy::id('sLocation'));
        $searchForNameElement->click();
        $searchForNameElement->sendKeys($adress);
    }
    public function sendRequest($driver){
        //click the search button
        $searchButtonElement = $driver->findElement(WebDriverBy::id('submit'));
        $searchButtonElement->click();
    }
    public function getHeader2($driver){
        $headerText = $driver->findElement(WebDriverBy::tagName('h2'))->getText();
        return $headerText;
    }
    public function getElementOfList($driver, $name){
        $companyContainer = $driver->findElement(WebDriverBy::id('serp-listing-wrapper'));
        $companies = $companyContainer->findElements(WebDriverBy::xpath('//div[@data-source="eigenBestand"]'));
        if($companies == null){
            echo 'No companyContianerFound';
        }
        foreach($companies as $item){
            $possibleResultAnkerName = $item->findElement(WebDriverBy::xpath('//div[@class="mui-col-md-4 mui-col-sm-4 mui-col-xs-8 is_gray"]/a[1]'));
            if($possibleResultAnkerName == null){
                echo 'No ankerName found!';
            }
            $possibleResultName = $possibleResultAnkerName->getText();
            if($possibleResultName == null || $possibleResultName == ''){
                echo 'No name found!';
            }

            if($possibleResultName == $name){
                $href = $possibleResultAnkerName->getAttribute('href');
                return $href;
            }
        }
        return '';
    }
    public function getDetails($driver){
        $root = $driver->findElement(WebDriverBy::xpath('//div[@class="business__details"]'));
        $arrayToReturn = array();
        $arrayToReturn['name'] = $driver->findElement(WebDriverBy::tagName('h1'))->getText();
        $arrayToReturn['street'] = $root->findElement(WebDriverBy::xpath('//span[@itemprop="streetAddress"]'))->getText();
        $arrayToReturn['postalCode'] = $root->findElement(WebDriverBy::xpath('//span[@itemprop="postalCode"]'))->getText();
        $arrayToReturn['city'] = $root->findElement(WebDriverBy::xpath('//span[@itemprop="addressLocality"]'))->getText();
        $arrayToReturn['phonenumber'] = $root->findElement(WebDriverBy::xpath('//span[@itemprop="telephone"]'))->getText();
        return $arrayToReturn;
        }
    public function compareData($originalArray, $arrayWithResults) 
    {
        if($originalArray['name'] != $arrayWithResults['name'])
        {
            echo 'Detected a difference in name! Found name: ' . $arrayWithResults['name'] . ', but expected name to be:  ' . $originalArray['name'];
        }
        if($originalArray['street'] != $arrayWithResults['street'])
        {
            echo 'Detected a difference in street! Found street: ' . $arrayWithResults['street'] . ', but expected street to be:  ' . $originalArray['street'];
        }
        if($originalArray['city'] != $arrayWithResults['city'])
        {
            echo 'Detected a difference in city! Found city: ' . $arrayWithResults['city'] . ', but expected city to be:  ' . $originalArray['city'];
        }
        if($originalArray['phonenumber'] != $arrayWithResults['phonenumber'])
        {
            echo 'Detected a difference in phonenumber! Found phonenumber: ' . $arrayWithResults['phonenumber'] . ', but expected phonenumber to be:  ' . $originalArray['phonenumber'];
        }
    }
}