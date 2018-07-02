<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic generate one match';

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
        $controller = app()->make('App\Http\Controllers\MatchController');
        $req  = app()->call([$controller, 'autoMatch'], []);
        if ($req) {
            $resp = $req->original;
            if ($resp['code'] == 10060) {
                Log::info('generate match success! matchIds: ', $resp['matchIds']);
            } else {
                Log::error('generate match error!', $resp);
            }
            $this->line($resp['message']);
        }
    }
}
