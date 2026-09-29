@extends('public.layouts.app')
@section('title', 'Disclaimer — TrendyBeatz')
@section('content')
<div style="max-width:860px;margin:40px auto;padding:0 20px;font-family:'Segoe UI',Arial,sans-serif;color:#222;">
    <h1 style="font-size:28px;font-weight:800;margin-bottom:6px;color:#0d1b2a;">Disclaimer</h1>
    <p style="font-size:13px;color:#888;margin-bottom:28px;border-bottom:2px solid #4682B4;padding-bottom:12px;">TrendyBeatz Media &mdash; Last updated {{ date('F Y') }}</p>
    <p style="font-size:15px;line-height:1.9;">The information and content on TrendyBeatz is provided for entertainment and informational purposes only. While we strive to keep all content accurate and up to date, we make no warranties of any kind, express or implied, about the completeness, accuracy or reliability of any content on this site.</p>
    <p style="font-size:15px;line-height:1.9;">All music, audio and video content available for download on TrendyBeatz is either submitted directly by artists and rights holders, or linked to third-party sources. We do not host copyrighted content without permission. If you believe your rights have been infringed, please see our <a href="{{ route('page.dmca') }}" style="color:#4682B4;">DMCA policy</a>.</p>
    <p style="font-size:15px;line-height:1.9;">TrendyBeatz shall not be liable for any losses or damages arising from your use of, or inability to use, this website.</p>
</div>
@endsection
