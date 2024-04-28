@php
    /** @var \Cyndaron\Category\Category $category */
    /** @var \Cyndaron\Category\ModelWithCategory[] $underlyingPages */
    /** @var \DateTimeInterface $updated */
    /** @var string $domain */
    /** @var \Cyndaron\Url\UrlService $urlService */
@endphp
<?xml version="1.0" encoding="utf-8"?>

<feed xmlns="http://www.w3.org/2005/Atom">

    <title>{{ $title }}</title>
    <author>
        <name>{{ $organisation }}</name>
    </author>
    <link href="{!! $selfUri !!}" rel="self" />
    <id>https://{!! $domain !!}/</id>
    <updated>{{ $updated->format(\DateTimeInterface::ATOM) }}</updated>

    @foreach ($underlyingPages as $page)
        @php
            $url = $page->getFriendlyUrl($urlService);
            $url = str_contains($url, '://') ? $url : "{$baseUrl}{$url}";
        @endphp
    <entry>
        <title>{{ $page->name }}</title>
        <link href="{!! $url !!}" />
        <id>{!! $url !!}</id>
        <published>{{ $page->created->format(\DateTimeInterface::ATOM) }}</published>
        <updated>{{ $page->modified->format(\DateTimeInterface::ATOM) }}</updated>
        <summary>{{ $page->getBlurb() }}</summary>
    </entry>
    @endforeach

</feed>
