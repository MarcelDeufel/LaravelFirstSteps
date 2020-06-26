<?php

namespace Tests\Browser;
namespace Facebook\WebDriver;
    use Facebook\WebDriver\Remote\DesiredCapabilities;
    use Facebook\WebDriver\Remote\RemoteWebDriver;
    use Spatie\Async\Process;
    require_once('vendor/autoload.php');
    use Tests\DuskTestCase;
    use Illuminate\Foundation\Testing\DatabaseMigrations;

class MySecondDuskTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testExample()
    {
        // Setting Character Set
        set_time_limit(0);

        $host = 'http://localhost:4444/wd/hub'; 
		
		// open a connection
        $capabilities = DesiredCapabilities::chrome();
        //$driver = RemoteWebDriver::create($host, $capabilities, 60000, 60000);
        //$driver->get('https://web2.cylex.de/firma-home/jonny-m--club-koenigstrasse-fitnessstudio-stuttgart-11064810.html');
        //$driver->get('https://web2.cylex.de/firma-home/alphatier-gmbh-11294606.html');
        $this->driver()->get('https://www.gelbeseiten.de/');

        $this->setNameOfInputField($driver, 'Alphatier GmbH');
        $this->setAdress($driver, '76133');
        $this->sendRequest($driver);
        $driver->wait(250, 1000)->until (WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('loadMoreGesamtzahl')));
        //$driver->manage()->timeouts()->implicitlyWait = 2;
        $result = $driver->findElement(WebDriverBy::id('loadMoreGesamtzahl'))->getText();
        if($result != '0 Einträge')
        {
            $arrayWithResults = $this->getElementOfList($driver, 'Alphatier GmbH');
            if($arrayWithResults != ''){
                $originalArray = array();
                $originalArray['name'] = 'Alphatier GmbH';
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
            
        } else{     
            echo 'No data was found with these search criteria!';
        }
        $driver->quit();
    }
    function setNameOfInputField($driver, $name){
        //get input field for name and fill name into it
        $searchForNameElement = $driver->findElement(WebDriverBy::id('what_search'));
        $searchForNameElement->click();
        $searchForNameElement->sendKeys($name);
    }
    function setAdress($driver, $adress){
        //get input field for adress and fill adress into it
        $searchForNameElement = $driver->findElement(WebDriverBy::id('where_search'));
        $searchForNameElement->click();
        $searchForNameElement->sendKeys($adress);
    }
    function sendRequest($driver){
        //click the search button
        $searchButtonElement = $driver->findElement(WebDriverBy::xpath('//button[@type="submit"]'   ));
        $searchButtonElement->click();
    }
    function getHeader2($driver){
        $headerText = $driver->findElement(WebDriverBy::tagName('h2'))->getText();
        return $headerText;
    }
    function getElementOfList($driver, $name){
        $companyContainer = $driver->findElement(WebDriverBy::id('gs_treffer'));
        $companies = $companyContainer->findElements(WebDriverBy::cssSelector('article.mod-Treffer'));
        if($companies == null){
            echo 'No companyContianerFound';
        }
        foreach($companies as $item){
            $nameOfItem = $item->findElement(WebDriverBy::tagName('h2'));
            if($name == null){
                echo 'No name found!';
            }
            $possibleResultName = $nameOfItem->getText();
            if($possibleResultName == null || $possibleResultName == ''){
                echo 'No name found!';
            }

            if($possibleResultName == $name){
                $totalAddressElement = $item->findElement(WebDriverBy::xpath('//address[@class="mod mod-AdresseKompakt"]'));
                $totalAddress = $totalAddressElement->findElement(WebDriverBy::xpath('//p[@data-wipe-name="Adresse"]'))->getText();
                $phoneNumber = $item->findElement(WebDriverBy::xpath('//p[@data-wipe-name="Kontaktdaten"]'))->getText();
                //get the right strings out of the compact string
                $stringsThatMatters = explode(', ',$totalAddress,2);
                $street = $stringsThatMatters[0];
                $postalCodeAndCity = explode(' ', $stringsThatMatters[1], 2);
                $postalCode = $postalCodeAndCity[0];
                $cityAndRest = explode(' ', $postalCodeAndCity[1], 2);
                $city = $cityAndRest[0];
            
                if($name != null){
                    $stringsToReturn['name'] = $name;
                }
                if($street != null){
                    $stringsToReturn['street'] = $street;
                }
                if($postalCode != null){
                    $stringsToReturn['postalCode'] = substr($city, 0, 5);
                    print_r($stringsToReturn['postalCode']);
                }
                if($city != null){
                    $stringsToReturn['city'] = $city;
                }
                if($phoneNumber != null){
                    $stringsToReturn['phonenumber'] = $phoneNumber;
                }
                return $stringsToReturn;
            }
        }
        return '';
    }
    function compareData($originalArray, $arrayWithResults) 
    {
        if($originalArray['name'] != $arrayWithResults['name'])
        {
            echo ' Detected a difference in name! Found name: ' . $arrayWithResults['name'] . ', but expected name to be:  ' . $originalArray['name'];
        }
        if($originalArray['street'] != $arrayWithResults['street'])
        {
            echo ' Detected a difference in street! Found street: ' . $arrayWithResults['street'] . ', but expected street to be:  ' . $originalArray['street'];
        }
        if($originalArray['city'] != $arrayWithResults['city'])
        {
            echo ' Detected a difference in city! Found city: ' . $arrayWithResults['city'] . ', but expected city to be:  ' . $originalArray['city'];
        }
        if($originalArray['phonenumber'] != $arrayWithResults['phonenumber'])
        {
            echo ' Detected a difference in phonenumber! Found phonenumber: ' . $arrayWithResults['phonenumber'] . ', but expected phonenumber to be:  ' . $originalArray['phonenumber'];
        }
    }       
}
