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
    protected $signature = 'enkin:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enkin log work';

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

        $work = $this->work();

        if (!$work){
            $this->error("start working fail");
            return Command::FAILURE;
        }

        $this->info("all good. start working successfully");

        $this->driver->quit();
        return Command::SUCCESS;
    }
}
