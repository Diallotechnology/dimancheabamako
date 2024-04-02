@php echo '
<?xml version="1.0" encoding="UTF-8"?>' @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('shop') }}</loc>
    </url>
    <url>
        <loc>{{ route('about') }}</loc>
    </url>
    <url>
        <loc>{{ route('contact') }}</loc>
    </url>
    <url>
        <loc>{{ route('livraison') }}</loc>
    </url>
    <url>
        <loc>{{ route('home') }}</loc>
    </url>
    <url>
        <loc>{{ route('login') }}</loc>
    </url>
    <url>
        <loc>{{ route('register') }}</loc>
    </url>
</urlset>
