<?php

namespace App\Console\Commands;

use App\Models\BotRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnkinHandleBotService extends Command
{
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
        BotRequest::TYPE_OFF => 'Đã nhận lệnh xin nghỉ | tự log của bạn hiền vào ngày ',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $date = $this->argument('date');

        try {
            BotRequest::create([
                'type' => $type,
                'date' => $date,
            ]);
        }catch (\Exception $exception){
            $this->output->write($exception->getMessage());
            return Command::FAILURE;
        }

        $formatDate = Carbon::parse($date)->format('d/m/Y');
        $this->output->write($this->prefixMessageByType[$type] . $formatDate);

        return Command::SUCCESS;
    }
}
