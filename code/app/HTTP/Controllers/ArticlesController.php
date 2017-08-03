<?php

namespace Thaliak\HTTP\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Slugify;
use Thaliak\Models\Article;

class ArticlesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'get']]);
    }

    public function index(Request $request): LengthAwarePaginator
    {
        return Article::orderBy('created_at', 'desc')->paginate();
    }

    public function get(Request $request): Article
    {
        return $request->article;
    }

    public function getBySlug(Request $request): Article
    {
        return $request->article_by_slug;
    }

    public function create(Request $request): Article
    {
        $this->validate($request, [
            'title' => 'required|string|unique:articles,title',
            'body' => 'required|string'
        ]);

        $article = new Article($request->only('world_id', 'title', 'body'));
        $article->user_id = $request->user()->id;
        $article->slug = Slugify::slugify($request->title);
        $article->save();

        return $article->fresh();
    }

    public function update(Request $request): Article
    {
        $this->validate($request, ['body' => 'required|string']);

        $article = $request->article;

        if ($request->world_id) {
            $article->world_id = $request->world_id;
        }

        if ($article->title != $request->title) {
            $this->validate($request, [
                'title' => 'required|string|unique:articles,title'
            ]);

            $article->title = $request->title;
            $article->slug = Slugify::slugify($request->title);
        }

        if ($article->body != $request->body) {
            $article->body = $request->body;
        }

        $article->save();

        return $article->fresh();
    }

    public function delete(Request $request): Article
    {
        $request->article->delete();
        return $request->article;
    }
}
