<?php

Route::group(['prefix' => '{locale}'], function () {

    Route::get('article/{article_slug}', 'ReaderArticleController@getArticle');

//    Route::get('sections', 'Article\ReaderArticleController@getSections');
//
//    Route::get('category/{category_slug}', 'Article\ReaderArticleController@getArticlesByCategory');
//
//    Route::get('search', 'Article\ReaderArticleController@getArticlesBySearch');
//
//    Route::get('archive', 'Article\ReaderArticleController@getArticlesByArchive');
});
