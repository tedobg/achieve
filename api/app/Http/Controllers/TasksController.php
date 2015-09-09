<?php

namespace App\Http\Controllers;

//Base dependencies
use Illuminate\Http\Request;


class TasksController extends AbstractController {
    
    protected $modelName = 'Tasks';
    protected $validation = [
        'create' => [
            'categories_id' => 'required',
            'alias' => 'required|unique:tasks',
            'title' => 'required|unique:tasks',
            'color' => 'required',
            'bg_color' => 'required'
        ],
        'update' => [
            'categories_id' => 'required',
            'alias' => 'required|unique:tasks',
            'title' => 'required|unique:tasks',
            'color' => 'required',
            'bg_color' => 'required'
        ],
        'delete' => [
            'id' => 'required'
        ]
    ];
    
    protected function _listFilter(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        if($request->category)
            $query->where('categories_id', $request->category);
        if($request->search)
            $query->where('title', "LIKE", '%'. $request->search . '%');
        
        if($request->onlymy)
            $query->where('user_defined', 1)
                  ->where('users_id', \App\Http\Middleware\AuthMiddleware::user('id'));
        elseif($request->notmy)
            $query->where('user_defined', 0);
        else
            $query->where(function($query){
                $query->where(function($query){
                    $query->where('user_defined', 1)
                          ->where('users_id', \App\Http\Middleware\AuthMiddleware::user('id'));
                })->orWhere('user_defined', 0);
            });
    }
    
    protected function _listWith(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        $query->with(['locale' => function($query){
            $query->where('locale', app('translator')->getLocale());
        },'category']);
    }
    
    protected function _viewFilter(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        if($request->alias)
            $query->where('alias', $request->alias);
        else if($request->id)
            $query->where('id', $request->id);
        else
            $query->where('alias', NULL);
        
        $query->where(function($query){
            $query->where(function($query){
                $query->where('user_defined', 1)
                      ->where('users_id', \App\Http\Middleware\AuthMiddleware::user('id'));
            })->orWhere('user_defined', 0);
        });
    }
    
    protected function _viewWith(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        $query->with([
        'category' => function($query) {
            $query->with(['locale' => function($query){
                $query->where('locale', app('translator')->getLocale());
            }]);
        },
        'user' => function($query) {
            $query->with(['locale' => function($query){
                $query->where('locale', app('translator')->getLocale());
            }]);
        },
        'achievements'  => function($query){
            $query->with(['locale' => function($query){
                $query->where('locale', app('translator')->getLocale());
            }]);
        },
        'favourited' => function($query){
           $query->where('users_id', \App\Http\Middleware\AuthMiddleware::user('id'));
        },
        'locale' => function($query){
            $query->where('locale', app('translator')->getLocale());
        }]);
    }
    
    protected function _simpleFilter(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        $this->_viewFilter($query, $request);
    }
    
    protected function _simpleWith(\Illuminate\Database\Eloquent\Builder $query, Request $request)
    {
        $query->with([
            'category' => function($query) {
                $query->with(['locale' => function($query){
                    $query->where('locale', app('translator')->getLocale());
                }]);
            },
            'locale' => function($query){
                $query->where('locale', app('translator')->getLocale());
        }]);
    }
}
