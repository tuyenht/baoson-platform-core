<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class I18nExport extends Command
{
    /**
     * @var string
     */
    protected $signature = 'i18n:export {--path=resources/js/locales/translations.json : Output path relative to app base}';

    /**
     * @var string
     */
    protected $description = 'Export UI translations from lang/{locale}/ui.php to React JSON bundle.';

    public function handle(Filesystem $fs): int
    {
        $supportedLocales = ['en', 'vi', 'ja', 'zh'];

        $outRel = (string) $this->option('path');
        $outPath = base_path($outRel);

        $data = [];

        foreach ($supportedLocales as $locale) {
            $uiPath = base_path("lang/{$locale}/ui.php");
            if (! $fs->exists($uiPath)) {
                $this->warn("Missing: {$uiPath} (skipped)");
                continue;
            }

            /** @var array<string, mixed> $ui */
            $ui = require $uiPath;

            // For this app we export only the "auth" namespace as a flat map (existing useI18n contract).
            $auth = $ui['auth'] ?? [];
            if (! is_array($auth)) {
                $auth = [];
            }

            $flat = [];
            foreach ($auth as $k => $v) {
                if (is_string($k) && is_string($v)) {
                    $flat[$k] = $v;
                }
            }

            ksort($flat);
            $data[$locale] = $flat;
        }

        ksort($data);

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            $this->error('Failed to encode JSON.');
            return self::FAILURE;
        }

        $dir = dirname($outPath);
        if (! $fs->exists($dir)) {
            $fs->makeDirectory($dir, 0755, true);
        }

        $fs->put($outPath, $json."\n");

        $this->info("Exported UI translations to: {$outRel}");
        $this->line('Locales: '.implode(', ', array_keys($data)));

        return self::SUCCESS;
    }
}


