<?php

namespace TallStackUi\Foundation\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use TallStackUi\Foundation\Support\Icons\IconGuide;
use ZipArchive;

use function Laravel\Prompts\spin;

class SetupIconsCommand extends Command
{
    private const PATH = __DIR__.'/../../resources/views/components/icon/';

    public $description = 'Set up different custom icons.';

    public $signature = 'tallstackui:icons {--force : Install icons even when the icons are already installed}';

    protected ?Collection $data = null;

    /** @throws Exception */
    public function handle(): void
    {
        $this->data = collect();

        if (str_contains(__ts_configuration('icons.type'), 'custom:')) {
            $this->components->error('You are using custom icons. This command has no effect with custom icons.');

            return;
        }

        if (($result = spin(fn () => $this->setup(), 'Setting up...')) !== true) {
            $this->components->error($result);

            return;
        }

        if (($result = spin(fn () => $this->download(), 'Downloading...')) !== true) {
            $this->components->error($result);

            return;
        }

        spin(fn () => Process::run('php artisan optimize:clear'), 'Cleaning up ...');

        $type = $this->data->get('type');

        $this->components->info('The icons ['.$type.'] are successfully installed.');
    }

    private function download(): string|bool
    {
        $response = Http::get(sprintf('https://github.com/tallstackui/icons/raw/main/%s/files.zip', $this->data->get('type')));

        if ($response->failed()) {
            return 'Failed to download the .zip file.';
        }

        $temp = Str::random();
        $file = storage_path('app/'.$temp.'.zip');
        file_put_contents($file, $response->body());

        $zip = new ZipArchive;

        if (! $zip->open($file)) {
            return 'Failed to extract the .zip file.';
        }

        $extract = storage_path('app/'.$temp);
        $zip->extractTo($extract);
        $zip->close();

        $this->prepare($file, $extract);

        return true;
    }

    private function flush(): void
    {
        if (config('tallstackui.icons.flush', true) === false) {
            return;
        }

        foreach (
            collect(IconGuide::Supported)
                ->filter(fn (string $type) => ! in_array($type, ['heroicons', $this->data->get('type')]))
                ->toArray() as $type
        ) {
            // Flushing the other unused icons to
            // avoid the existence of unused files.
            File::deleteDirectory(self::PATH.$type);
        }
    }

    private function prepare(string $file, string $extract): void
    {
        if ($this->option('force')) {
            File::deleteDirectory(self::PATH.$this->data->get('type'));
        }

        File::copyDirectory($extract, self::PATH.$this->data->get('type'));
        File::deleteDirectory($extract);
        unlink($file);

        $this->flush();
    }

    /** @throws Exception */
    private function setup(): bool|string
    {
        $config = config('tallstackui');
        $type = data_get($config, 'icons.type');
        $style = data_get($config, 'icons.style');

        if (blank(data_get($config, 'icons')) || blank($type) || blank($style)) {
            return 'Wrong configuration file. Please, review the docs.';
        }

        if (! IconGuide::supported($type)) {
            return 'Unsupported icon type. Please, review the configuration file.';
        }

        if (! in_array($style, IconGuide::styles($type))) {
            return 'Unsupported icon style. Please, review the configuration file.';
        }

        if (! $this->option('force') && is_dir(self::PATH.$type)) {
            $this->flush();

            return 'The icons selected ['.$type.'] are already installed.';
        }

        $this->data->put('type', $type);
        $this->data->put('style', $style);

        return true;
    }
}
