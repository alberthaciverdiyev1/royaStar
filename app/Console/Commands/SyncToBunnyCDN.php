<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncToBunnyCDN extends Command
{
    protected $signature = 'storage:sync-to-bunnycdn
        {--disk= : Source disk (default: public)}
        {--dir= : Directory to sync, e.g. "questions" (default: all)}';

    protected $description = 'Sync existing local storage files to BunnyCDN';

    public function handle(): int
    {
        $sourceDisk = $this->option('disk') ?: 'public';
        $targetDir = $this->option('dir');

        $source = Storage::disk($sourceDisk);
        $target = Storage::disk('bunnycdn');

        $files = $source->allFiles($targetDir);

        if (empty($files)) {
            $this->warn('No files found to sync.');

            return Command::SUCCESS;
        }

        $this->info('Found ' . count($files) . ' files. Starting sync...');

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $synced = 0;
        $skipped = 0;

        foreach ($files as $file) {
            if ($target->exists($file)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $contents = $source->get($file);
            $mimeType = $source->mimeType($file);

            $target->put($file, $contents, ['mimetype' => $mimeType]);

            $synced++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Status', 'Count'],
            [
                ['Synced', $synced],
                ['Skipped (already exists)', $skipped],
            ]
        );

        return Command::SUCCESS;
    }
}
