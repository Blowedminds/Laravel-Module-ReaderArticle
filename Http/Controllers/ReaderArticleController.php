<?php

namespace App\Http\Controllers\Reader\Article;

use App\Category;
use App\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Article;
use Illuminate\Support\Facades\Cache;

class ReaderArticleController extends Controller
{
    public function __construct()
    {
    }

    public function getArticle($locale, $slug)
    {
        $language_id = Language::slug($locale)->firstOrFail()->id;

        $article = Article::slug($slug)->whereHasPublishedContent($language_id)
            ->withPublishedContent($language_id)
            ->with(['categories', 'availableLanguages', 'author' => function ($q) {
                $q->with('userData');
            }])
            ->firstOrFail()
            ->toArray();

        $article['author'] = [
            'name' => $article['author']['name'],
            'profile_image' => $article['author']['user_data']['profile_image'],
            'biography' => $article['author']['user_data']['biography'][$locale],
        ];

        return response()->json($article);
    }

    public function getSections(Language $locale)
    {
        $sections = Cache::remember("$locale->slug:article:sections", 5, function () use ($locale) {

            return Category::with(['articles' => function ($query) use ($locale) {
                $query->whereHasPublishedContent($locale->id)
                    ->withPublishedContent($locale->id)
                    ->orderBy('created_at', 'DESC')
                    ->NPerGroup('article_categories', 'category_id', 7);
            }])->get()->toArray();
        });

        return response()->json($sections);
    }

    public function getArticlesByCategory(Language $locale, $category_slug)
    {
        $category = Category::slug($category_slug)->firstOrFail();

        $articles = $category->articles()
            ->whereHasPublishedContent($locale->id)
            ->with(['contents' => function ($q) {
                $q->select('article_id', 'title');
            }])
            ->paginate(10);

        return response()->json($articles, 200);
    }

    public function getArticlesBySearch($locale)
    {
        $language_id = Language::slug($locale)->firstOrFail()->id;

        $query = request()->input('q');

        $articles = Article::whereHasPublishedContent($language_id)->whereHas('contents', function ($q) use ($query, $language_id) {
            $q->where('title', 'like', '%' . $query . '%');
        })->withPublishedContent($language_id)->get();

        return response()->json($articles, 200);
    }

    public function getArticleByDetailedSearch()
    {
    }

    public function getArticlesByArchive(Request $request)
    {
    }

}
