@extends('public.layouts.app')
@section('title', 'Contact Us — TrendyBeatz')
@section('content')
<div style="max-width:860px;margin:40px auto;padding:0 20px;font-family:'Segoe UI',Arial,sans-serif;color:#222;">
    <h1 style="font-size:28px;font-weight:800;margin-bottom:6px;color:#0d1b2a;">Contact TrendyBeatz</h1>
    <p style="font-size:13px;color:#888;margin-bottom:28px;border-bottom:2px solid #4682B4;padding-bottom:12px;">TrendyBeatz Media &mdash; Last updated {{ date('F Y') }}</p>
    <p style="font-size:15px;line-height:1.9;">Have a question, a music submission, or a business inquiry? We&apos;d love to hear from you.</p>
    <div style="background:#f4f8fc;border-radius:12px;padding:28px;margin:20px 0;border:1px solid #dbe8f4;">
        <p style="margin:0 0 12px;font-size:15px;"><strong>Email:</strong> <a href="mailto:info@trendybeatz.com" style="color:#4682B4;">info@trendybeatz.com</a></p>
        <p style="margin:0 0 12px;font-size:15px;"><strong>WhatsApp (Business only):</strong> <a href="https://wa.me/2349076131844" style="color:#25d366;">+234 907 613 1844</a></p>
        <p style="margin:0;font-size:15px;"><strong>Office:</strong> TrendyBeatz Media, Lekki, Lagos, Nigeria</p>
    </div>
    <p style="font-size:14px;color:#666;line-height:1.8;">For music promotion, advertising or partnership enquiries, please include your name, brand/artist name and a brief description of your request in your message. We respond within a few minutes.</p>
</div>
@endsection
