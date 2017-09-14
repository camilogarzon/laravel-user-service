<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class UpdateAccountKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:update-account-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute a routine to use an external service responsible to create account_keys';

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
        User::updateAccountKeys();
    }
}
