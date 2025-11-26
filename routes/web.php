<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/about', fn () => view('about'))->name('about');

Route::get('/contact', fn () => view('contact'))->name('contact');

Route::get('/pricing', fn () => view('pricing'))->name('pricing');
