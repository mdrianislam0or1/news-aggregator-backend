<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Article;

class FetchArticles extends Command
{
    protected $signature = 'articles:fetch';
    protected $description = 'Fetch articles from various news APIs';

    public function handle()
    {
        $this->fetchFromNewsAPI();
        $this->fetchFromCurrentsAPI();
        $this->fetchFromGNewsAPI();
    }

    // private function fetchFromNewsAPI()
    // {
    //     $apiKey = env('NEWSAPI_KEY');
    //     $url = "https://newsapi.org/v2/top-headlines?apiKey={$apiKey}&country=us";

    //     try {
    //         $response = Http::get($url);

    //         if ($response->successful()) {
    //             foreach ($response['articles'] as $articleData) {
    //                 if (!empty($articleData['description'])) {
    //                     $category = $articleData['category'] ?? 'general';

    //                     Article::updateOrCreate(
    //                         ['url' => $articleData['url']],
    //                         [
    //                             'title' => $articleData['title'],
    //                             'description' => $articleData['description'],
    //                             'source' => $articleData['source']['name'] ?? 'Unknown',
    //                             'category' => $category,
    //                         ]
    //                     );
    //                 } else {
    //                     $this->warn("Skipping article due to missing description: {$articleData['url']}");
    //                 }
    //             }
    //             $this->info('Articles fetched and saved successfully from NewsAPI.');
    //         } else {
    //             $this->error("Failed to fetch articles from NewsAPI. Status code: {$response->status()}");
    //             Log::error('NewsAPI Error Response:', [
    //                 'status_code' => $response->status(),
    //                 'body' => $response->body(),
    //             ]);
    //         }
    //     } catch (\Exception $e) {
    //         $this->error("Exception occurred while fetching articles from NewsAPI: {$e->getMessage()}");
    //         Log::error('NewsAPI Exception:', [
    //             'message' => $e->getMessage(),
    //             'url' => $url,
    //         ]);
    //     }
    // }


    private function fetchFromNewsAPI()
    {
        $apiKey = env('NEWSAPI_KEY');
        $url = "https://newsapi.org/v2/top-headlines?apiKey={$apiKey}&country=us";

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                foreach ($response['articles'] as $articleData) {
                    $title = $articleData['title'] ?? null;
                    $description = $articleData['description'] ?? null;
                    $url = $articleData['url'] ?? null;
                    $source = $articleData['source']['name'] ?? 'Unknown';
                    $imageUrl = $articleData['urlToImage'] ?? null;
                    $publishedAt = $articleData['publishedAt'] ?? null;

                    if ($title && $description && $url) {
                        if ($imageUrl === null) {
                            $this->warn("Skipping article due to missing image URL: {$url}");
                            $imageUrl = 'default-image-url.png';
                        }

                        // Store article with image URL
                        Article::updateOrCreate(
                            ['url' => $url],
                            [
                                'title' => $title,
                                'description' => $description,
                                'source' => $source,
                                'category' => 'general',
                                'image_url' => $imageUrl,
                                'published_at' => $publishedAt,
                            ]
                        );
                    } else {
                        $this->warn("Skipping article due to missing title or description: {$url}");
                    }
                }
                $this->info('Articles fetched and saved successfully from NewsAPI.');
            } else {
                $this->error("Failed to fetch articles. Status code: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error("Error occurred: {$e->getMessage()}");
        }
    }



    private function fetchFromCurrentsAPI()
    {
        $apiKey = env('CURRENTS_API_KEY');
        $url = "https://api.currentsapi.services/v1/latest-news?apiKey={$apiKey}";

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                foreach ($response['news'] as $articleData) {
                    if (!empty($articleData['description'])) {
                        $category = is_array($articleData['category'] ?? null)
                            ? implode(', ', $articleData['category'])
                            : ($articleData['category'] ?? 'general');

                        $imageUrl = !empty($articleData['image']) && $articleData['image'] !== 'None'
                            ? $articleData['image']
                            : null;

                        Article::updateOrCreate(
                            ['url' => $articleData['url']],
                            [
                                'title' => $articleData['title'],
                                'description' => $articleData['description'],
                                'source' => $articleData['source'] ?? 'Unknown',
                                'category' => $category,
                                'image_url' => $imageUrl,
                                'published_at' => $articleData['published'] ?? null,
                            ]
                        );
                    } else {
                        $this->warn("Skipping Currents API article due to missing description: {$articleData['url']}");
                    }
                }
                $this->info('Articles fetched and saved successfully from Currents API.');
            } else {
                $this->error("Failed to fetch articles from Currents API. Status code: {$response->status()}");
                Log::error('Currents API Error Response:', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $this->error("Exception occurred while fetching articles from Currents API: {$e->getMessage()}");
            Log::error('Currents API Exception:', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
        }
    }


    private function fetchFromGNewsAPI()
    {
        $apiKey = env('GNEWS_API_KEY');
        $url = "https://gnews.io/api/v4/top-headlines?token={$apiKey}&lang=en";

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                foreach ($response['articles'] as $articleData) {
                    if (!empty($articleData['description']) && !empty($articleData['url'])) {
                        $category = 'General';

                        Article::updateOrCreate(
                            ['url' => $articleData['url']],
                            [
                                'title'       => $articleData['title'],
                                'description' => $articleData['description'],
                                'source'      => $articleData['source']['name'] ?? 'Unknown',
                                'category'    => $category,
                                'image_url'   => $articleData['image'] ?? null,
                            ]
                        );
                    } else {
                        $this->warn("Skipping GNews article due to missing details: {$articleData['url']}");
                    }
                }
                $this->info('Articles fetched and saved successfully from GNews API.');
            } else {
                $this->error("Failed to fetch articles from GNews API. Status code: {$response->status()}");
                Log::error('GNews API Error Response:', [
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $this->error("Exception occurred while fetching articles from GNews API: {$e->getMessage()}");
            Log::error('GNews API Exception:', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
        }
    }
}
