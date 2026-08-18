<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ url('/') }}</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>
    <url><loc>{{ url('/pricing') }}</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
    <url><loc>{{ url('/register') }}</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
    <url><loc>{{ url('/blog') }}</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
    @foreach ($categories as $category)
        <url><loc>{{ url('/blog/category/'.$category->slug) }}</loc><changefreq>weekly</changefreq><priority>0.6</priority></url>
    @endforeach
    @foreach ($posts as $post)
        <url>
            <loc>{{ url('/blog/'.$post->slug) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>
