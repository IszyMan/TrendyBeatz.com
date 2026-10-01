@php
    $artistName = trim((string) (
        $artist->Stage_Name ?: $artist->ArtistsName
    ));

    $artistSlug = \Illuminate\Support\Str::slug($artistName);

    $photo = trim((string) $artist->ProfilePic);

    if ($photo === '') {
        $photoUrl = null;
    } elseif (preg_match('~^https?://~i', $photo)) {
        $photoUrl = $photo;
    } else {
        $photoPath = ltrim($photo, '/');

        $photoUrl = asset(
            str_starts_with($photoPath, 'images/')
                ? $photoPath
                : 'images/' . $photoPath
        );
    }
@endphp

<a
        class="tb-artist-card"
        href="{{ filled($artistSlug)
            ? route('artists.show', ['slug' => $artistSlug])
            : route('artists.index') }}"
    >
        <span class="tb-artist-card-photo">
        <span class="tb-artist-card-placeholder" aria-hidden="true">
            TB
        </span>

        @if ($photoUrl)
            <img
                src="{{ $photoUrl }}"
                alt="{{ $artistName }} profile photo"
                loading="lazy"
                onerror="this.remove()"
            >
        @endif
    </span>

    <strong class="tb-artist-card-name">
        {{ $artistName }}
    </strong>

    <span class="tb-artist-card-action">
        View Profile →
    </span>
</a>