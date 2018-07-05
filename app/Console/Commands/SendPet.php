<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Pet;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SendPet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pet:send {users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pet to some users';

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
        $userIds = $this->argument('users');
        $userIds = explode(',', $userIds);

        $petModel = new Pet();
        foreach ($userIds as $v) {
            $petId = $petModel->createPet(
                array(
                    'ownerId' => $v,
                    'type' => array_rand(Config::get('constants.PETS_OPTIONS')),
                    'expired_at' => Carbon::now(),
                    'on_sale' => 1,
                    'sp' => Config::get('constants.PET_START_PRICE'),
                    'fp' => Config::get('constants.PET_FINAL_PRICE'),
                    'attr4' => rand(1, 5)
                )
            );
            if (!$petId) {
                Log::error('generate pet error! ', $resp);
            } else {
                Log::info('send pet #' . $petId . ' to user ' . $v . ' success! ');
            }
        }



    }
}
