@extends('layouts.app')

@section('title', 'About - Fairpoint')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 3rem 2rem;">
    <h1 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 1.5rem;">About Us</h1>
    
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <p style="font-size: 1.125rem; margin-bottom: 1rem; line-height: 1.7;">
            This is the About page. Notice how it uses the same header and layout as the Welcome page!
        </p>
        <p style="margin-bottom: 1rem; color: #6b7280; line-height: 1.6;">
            This demonstrates the power of Laravel's Blade templating system. We defined the layout once in 
            <code style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">layouts/app.blade.php</code> 
            and now all pages can extend it.
        </p>
        <p style="color: #6b7280;">
            No more copying and pasting the same HTML structure across multiple files!
        </p>
    </div>

    <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 1.5rem; border-radius: 4px;">
        <h3 style="font-weight: 600; margin-bottom: 0.75rem; color: #1e40af;">💡 How it works:</h3>
        <ul style="list-style: disc; padding-left: 2rem; color: #1e40af;">
            <li>- Uses the layout</li>
            <li>- Defines page content</li>
            <li>Header is loaded from the layout file automatically</li>
        </ul>
    </div>
</div>
@endsection