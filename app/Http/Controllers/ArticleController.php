<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    // Method to fetch all articles with search and filtering  
    public function index(Request $request)
    {
        // Start with a query builder for the Article model
        $query = Article::query();

        // Check if search query is provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');

            // Search in 'title' or 'description' or any other fields
            $query->where('title', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
        }

        // Filtering by category
        if ($request->has('category')) {
            $category = $request->input('category');
            $query->where('category_id', $category);
        }

        // Filtering by author
        if ($request->has('author')) {
            $author = $request->input('author');
            $query->where('author_id', $author);
        }

        // Sorting by creation date (default to latest)
        if ($request->has('sort_by') && $request->input('sort_by') == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Execute the query and get the results
        $articles = $query->get();

        // Return the articles in JSON format
        return response()->json($articles); // image_url will be included automatically if part of the model
    }


    // Method to fetch personalized news feed based on user preferences  
    public function personalizedFeed(Request $request)
    {
        $user = $request->user(); // Assuming you're using authentication  
        $preferences = json_decode($user->preferences, true) ?? [];

        $query = Article::query();

        // Filter based on user preferences  
        if (!empty($preferences['sources'])) {
            $query->whereIn('source', $preferences['sources']);
        }
        if (!empty($preferences['categories'])) {
            $query->whereIn('category', $preferences['categories']);
        }

        // Execute the query and get results  
        $articles = $query->get();

        return response()->json($articles);
    }
}
