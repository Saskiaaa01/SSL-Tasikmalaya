<?php

namespace App\Console\Commands;

use App\Jobs\CheckDomainSslJob;
use App\Models\Domain;
use Illuminate\Console\Command;

class CheckSslCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssl:check-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches daily SSL/TLS certificate check jobs for all active domains';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting daily SSL certificate checks...');

        $domains = Domain::where('status', 'active')->get();

        if ($domains->isEmpty()) {
            $this->warn('No active domains found to check.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($domains as $domain) {
            CheckDomainSslJob::dispatch($domain);
            $count++;
        }

        $this->info("Successfully dispatched SSL check jobs for {$count} active domains.");

        return Command::SUCCESS;
    }
}
