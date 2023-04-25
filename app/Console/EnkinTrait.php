<?php

namespace App\Console;

use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Facades\Log;

Trait EnkinTrait{

    protected ChromeDriver $driver;

    protected string $baseURL = "https://etgroup.enkinlab.net";

    public function init(): void
    {
        $desiredCapabilities = DesiredCapabilities::chrome();

        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox'
        ]);
        $chromeOptions->setExperimentalOption('w3c', false);

        $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        $desiredCapabilities->setCapability( 'loggingPrefs', [
            'browser' => 'ALL',
            'performance' => 'ALL',
        ]);

        putenv('WEBDRIVER_CHROME_DRIVER='.base_path('chromedriver'));
        $this->driver = ChromeDriver::start($desiredCapabilities);
        $devTools = new ChromeDevToolsDriver($this->driver);
        $devTools->execute('Performance.enable');
    }

    public function login($username, $password): bool
    {
        $this->driver->get($this->baseURL."/login");

        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::name('loginId'))
            );

            $this->driver->wait()->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::name('password'))
            );

            $this->driver->findElement(WebDriverBy::name('loginId'))
                ->sendKeys($username);

            $this->driver->findElement(WebDriverBy::name('password'))
                ->sendKeys($password)
                ->submit();

            sleep(5);
        } catch (\Exception $e) {
            Log::info($e->getMessage());

            return false;
        }

        if ($this->driver->getCurrentURL() === $this->baseURL. "/app#top/myhome"){
            return true;
        }

        return false;
    }

    public function openModal($mode): bool
    {
        sleep(1);
        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ext-work-start-end-view-1 button.x-button-el'))
            );

            $buttons = $this->driver->findElements(WebDriverBy::cssSelector('#ext-work-start-end-view-1 button.x-button-el'));


            if (!empty($buttons[$mode]) && $buttons[$mode]->isEnabled()){
                $buttons[$mode]->click();
            }else{
                $this->error("button open modal is disable");
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return false;
        }

        return true;
    }

    public function action(): bool
    {
        sleep(1);
        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ext-work-input-dialog-1 button.x-button-el'))
            );

            $button = $this->driver->findElement(WebDriverBy::cssSelector('#ext-work-input-dialog-1 button.x-button-el'));

            if ($button->isEnabled()){
                $button->click();
            }else{
                $this->error("button log is disable");
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return false;
        }

        return true;
    }

    public function work(){
        $openModal = $this->openModal(EnkinConst::WORK);

        if ($openModal){
            return $this->action();
        }
    }

    public function leave(){
        $openModal = $this->openModal(EnkinConst::LEAVE);

        if ($openModal){
            return $this->action();
        }
    }
}
