<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoBirth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pet:birth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic born one pet';

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
        $controller = app()->make('App\Http\Controllers\PetController');
        $req  = app()->call([$controller, 'autoBirth'], []);
        $resp = $req->original;

        if ($resp['code'] == 10060) {
            Log::info('generate pet #' . $resp['petId'] . ' success! ');
        } else {
            Log::error('generate pet error! ', $resp);
        }
        $this->line($resp['message']);
    }
}
