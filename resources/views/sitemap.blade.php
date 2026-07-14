<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{{ url('/') }}</loc>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>{{ url('/about') }}</loc>
    <priority>0.3</priority>
  </url>
  <url>
    <loc>{{ url('/create') }}</loc>
    <priority>0.5</priority>
  </url>
@foreach ($spots as $spot)
  <url>
    <loc>{{ url("/spots/{$spot->id}") }}</loc>
    <lastmod>{{ $spot->updated_at->toAtomString() }}</lastmod>
    <priority>0.8</priority>
  </url>
@endforeach
</urlset>
