@php
    $roleId = (int) auth()->user()->roleid;

    $canManageListings = in_array(
        $roleId,
        [config('admin.administrator'), config('admin.standard')],
        true
    );

    $canManageBlogs = in_array(
        $roleId,
        [config('admin.administrator'), config('admin.editor')],
        true
    );

    $isAdministrator = $roleId === config('admin.administrator');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Admin') | TrendyBeatz</title>

    <link rel="icon" type="image/png" href="{{ asset('images/faviconn.png') }}">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #17212b;
            background: #f3f6f4;
            font: 14px/1.5 Arial, sans-serif;
        }

        a {
            color: inherit;
        }

        .admin-menu details a.active {
            border-radius: 7px;
            color: #fff;
            background: #16803d;
            font-weight: 700;
        }

        

        .admin-shell {
            display: grid;
            grid-template-columns: 235px minmax(0, 1fr);
            min-height: 100vh;
        }

        .admin-sidebar {
            padding: 20px 12px;
            color: #fff;
            background: #101b14;
        }

        .admin-brand {
            display: block;
            margin: 0 10px 24px;
            color: #fff;
            font-size: 22px;
            font-weight: 900;
            text-decoration: none;
        }

        .admin-brand span {
            color: #32c966;
        }

        .admin-menu-link,
        .admin-menu summary {
            display: block;
            width: 100%;
            padding: 10px 12px;
            border-radius: 7px;
            color: #e5ece7;
            text-decoration: none;
            cursor: pointer;
        }

        .admin-menu-link:hover,
        .admin-menu summary:hover,
        .admin-menu-link.active,
        .admin-menu[open] > summary {
            color: #fff;
            background: #16803d;
        }

        .admin-menu details a {
            display: block;
            margin-left: 12px;
            padding: 8px 12px;
            color: #dce6de;
            text-decoration: none;
        }

        .admin-menu details a:hover,
        .admin-menu details a.active {
            color: #fff;
            font-weight: 700;
        }

        .admin-logout {
            width: 100%;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #54735b;
            border-radius: 7px;
            color: #fff;
            background: transparent;
            cursor: pointer;
        }

        .admin-main {
            min-width: 0;
            padding: 24px;
        }

        .admin-topbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
        }

        .admin-panel {
            padding: 20px;
            border: 1px solid #dce4dd;
            border-radius: 10px;
            background: #fff;
        }

        .admin-page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .admin-page-head h1 {
            margin: 0;
        }

        .admin-button {
            display: inline-block;
            padding: 9px 14px;
            border: 0;
            border-radius: 6px;
            color: #fff;
            background: #16803d;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .admin-button-danger {
            background: #b93232;
        }

        .admin-filters,
        .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .admin-filters {
            grid-template-columns: minmax(0, 1fr) 150px auto;
            margin-bottom: 20px;
        }

        .admin-album-filters {
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
        }

        .admin-album-filters .admin-button {
            width: auto;
        }

        .admin-dj-filters {
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
        }

        .admin-field {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 5px;
            font-weight: 700;
        }

        .admin-field input,
        .admin-field select,
        .admin-field textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bfcfc3;
            border-radius: 6px;
            background: #fff;
            font: inherit;
        }

        .admin-field textarea {
            min-height: 110px;
        }

        .admin-field-wide {
            grid-column: 1 / -1;
        }

        .admin-checks {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
        }

        .admin-checks label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .admin-table-wrap {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 11px;
            border-bottom: 1px solid #e6ebe7;
            text-align: left;
            vertical-align: top;
        }

        .admin-actions {
            display: flex;
            gap: 8px;
        }

        .admin-actions form {
            margin: 0;
        }

        .admin-notice {
            margin-bottom: 15px;
            padding: 11px;
            border-radius: 6px;
            background: #e1f5e6;
        }

        .admin-errors {
            margin-bottom: 15px;
            padding: 11px;
            border-radius: 6px;
            color: #8d2020;
            background: #ffebeb;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 15px;
        }

        .admin-stat {
            padding: 20px;
            border-radius: 10px;
            background: #fff;
        }

        .admin-stat strong {
            display: block;
            color: #16803d;
            font-size: 28px;
        }

        @media (max-width: 768px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }

            .admin-main {
                padding: 15px;
            }

            .admin-filters,
            .admin-form-grid,
            .admin-stats {
                grid-template-columns: 1fr;
            }
        }

        .admin-dj-mix-filters {
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
        }


        .admin-embed-preview {
            display: block;
            width: min(100%, 420px);
            margin-top: 12px;
            overflow: hidden;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f3f3f3;
            aspect-ratio: 16 / 9;
        }

        .admin-embed-preview[hidden] {
            display: none;
        }

        .admin-embed-preview iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .admin-pagination {
    margin-top: 18px;
}

.tb-pagination-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.tb-pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 8px;
    border: 1px solid #d4ded6;
    border-radius: 6px;
    color: #16803d;
    background: #fff;
    font-weight: 700;
    text-decoration: none;
}

a.tb-pagination-link:hover,
.tb-pagination-link.is-current {
    border-color: #16803d;
    color: #fff;
    background: #16803d;
}

.tb-pagination-link.is-disabled {
    color: #999;
    background: #f3f3f3;
}


/*BLOG ADMIN CSS */


.blog-page {
    max-width: 1100px;
}

.blog-section {
    margin-bottom: 20px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
}

.blog-section-heading {
    padding: 10px 16px;
    border-bottom: 1px solid #e0e0e0;
    background: #f5f5f5;
    color: #333;
    font-size: 13px;
    font-weight: 700;
}

.blog-section-body {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;
}

.blog-two-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.blog-current-image {
    display: flex;
    align-items: center;
    gap: 14px;
}

.blog-current-image img {
    width: 90px;
    height: 68px;
    border: 2px solid #16803d;
    border-radius: 6px;
    object-fit: cover;
}

.blog-image-preview {
    display: none;
    max-width: 140px;
    max-height: 100px;
    margin-top: 10px;
    border: 2px solid #16803d;
    border-radius: 6px;
    object-fit: cover;
}

.blog-editor {
    min-height: 400px;
    background: #fff;
    color: #111;
    font: 15px Georgia, serif;
}

.blog-page .ql-toolbar.ql-snow {
    background: #f5f5f5;
    border-color: #ccc;
}

.blog-page .ql-container.ql-snow {
    background: #fff;
    border-color: #ccc;
}

.blog-page .ql-editor {
    min-height: 400px;
    color: #111;
}

.blog-page .ql-snow .ql-stroke {
    stroke: #444;
}

.blog-page .ql-snow .ql-fill {
    fill: #444;
}

.blog-page .ql-snow .ql-picker {
    color: #444;
}

.blog-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.blog-publish {
    padding: 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background: #f5f5f5;
}

.blog-publish label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.blog-publish input {
    width: 16px;
    height: 16px;
}

.blog-index-filters {
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: end;
}

@media (max-width: 768px) {
    .blog-two-columns,
    .blog-index-filters {
        grid-template-columns: 1fr;
    }

    .blog-actions {
        flex-wrap: wrap;
    }
}

.login-logo {
    display: block;
    width: 50px;
    height: 50px;
    margin: 0 auto 10px;
    object-fit: contain;
}

 


</style> 

@stack('styles')


</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <img
                class="login-logo"
                src="{{ asset('images/faviconn.png') }}"
                alt="TrendyBeatz"
            >
            Trendy<span>Beatz</span>
        </a>

        <nav class="admin-menu" aria-label="Admin navigation">
            <a
                @class([
                    'admin-menu-link',
                    'active' => request()->routeIs('admin.dashboard'),
                ])
                href="{{ route('admin.dashboard') }}"
            >
                Dashboard
            </a>

            @if ($canManageListings)
                <details
                    @if (request()->routeIs('admin.listings.*')) open @endif
                >
                    <summary>Listings</summary>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.listings.index'),
                        ])
                        href="{{ route('admin.listings.index') }}"
                    >
                        All Listings
                    </a>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.listings.create'),
                        ])
                        href="{{ route('admin.listings.create') }}"
                    >
                        Add Listing
                    </a>
                </details>

                <details @if (request()->routeIs('admin.artists.*')) open @endif>
                    <summary>Artists</summary>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.artists.index'),
                        ])
                        href="{{ route('admin.artists.index') }}"
                    >
                        All Artists
                    </a>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.artists.create'),
                        ])
                        href="{{ route('admin.artists.create') }}"
                    >
                        Add Artist
                    </a>
                </details>

                <details @if (request()->routeIs('admin.albums.*')) open @endif>
                    <summary>Albums</summary>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.albums.index'),
                        ])
                        href="{{ route('admin.albums.index') }}"
                    >
                        All Albums
                    </a>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.albums.create'),
                        ])
                        href="{{ route('admin.albums.create') }}"
                    >
                        Add Album
                    </a>
                </details>

                <details @if (request()->routeIs('admin.djs.*')) open @endif>
                    <summary>DJs</summary>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.djs.index'),
                        ])
                        href="{{ route('admin.djs.index') }}"
                    >
                        All DJs
                    </a>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.djs.create'),
                        ])
                        href="{{ route('admin.djs.create') }}"
                    >
                        Add DJ
                    </a>
                </details>

                <details @if (request()->routeIs('admin.dj-mixes.*')) open @endif>
                    <summary>DJ Mixes</summary>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.dj-mixes.index'),
                        ])
                        href="{{ route('admin.dj-mixes.index') }}"
                    >
                        All DJ Mixes
                    </a>

                    <a
                        @class([
                            'active' => request()->routeIs('admin.dj-mixes.create'),
                        ])
                        href="{{ route('admin.dj-mixes.create') }}"
                    >
                        Add DJ Mix
                    </a>
                </details>
            @endif

            @if ($canManageBlogs)
                <details @if (request()->routeIs('admin.blogs.*')) open @endif>
                    <summary>Blogs</summary>

                    <a
                        @class(['active' => request()->routeIs('admin.blogs.index')])
                        href="{{ route('admin.blogs.index') }}"
                    >
                        All Blogs
                    </a>

                    <a
                        @class(['active' => request()->routeIs('admin.blogs.create')])
                        href="{{ route('admin.blogs.create') }}"
                    >
                        Add Blog
                    </a>
                </details>
            @endif

            @if ($isAdministrator)
                <span class="admin-menu-link">Settings — coming next</span>
            @endif
        </nav>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="admin-logout" type="submit">
                Logout
            </button>
        </form>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <strong>@yield('title', 'Dashboard')</strong>
            <span>Hello, {{ auth()->user()->name }}</span>
        </div>

        @if (session('success'))
            <div class="admin-notice">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="admin-errors">
                <strong>Please correct these fields:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>

@stack('scripts')
</html>