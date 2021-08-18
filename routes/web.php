<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::get('/home', 'HomeController@redirect')->name('home_redirect');


Route::resource('begegnungen', 'BegegnungenController', ['names' => [
    'store' => 'Begegnungen.new',
    'edit' => 'Begegnungen.edit',
    'destroy' => 'Begegnungen.destroy',
    'update' => 'Begegnungen.update',

]]);

Route::resource('spiele', 'SpieleController', ['names' => [
    'store' => 'Spiele.new',
    'edit' => 'Spiele.edit',
    'destroy' => 'Spiele.destroy',
    'update' => 'Spiele.update',

]]);

Route::resource('spiele', 'SpielerController', ['names' => [
    'store' => 'Spieler.new',
    'edit' => 'Spieler.edit',
    'destroy' => 'Spieler.destroy',
    'update' => 'Spieler.update',

]]);

Route::get('/spiele', 'SpieleController@index')->name('spiele');
Route::get('/spieler', 'SpielerController@index')->name('spieler');
