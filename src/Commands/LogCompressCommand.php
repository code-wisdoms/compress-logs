<?php

namespace Codewisdoms\CompressLogs\Commands;

use Illuminate\Console\Command;

class LogCompressCommand extends Command
{
    protected $signature = 'logs:compress';

    protected $description = 'Compress logs of Laravel';

    public function handle()
    {
        self::processDir(storage_path('logs'));
        return 0;
    }
    private static function processDir($path)
    {
        $files = scandir($path);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..', '.gitkeep', '.gitignore', 'laravel.log', sprintf('laravel-%s.log', date('Y-m-d'))]) || preg_match('@.gz$@', $file)) {
                if (!in_array($file, ['.', '..']) && is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    self::processDir($path . DIRECTORY_SEPARATOR . $file);
                }
                continue;
            }
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::processDir($file);
                continue;
            }
            if (self::compress($file)) {
                @unlink($file);
            }
        }
    }
    private static function compress($source, ?int $level = null)
    {
        if (is_null($level)) {
            $level = config('log-compress.compression', 9);
        }
        $dest = "$source.gz";
        $mode = "wb$level";
        try {
            if (!$fp_out = gzopen($dest, $mode)) {
                throw new \Exception('Unable to gzopen');
            }
            if (!$fp_in = fopen($source, 'rb')) {
                throw new \Exception('Unable to fopen');

            }
            while (!feof($fp_in)) {
                gzwrite($fp_out, fread($fp_in, 1024 * 512));
            }
        } catch (\Throwable $e) {
            return false;
        } finally {
            fclose($fp_in);
            gzclose($fp_out);
        }
        return $dest;
    }
}
