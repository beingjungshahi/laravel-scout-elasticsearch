<?php

declare(strict_types=1);

namespace Matchish\ScoutElasticSearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine;

final class ElasticSearchServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(Client::class, function () {
            return ClientBuilder::create()->setHosts([config('elasticsearch.host')])->build();
        });

        $this->app->bind(
            'Matchish\ScoutElasticSearch\ElasticSearch\HitsIteratorAggregate',
            'Matchish\ScoutElasticSearch\ElasticSearch\EloquentHitsIteratorAggregate'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        resolve(EngineManager::class)->extend('elasticsearch', function () {
            return new ElasticSearchEngine(
                ClientBuilder::create()
                    ->create()
                    ->setHosts(config('scout.elasticsearch.hosts'))
                    ->build(),
                config('scout.elasticsearch.index')
            );
        });
        
        $this->publishes([
            __DIR__.'/../config/elasticsearch.php' => config_path('elasticsearch.php'),
        ], 'config');
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return [Client::class];
    }
}
