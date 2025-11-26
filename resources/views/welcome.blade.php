{{-- resources/views/welcome.blade.php --}}
@extends('layouts.app')

@section('title', 'Fairpoint - Smart Accounting')

@section('content')

{{-- HERO --}}
<section class="pt-16 pb-16 text-center bg-linear-to-b from-white to-slate-50 dark:from-slate-900 dark:to-slate-950">
    <div class="container px-6 mx-auto lg:px-8">
        <span class="inline-block bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 text-sm font-medium px-4 py-1.5 rounded-full mb-6">
            Built for Philippine Taxation
        </span>

        <h1 class="mb-6 text-5xl font-bold leading-tight lg:text-6xl text-slate-900 dark:text-white">
            Smart Accounting for <span class="text-primary">Businesses</span>
        </h1>

        <p class="max-w-3xl mx-auto mb-10 text-lg leading-relaxed text-slate-600 dark:text-slate-300">
            Automate your Philippine tax computations, manage your books, and stay compliant with BIR requirements. Built by accountants, for Filipino entrepreneurs.
        </p>

        <div class="flex flex-col justify-center gap-6 mb-16 sm:flex-row">
            <a href="/" class="inline-flex items-center justify-center bg-primary text-white w-64 h-16 rounded-full text-xl font-semibold shadow-lg hover:shadow-xl hover:bg-[#2BA3E6] transition-all duration-300">
                Get Started Free
            </a>
            <button type="button" class="inline-flex items-center justify-center w-64 h-16 text-xl font-semibold transition-all duration-300 bg-white border-2 rounded-full shadow-md dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-600 hover:shadow-lg">
                Watch Demo
            </button>
        </div>

        <div class="flex flex-col items-center justify-center gap-8 text-sm sm:flex-row text-slate-600 dark:text-slate-400">
            <div class="flex items-center gap-2">
                <div class="flex text-yellow-400">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    @endfor
                </div>
                <span class="font-semibold">4.9/5 Rating</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-842.1m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="font-semibold">500 Customers</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="font-semibold">1000+ Businesses</span>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section id="features" class="py-20 bg-white dark:bg-slate-800">
    <div class="container px-6 mx-auto lg:px-8">
        <div class="mb-16 text-center">
            <h2 class="mb-4 text-4xl font-bold text-slate-900 dark:text-white">Everything you need for Philippine accounting</h2>
            <p class="max-w-2xl mx-auto text-xl text-slate-600 dark:text-slate-300">From BIR forms to financial reports, we’ve got you covered.</p>
        </div>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @include('partials.features')
        </div>
    </div>
</section>

{{-- CTA --}}
<section id="contact" class="py-20 bg-linear-to-r from-blue-500 to-[#2BA3E6] text-white text-center">
    <div class="container px-6 mx-auto lg:px-8">
        <h2 class="mb-6 text-4xl font-bold">Ready to simplify your accounting?</h2>
        <p class="max-w-2xl mx-auto mb-8 text-xl opacity-90">Join thousands of Filipino businesses who trust Fairpoint.</p>
        <div class="flex flex-col justify-center gap-4 sm:flex-row">
            <a href="/" class="px-8 py-4 text-lg font-semibold text-black bg-white rounded-lg text-primary hover:bg-gray-50">Get Started Free</a>
            <button type="button" class="px-8 py-4 text-lg font-semibold border-2 border-white rounded-lg hover:bg-white/10">Schedule Demo</button>
        </div>
    </div>
</section>

@endsection
