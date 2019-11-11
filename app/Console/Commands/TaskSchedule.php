<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FileProcessorController;

class TaskSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //protected $signature = 'command:name';
    protected $signature = 'fileprocessor:incoming {trader}{partner}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fileprocessor incoming';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $trader = $this->argument('trader');
        echo 'trader : '.$trader;
         
        $partner = $this->argument('partner');
        echo 'partner : '.$partner;

        $fp = new FileProcessorController();

        $fp->boot($trader,$partner);
    }
}
