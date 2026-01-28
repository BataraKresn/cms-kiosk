<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

class MinioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override MinIO disk URL generation to use public URL
        Storage::extend('minio-public', function ($app, $config) {
            $client = new S3Client([
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region' => $config['region'],
                'version' => 'latest',
                'bucket_endpoint' => false,
                'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? true,
                'endpoint' => $config['endpoint'],
            ]);

            $adapter = new AwsS3V3Adapter(
                $client,
                $config['bucket'],
                $config['prefix'] ?? '',
                new \League\Flysystem\AwsS3V3\PortableVisibilityConverter()
            );

            $filesystem = new Filesystem($adapter, $config);
            
            // Override URL generation
            $originalGetUrl = \Closure::bind(function ($path) use ($config) {
                $publicUrl = rtrim($config['url'] ?? '', '/');
                return $publicUrl . '/' . ltrim($path, '/');
            }, null, null);

            return new \Illuminate\Filesystem\FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}
