<?php

namespace App\Console\Commands;

use App\Console\EnkinTrait;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Console\Command;

class EnkinLog extends Command
{
    use EnkinTrait;
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

        $this->driver->quit();
        return Command::SUCCESS;
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
