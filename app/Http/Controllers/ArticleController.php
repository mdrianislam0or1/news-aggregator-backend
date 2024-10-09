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
        // Initialize the query  
        $query = Article::query();

        // Search by keyword  
        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('title', 'LIKE', "%{$keyword}%")
                ->orWhere('description', 'LIKE', "%{$keyword}%");
        }

        // Filter by category  
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by source  
        if ($request->has('source')) {
            $query->where('source', $request->input('source'));
        }

        // Filter by date (e.g., created_at)  
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        // Execute the query and get results  
        $articles = $query->get();

        return response()->json($articles);
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
