{{-- resources/views/contact.blade.php --}}
@extends('layouts.app')

@section('title', 'Contact Us - Fairpoint')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-slate-900 dark:to-slate-950">

    {{-- Hero Section --}}
    <section class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 py-16">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
                Get in Touch
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                Have questions about Fairpoint? We're here to help. Reach out to our team of experts.
            </p>
        </div>
    </section>

    {{-- Main Content --}}
    <section class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Contact Form --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-8">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                        Send us a message
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 mb-8">
                        We'll get back to you within 24 hours.
                    </p>

                    <form action="#" method="POST" class="space-y-6">
                        @csrf

                        {{-- Name Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="firstName"
                                    required
                                    placeholder="Juan"
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="lastName"
                                    required
                                    placeholder="Dela Cruz"
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all"
                                >
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                required
                                placeholder="juan.delacruz@gmail.com"
                                class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all"
                            >
                        </div>

                        {{-- Phone & Company --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Phone Number
                                </label>
                                <input
                                    type="tel"
                                    name="phone"
                                    placeholder="+63 917 123 4567"
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Company
                                </label>
                                <input
                                    type="text"
                                    name="company"
                                    placeholder="Your Company"
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all"
                                >
                            </div>
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="subject"
                                required
                                class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all"
                            >
                                <option value="">Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="support">Technical Support</option>
                                <option value="sales">Sales</option>
                                <option value="partnership">Partnership</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        {{-- Message --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="message"
                                required
                                rows="6"
                                placeholder="How can we help you today?"
                                class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#38B6FF] focus:border-transparent outline-none transition-all resize-none"
                            ></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <button
                            type="submit"
                            class="w-full bg-[#38B6FF] text-white py-4 rounded-lg font-semibold hover:bg-[#2a9fe6] transition-all shadow-lg hover:shadow-xl"
                        >
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            {{-- Contact Information Sidebar --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-8 sticky top-24">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8">
                        Contact Information
                    </h2>

                    <div class="space-y-8">

                        {{-- Phone --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Phone</h3>
                                <p class="text-slate-600 dark:text-slate-400">+63 917 123 4567</p>
                                <p class="text-slate-600 dark:text-slate-400">+63 2 8123 4567</p>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Email</h3>
                                <p class="text-slate-600 dark:text-slate-400">hello@fairpointca.com</p>
                                <p class="text-slate-600 dark:text-slate-400">support@fairpointca.com</p>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Office</h3>
                                <p class="text-slate-600 dark:text-slate-400">123 Ayala Avenue</p>
                                <p class="text-slate-600 dark:text-slate-400">Makati City, Philippines</p>
                            </div>
                        </div>

                        {{-- Business Hours --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Business Hours</h3>
                                <p class="text-slate-600 dark:text-slate-400">Monday - Friday: 8:00 AM - 6:00 PM</p>
                                <p class="text-slate-600 dark:text-slate-400">Saturday: 9:00 AM - 12:00 PM</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection