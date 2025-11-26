@extends('layouts.app')

@section('title', 'Pricing - Fairpoint')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-950">

    {{-- Hero Section --}}
    <section class="pt-20 pb-16 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl font-bold text-slate-900 dark:text-white mb-6">
                Simple, Transparent Pricing
            </h1>
            <p class="text-xl text-slate-600 dark:text-slate-300 mb-8 max-w-2xl mx-auto">
                Choose the perfect plan for your business needs. All plans include our core accounting features and BIR compliance tools, with Philippine tax automation.
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Try free for 14 days • No setup fees
            </p>
        </div>
    </section>

    {{-- Billing Toggle - NO route() = NO ERRORS --}}
    <div class="max-w-7xl mx-auto px-6 mb-12">
        <div class="flex justify-center">
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md p-1 inline-flex">
                <a href="/pricing"
                   class="px-8 py-3 rounded-md font-medium transition-all {{ request('period') !== 'yearly' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    Monthly
                </a>
                <a href="/pricing?period=yearly"
                   class="px-8 py-3 rounded-md font-medium transition-all {{ request('period') === 'yearly' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    Yearly <span class="text-sm font-normal opacity-90">(Save up to 17%)</span>
                </a>
            </div>
        </div>
    </div>

    @php
        $isYearly = request('period') === 'yearly';
        $plans = [
            [
                'name' => 'Starter Plan',
                'monthly' => 1500,
                'yearly' => 15000,
                'description' => 'Perfect for small businesses and freelancers',
                'popular' => false,
                'features' => [
                    'Up to 50 transactions per month',
                    'Basic BIR forms generation',
                    'Income and expense tracking',
                    'Monthly financial reports',
                    'Email support',
                    'Mobile app access',
                ]
            ],
            [
                'name' => 'Professional Plan',
                'monthly' => 3500,
                'yearly' => 35000,
                'description' => 'Ideal for growing businesses with more complex needs',
                'popular' => true,
                'features' => [
                    'Up to 500 transactions per month',
                    'All BIR forms generation',
                    'Advanced financial reporting',
                    'Bank reconciliation',
                    'Multi-user access (up to 3 users)',
                    'Priority email support',
                    'Audit trail and compliance monitoring',
                    'Custom chart of accounts',
                ]
            ],
            [
                'name' => 'Enterprise Plan',
                'monthly' => 7500,
                'yearly' => 75000,
                'description' => 'For established businesses requiring full features',
                'popular' => false,
                'features' => [
                    'Unlimited transactions',
                    'All BIR forms and compliance features',
                    'Advanced analytics and dashboards',
                    'Multiple company management',
                    'Unlimited users',
                    'Phone and email support',
                    'Dedicated account manager',
                    'API access',
                    'Custom integrations',
                    'Advanced security features',
                ]
            ]
        ];
    @endphp

    {{-- Pricing Plans --}}
    <section class="py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-3 gap-8">
                @foreach($plans as $plan)
                    @php
                        $price = $isYearly ? $plan['yearly'] : $plan['monthly'];
                        $savings = ($plan['monthly'] * 12) - $plan['yearly'];
                    @endphp

                    <div class="relative {{ $plan['popular'] ? 'ring-4 ring-primary/30 scale-105' : '' }} transition-all">
                        @if($plan['popular'])
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                <span class="bg-primary text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-8 h-full flex flex-col">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $plan['name'] }}</h3>
                                <p class="text-slate-600 dark:text-slate-400 mt-2">{{ $plan['description'] }}</p>
                            </div>

                            <div class="text-center mb-8">
                                <span class="text-4xl font-bold text-slate-900 dark:text-white">
                                    ₱{{ number_format($price) }}
                                </span>
                                <span class="text-lg font-normal text-slate-500">/{{ $isYearly ? 'year' : 'month' }}</span>

                                @if($isYearly && $savings > 0)
                                    <p class="text-green-600 dark:text-green-400 text-sm mt-2">
                                        Save ₱{{ number_format($savings) }} per year
                                    </p>
                                @endif
                            </div>

                            <ul class="space-y-4 flex-1 mb-8">
                                @foreach($plan['features'] as $feature)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-slate-600 dark:text-slate-300 text-sm">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- NO route('register') → NO ERROR --}}
                            <a href="/register"
                               class="w-full text-center py-3 px-6 rounded-lg font-semibold transition-all
                                      {{ $plan['popular'] 
                                         ? 'bg-primary text-white hover:bg-[#2BA3E6] shadow-lg' 
                                         : 'border-2 border-primary text-primary hover:bg-primary hover:text-white' }}">
                                Choose {{ Str::after($plan['name'], ' ') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Consulting Services --}}
    <section class="py-20 px-6 bg-white dark:bg-slate-800">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                    Consulting Services
                </h2>
                <p class="text-xl text-slate-600 dark:text-slate-300">
                    Get expert advice from certified Filipino accountants
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach([
                    ['title' => 'BIR Registration Setup', 'price' => '₱5,000'],
                    ['title' => 'Tax Compliance Audit', 'price' => '₱15,000'],
                    ['title' => 'Financial Statement Preparation', 'price' => '₱8,000'],
                    ['title' => 'Bookkeeping Setup', 'price' => '₱3,000'],
                    ['title' => 'Monthly Bookkeeping Service', 'price' => '₱12,000'],
                    ['title' => 'Annual Tax Filing', 'price' => '₱6,000'],
                ] as $service)
                    <div class="bg-gradient-to-br from-[#38B6FF] to-[#2563eb] rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300 text-white">
                        <p class="text-white/80 text-lg mb-6">Consult with</p>

                        <div class="bg-white/20 backdrop-blur rounded-xl p-6 mb-6 flex items-center justify-center h-32">
                            <div class="w-20 h-20 bg-white/30 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-white/70" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5">
                            <p class="text-gray-600 text-sm mb-1">Service</p>
                            <h4 class="text-lg font-bold text-gray-900 mb-3">{{ $service['title'] }}</h4>
                            <p class="text-2xl font-bold text-gray-900">{{ $service['price'] }}</p>
                        </div>

                        <a href="/contact" class="block w-full text-center mt-6 bg-white text-primary font-bold py-3 rounded-xl hover:bg-primary hover:text-white transition-all shadow-lg">
                            Book Consultation
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="py-20 px-6">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-slate-900 dark:text-white mb-4">
                    Frequently Asked Questions
                </h2>
            </div>

            <div class="space-y-4">
                @foreach([
                    ['q' => 'How does Fairpoint pricing work?', 'a' => 'We offer simple, transparent pricing based on the number of transactions and features you need. Yearly plans save you up to 17%.'],
                    ['q' => 'Can I change my plan anytime?', 'a' => 'Yes, you can upgrade or downgrade at any time. Changes take effect on your next billing cycle.'],
                    ['q' => 'Is there a setup fee?', 'a' => 'No, there are no setup fees for any of our plans.'],
                    ['q' => 'Do you offer refunds?', 'a' => 'We offer a 30-day money-back guarantee.'],
                    ['q' => "What's included in the consultation?", 'a' => 'Personalized guidance from certified Filipino accountants who know BIR rules inside out.'],
                ] as $faq)
                    <details class="group bg-white dark:bg-slate-800 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <summary class="list-none px-8 py-6 cursor-pointer font-medium text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors flex justify-between items-center">
                            <span>{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 text-slate-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>
                        <div class="px-8 pb-6 text-slate-600 dark:text-slate-300">
                            {{ $faq['a'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection