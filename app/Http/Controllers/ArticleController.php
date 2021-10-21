<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ArticleRequest;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = Article::all();
        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ArticleRequest $request)
    {
        $article = new Article($request->all());
        $article->user_id = $request->user()->id;
        $files = $request->file('file');

        DB::beginTransaction();
        try {
            $article->save();

            foreach ($files as $file) {
                $file_name = $file->getClientOriginalName();
                $path = Storage::putFile('articles', $file);

                $image = new Image();
                $image->article_id = $article->id;
                $image->img_name = $file_name;
                $image->name = basename($path);

                $image->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            foreach ($files as $file) {
                if (!empty($path)) {
                    Storage::delete($path);
                }
            }
            DB::rollback();
            return back()
                ->withErrors($e->getMessage());
        }

        return redirect()
            ->route('articles.index')
            ->with(['flash_message' => '登録が完了しました']);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all());
        try {
            $article->save();
        } catch (\Exception $e) {
            return back()
                ->withErrors($e->getMessage());
        }
        return redirect()
            ->route('articles.index')
            ->with(['flash_message' => ' 更新が完了しました']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $images = $article->images;
        $article->delete();

        foreach ($images as $image) {
            Storage::delete('articles/' . $image->name);
        }
        return redirect()
            ->route('articles.index')
            ->with(['flash_message' => '削除しました']);
    }
}
