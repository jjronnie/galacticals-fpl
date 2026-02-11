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
    <div class="relative w-full max-w-[728px] h-[90px] overflow-hidden rounded-lg">
        
        <div x-show="!adLoaded" class="absolute inset-0 z-10 flex h-[90px] items-center justify-center text-sm text-white bg-gray-800">
            AD
        </div>

        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
            crossorigin="anonymous"></script>

        <ins class="adsbygoogle"
            style="display:inline-block;width:100%;max-width:728px;height:90px"
            data-ad-client="ca-pub-1640926658118061"
            data-ad-slot="2252213454"
            data-full-width-responsive="false"></ins>

        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
</div>
@endif