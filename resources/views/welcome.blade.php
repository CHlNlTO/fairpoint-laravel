@extends('layouts.app')

@section('title', 'Welcome - Fairpoint')

@section('content')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 2rem;
        text-align: center;
        margin-bottom: 3rem;
    }
    .hero-title {
        font-size: 3.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .hero-subtitle {
        font-size: 1.5rem;
        opacity: 0.95;
        margin-bottom: 2rem;
    }
    .hero-button {
        display: inline-block;
        background: white;
        color: #667eea;
        padding: 1rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.125rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hero-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem 3rem;
    }
    .feature-card {
        background: white;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        transition: transform 0.3s, box-shadow 0.3s;
        border-top: 4px solid;
    }
    .feature-card:nth-child(1) { border-top-color: #f56565; }
    .feature-card:nth-child(2) { border-top-color: #ed8936; }
    .feature-card:nth-child(3) { border-top-color: #48bb78; }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    }
    .feature-icon {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        display: block;
    }
    .feature-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1a202c;
    }
    .feature-description {
        color: #718096;
        line-height: 1.7;
        margin-bottom: 1.5rem;
        font-size: 1.05rem;
    }
    .feature-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: color 0.2s;
    }
    .feature-link:hover {
        color: #764ba2;
    }
    .cta-section {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 12px;
        text-align: center;
        max-width: 1200px;
        margin: 0 auto 3rem;
    }
    .cta-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    .cta-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.95;
    }
    .cta-button {
        display: inline-block;
        background: white;
        color: #f5576c;
        padding: 1rem 2.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.125rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .cta-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto 3rem;
        padding: 0 2rem;
    }
    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        text-align: center;
    }
    .stat-number {
        font-size: 3rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 0.5rem;
    }
    .stat-label {
        color: #718096;
        font-size: 1.125rem;
    }
</style>

<div class="hero-section">
    <div style="max-width: 900px; margin: 0 auto;">
        <h1 class="hero-title">🚀 Welcome to Fairpoint</h1>
        <p class="hero-subtitle">Build amazing applications with Laravel's powerful framework</p>
        <a href="https://laravel.com/docs" target="_blank" class="hero-button">Get Started</a>
    </div>
</div>

<div class="stats-section">
    <div class="stat-card">
        <div class="stat-number">10K+</div>
        <div class="stat-label">Active Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">500+</div>
        <div class="stat-label">Projects Built</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">99.9%</div>
        <div class="stat-label">Uptime</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">24/7</div>
        <div class="stat-label">Support</div>
    </div>
</div>

<div class="features-grid">
    <div class="feature-card">
        <span class="feature-icon">📚</span>
        <h3 class="feature-title">Comprehensive Docs</h3>
        <p class="feature-description">
            Laravel has wonderful, thorough documentation covering every aspect of the framework. Whether you're new or experienced, you'll find what you need.
        </p>
        <a href="https://laravel.com/docs" target="_blank" class="feature-link">
            Read the documentation →
        </a>
    </div>

    <div class="feature-card">
        <span class="feature-icon">🎥</span>
        <h3 class="feature-title">Video Tutorials</h3>
        <p class="feature-description">
            Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript. Perfect for visual learners who want to level up their skills.
        </p>
        <a href="https://laracasts.com" target="_blank" class="feature-link">
            Start learning →
        </a>
    </div>

    <div class="feature-card">
        <span class="feature-icon">📰</span>
        <h3 class="feature-title">Latest News</h3>
        <p class="feature-description">
            Stay up to date with the latest Laravel news, packages, tutorials, and community updates. Never miss what's new in the ecosystem.
        </p>
        <a href="https://laravel-news.com" target="_blank" class="feature-link">
            Read the news →
        </a>
    </div>
</div>

<div style="max-width: 1200px; margin: 0 auto; padding: 0 2rem;">
    <div class="cta-section">
        <h2 class="cta-title">Ready to Build Something Amazing?</h2>
        <p class="cta-subtitle">Laravel gives you all the tools you need to create modern, powerful web applications</p>
        <a href="https://laravel.com" target="_blank" class="cta-button">
            Explore Laravel
        </a>
    </div>
</div>
@endsection