<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Pet;
use Illuminate\Support\Facades\Log;

class CleanPet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pet:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear up expired pets';

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
     *
     * @throws \Exception
     */
    public function handle()
    {
        $petModel = new Pet();
        $counts = $petModel->delOutExpPets();
        if ($counts)
            Log::info('clean ' . $counts . ' pets success');
    }
}
