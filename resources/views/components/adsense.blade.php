@if(auth()->guest() || (auth()->check() && !auth()->user()->isAdmin()))
<div x-data="{ adLoaded: false, adFailed: false }" x-init="
    setTimeout(() => {
        const ad = $el.querySelector('ins.adsbygoogle');
        const observer = new MutationObserver(() => {
            if (ad && ad.offsetHeight > 0) {
                adLoaded = true;
                observer.disconnect();
            }
        });
        observer.observe(ad, { attributes: true, childList: true, subtree: true });

        setTimeout(() => {
            if (!adLoaded) {
                adFailed = true;
            }
        }, 5000);
    }, 10);
" x-show="!adFailed" class="flex w-full justify-center py-4">
    <div class="relative w-full max-w-[728px] overflow-hidden rounded-lg">
        <div x-show="!adLoaded" class="absolute inset-0 z-10 flex min-h-[90px] items-center justify-center text-sm text-white">
            AD
        </div>

        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
            crossorigin="anonymous"></script>

        <ins class="adsbygoogle block w-full"
            data-ad-client="ca-pub-1640926658118061"
            data-ad-slot="2252213454"
            data-ad-format="auto"
            data-full-width-responsive="true"
            style="display:block;min-height:90px;"></ins>

        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
</div>

@endif
