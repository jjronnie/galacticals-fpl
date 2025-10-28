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
" x-show="!adFailed" class="w-full flex justify-center items-center py-6">
    <div class="w-[728px] h-[90px] relative overflow-hidden">
        <div x-show="!adLoaded" class="absolute inset-0 flex items-center justify-center text-sm text-white">
            AD
        </div>

        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
            crossorigin="anonymous"></script>

        <ins class="adsbygoogle block w-[728px] h-[90px]"
            data-ad-client="ca-pub-1640926658118061"
            data-ad-slot="2252213454"
            style="display:inline-block;width:728px;height:90px;"></ins>

        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
</div>
