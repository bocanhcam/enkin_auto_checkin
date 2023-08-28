<?php

namespace App\Console\Commands;

use App\Console\EnkinTrait;
use App\Models\BotRequest;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Console\Command;

class EnkinHandleBotService extends Command
{
    use EnkinTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enkin:bot {type} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle bot service';

    protected array $prefixMessageByType = [
        BotRequest::TYPE_LATE_30M => 'Đã nhận lệnh xin đến muộn 30p của bạn hiền vào ngày ',
        BotRequest::TYPE_LATE_1h => 'Đã nhận lệnh xin đến muộn 1h của bạn hiền vào ngày ',
        BotRequest::TYPE_OFF => 'Đã nhận lệnh xin nghỉ của bạn hiền vào ngày ',
        BotRequest::TYPE_MANUAL => 'Đã nhận lệnh tự log của bạn hiền vào ngày ',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $date = $this->argument('date');
        $formatDate = Carbon::parse($date)->format('d/m/Y');

        if ($type != BotRequest::TYPE_MANUAL){
            $logForm = $this->logForm($type, $date);

            if ($logForm){
                $this->output->write('Log form ngày '.$formatDate.' thành công.', true);
            }
        }

        try {
            BotRequest::create([
                'type' => $type,
                'date' => $date,
            ]);
        } catch (\Exception $exception) {
            $this->output->write($exception->getMessage(), true);
            return Command::FAILURE;
        }

        $this->output->write($this->prefixMessageByType[$type] . $formatDate, true);

        return Command::SUCCESS;
    }

    public function logForm($type, $date): bool
    {
        $this->init();

        $url = 'https://docs.google.com/forms/d/e/1FAIpQLSfVpWs8K1q0-geCn09p29QzJmlEwXshKPqaQdN4HeMzWk2pUg/viewform?pli=1&pli=1';
        $this->driver->get($url);

        try {
            $logStep1 = $this->logFormStep1($type);

            if (!$logStep1){
                $this->output->write('Log form lỗi bước 1', true);
                return false;
            }

            sleep(2);

            $logStep2 = $this->logFormStep2($type, $date);

            if (!$logStep2){
                $this->output->write('Log form lỗi bước 2', true);
                return false;
            }

            sleep(2);

            $logStep3 = $this->logFormStep3();
            if (!$logStep3){
                $this->output->write('Log form lỗi bước 3', true);
                return false;
            }

        } catch (\Exception $e) {
            $this->output->write($e->getMessage(), true);
            $this->error($e->getMessage());

            return false;
        }

        return true;
    }

    private function logFormStep1($type): bool
    {
        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('input[type="email"]'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//input[@type="text"]'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i14'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//textarea'))
            );
            if ($type == BotRequest::TYPE_OFF){
                $this->driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i25'))
                );
            }else{
                $this->driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i28'))
                );
            }
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//div[@role="button"]'))
            );

            $this->driver->findElement(WebDriverBy::cssSelector('input[type="email"]'))->sendKeys('hora.alien@gmail.com');
            $this->driver->findElement(WebDriverBy::xpath('//input[@type="text"]'))->sendKeys('Dương Việt Hoàng');
            $this->driver->findElement(WebDriverBy::cssSelector('#i14'))->click();
            $this->driver->findElement(WebDriverBy::xpath('//textarea'))->sendKeys('Bình thường');
            if ($type == BotRequest::TYPE_OFF){
                $this->driver->findElement(WebDriverBy::cssSelector('#i25'))->click();
            }else{
                $this->driver->findElement(WebDriverBy::cssSelector('#i28'))->click();
            }
            $this->driver->findElement(WebDriverBy::xpath('//div[@role="button"]'))->click();

        }catch (\Exception $e){
            $this->output->write($e->getMessage(), true);
            $this->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function logFormStep2($type, $date): bool
    {
        try {
            if ($type == BotRequest::TYPE_OFF){
                $this->logFormOff($date);
            }else{
                $this->logFormLate($type, $date);
            }
        }catch (\Exception $e){
            $this->output->write($e->getMessage(), true);
            $this->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function logFormLate($type, $date): bool
    {
        try {
            $date = Carbon::parse($date)->format('d/m/Y');

            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//input[@type="text"]'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//div[@role="button"]'))
            );

            if ($type == BotRequest::TYPE_LATE_30M){
                $this->driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i13'))
                );
            }else{
                $this->driver->wait()->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i16'))
                );
            }

            $this->driver->findElement(WebDriverBy::xpath('//input[@type="text"]'))->sendKeys($date);

            if ($type == BotRequest::TYPE_LATE_30M){
                $this->driver->findElement(WebDriverBy::cssSelector('#i13'))->click();
            }else{
                $this->driver->findElement(WebDriverBy::cssSelector('#i16'))->click();
            }

            $buttons = $this->driver->findElements(WebDriverBy::xpath('//div[@role="button"]'));
            // button 0:back 1:submit
            $buttons[1]->click();
        }catch (\Exception $e){
            $this->output->write($e->getMessage(), true);
            $this->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function logFormOff($date): bool
    {
        try {
            $date = Carbon::parse($date)->format('d/m/Y');

            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//input[@type="text"]'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i19'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//div[@role="button"]'))
            );

            $this->driver->findElement(WebDriverBy::xpath('//input[@type="text"]'))->sendKeys($date);
            $this->driver->findElement(WebDriverBy::cssSelector('#i19'))->click();
            $buttons = $this->driver->findElements(WebDriverBy::xpath('//div[@role="button"]'));

            // button 0:back 1:submit
            $buttons[1]->click();
        }catch (\Exception $e){
            $this->output->write($e->getMessage(), true);
            $this->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function logFormStep3(): bool
    {
        try {
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#i9'))
            );
            $this->driver->wait()->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::xpath('//div[@role="button"]'))
            );

            $this->driver->findElement(WebDriverBy::cssSelector('#i9'))->click();
            $buttons = $this->driver->findElements(WebDriverBy::xpath('//div[@role="button"]'));

            // button 0:back 1:submit
            // $buttons[1]->click();
        }catch (\Exception $e){
            $this->output->write($e->getMessage(), true);
            $this->error($e->getMessage());
            return false;
        }

        return true;
    }
}
