{!! '<' . '?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
    </url>
    <url>
        <loc>{{ route('katalog.index') }}</loc>
    </url>
    @foreach ($kategoris as $kategori)
        <url>
            <loc>{{ route('katalog.kategori', ['kategori' => $kategori->slug]) }}</loc>
        </url>
    @endforeach
    @foreach ($produks as $produk)
        <url>
            <loc>{{ route('katalog.produk', ['barangJasa' => $produk->idbarangjasa]) }}</loc>
            @if ($produk->updated_at)
                <lastmod>{{ \Illuminate\Support\Carbon::parse($produk->updated_at)->toAtomString() }}</lastmod>
            @endif
        </url>
    @endforeach
</urlset>