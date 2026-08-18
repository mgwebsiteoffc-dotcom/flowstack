<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ url('/') }}</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>
    <url><loc>{{ url('/pricing') }}</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
    <url><loc>{{ url('/register') }}</loc><changefreq>monthly</changefreq><priority>0.9</priority></url>
    <url><loc>{{ url('/blog') }}</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
    <url><loc>{{ url('/company') }}</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
    <url><loc>{{ url('/features') }}</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
    @foreach (['client-management','project-tasks','leads-crm','finance-invoicing','reporting','automation'] as $fslug)
        <url><loc>{{ url('/features/'.$fslug) }}</loc><changefreq>monthly</changefreq><priority>0.8</priority></url>
    @endforeach
    <url><loc>{{ url('/use-cases') }}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
    @foreach (['digital-agency','creative-agency','web-dev','consulting','saas-agency','freelancers'] as $uslug)
        <url><loc>{{ url('/use-cases/'.$uslug) }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    @endforeach
    <url><loc>{{ url('/integrations') }}</loc><changefreq>monthly</changefreq><priority>0.7</priority></url>
    <url><loc>{{ url('/resources') }}</loc><changefreq>weekly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/faq') }}</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
    <url><loc>{{ url('/contact') }}</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
    <url><loc>{{ url('/tools') }}</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
    <url><loc>{{ url('/tools/retainer-calculator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/tools/invoice-due-calculator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/tools/proposal-value-calculator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/tools/lead-value-calculator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/tools/agency-margin-calculator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/tools/project-quote-generator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc>{{ url('/tools/profit-margin-calculator') }}</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
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
