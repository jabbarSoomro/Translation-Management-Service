<?php

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTranslations extends Command
{
    protected $signature = 'translations:seed {count=100000}';

    protected $description = 'Seed the database with translations for performance testing';

    private array $locales = ['en', 'fr', 'es', 'de', 'it', 'pt', 'ru', 'zh', 'ja', 'ar'];

    private array $tagNames = ['web', 'mobile', 'desktop', 'admin', 'public', 'email', 'sms'];

    private array $keyPrefixes = [
        'auth', 'validation', 'pagination', 'passwords', 'button',
        'menu', 'error', 'success', 'warning', 'info', 'common',
        'form', 'user', 'admin', 'dashboard', 'settings', 'profile',
    ];

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        $this->info("Starting to seed {$count} translations...");

        $this->seedTags();

        $this->seedTranslations($count);

        $this->info("Successfully seeded {$count} translations!");

        return Command::SUCCESS;
    }

    private function seedTags(): void
    {
        $this->info('Creating tags...');

        foreach ($this->tagNames as $tagName) {
            Tag::firstOrCreate(['name' => $tagName]);
        }
    }

    private function seedTranslations(int $count): void
    {
        $batchSize = 1000;
        $batches = ceil($count / $batchSize);

        $bar = $this->output->createProgressBar($batches);
        $bar->start();

        $tags = Tag::all();
        $tagIds = $tags->pluck('id')->toArray();

        for ($batch = 0; $batch < $batches; $batch++) {
            $currentBatchSize = min($batchSize, $count - ($batch * $batchSize));

            $translations = [];
            $timestamp = now();

            for ($i = 0; $i < $currentBatchSize; $i++) {
                $prefix = $this->keyPrefixes[array_rand($this->keyPrefixes)];
                $suffix = $this->generateRandomString(8);
                $key = "{$prefix}.{$suffix}";
                $locale = $this->locales[array_rand($this->locales)];

                $translations[] = [
                    'key' => $key,
                    'locale' => $locale,
                    'value' => $this->generateTranslationValue($key),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('translations')->insert($translations);

            $insertedIds = DB::table('translations')
                ->where('created_at', $timestamp)
                ->orderBy('id', 'desc')
                ->limit($currentBatchSize)
                ->pluck('id')
                ->toArray();

            $pivotData = [];
            foreach ($insertedIds as $translationId) {
                $numTags = rand(1, 3);
                $selectedTags = array_rand(array_flip($tagIds), $numTags);

                if (! is_array($selectedTags)) {
                    $selectedTags = [$selectedTags];
                }

                foreach ($selectedTags as $tagId) {
                    $pivotData[] = [
                        'translation_id' => $translationId,
                        'tag_id' => $tagId,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }

            if (! empty($pivotData)) {
                DB::table('translation_tag')->insert($pivotData);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function generateRandomString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $result;
    }

    private function generateTranslationValue(string $key): string
    {
        $words = [
            'welcome', 'hello', 'goodbye', 'thank', 'please', 'yes', 'no',
            'submit', 'cancel', 'save', 'delete', 'edit', 'create', 'update',
            'user', 'password', 'email', 'login', 'logout', 'register',
            'profile', 'settings', 'dashboard', 'admin', 'home', 'about',
        ];

        $numWords = rand(2, 6);
        $selectedWords = [];

        for ($i = 0; $i < $numWords; $i++) {
            $selectedWords[] = $words[array_rand($words)];
        }

        return ucfirst(implode(' ', $selectedWords));
    }
}
