<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnkinLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enkin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enkin log';

    protected ChromeDriver $driver;

    const BASE_URL = "https://etgroup.enkinlab.net";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->init();

        $login = $this->login(env("ENKIN_USERNAME"), env("ENKIN_PASSWORD"));

        if (!$login){
            $this->error("login fail. check your account");
            return Command::FAILURE;
        }

        $openModal = $this->openModal();

        if (!$openModal){
            $this->error("open model fail");
            return Command::FAILURE;
        }

        $startWork = $this->startWork();

        if (!$startWork){
            $this->error("start working fail");
            return Command::FAILURE;
        }

        $this->info("all good. start working successfully");
        return Command::SUCCESS;
    }

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
        $this->driver->get(self::BASE_URL."/login");

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

        if ($this->driver->getCurrentURL() === self::BASE_URL . "/app#top/myhome"){
            return true;
        }

        return false;
    }

    public function openModal(): bool
    {
        sleep(1);
        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#ext-work-start-end-view-1 button.x-button-el'))
            );

            $button = $this->driver->findElement(WebDriverBy::cssSelector('#ext-work-start-end-view-1 button.x-button-el'));

            if ($button->isEnabled()){
                $button->click();
            }else{
                $this->error("button open modal is disable");
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return false;
        }

        return true;
    }

    public function startWork(): bool
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
                $this->error("button start work is disable");
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return false;
        }

        return true;
    }
}
