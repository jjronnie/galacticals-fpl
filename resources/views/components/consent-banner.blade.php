<div 
    x-data="{
        consentGiven: localStorage.getItem('cookie_notice') === 'true',
        accept() {
            localStorage.setItem('cookie_notice', 'true'); // stores for 1 year implicitly
            this.consentGiven = true;
        }
    }"
    x-show="!consentGiven" x-cloak
    class="fixed bottom-4 left-1/2 transform -translate-x-1/2 w-[95%] sm:w-[90%] md:w-[70%] lg:w-[80%] max-w-md bg-white border border-green-500 rounded-xl shadow-lg p-5 md:p-6 z-50 transition-all"
>
    <div class="flex flex-col gap-4">
        <div class="text-gray-800 text-sm leading-relaxed">
            <span class="font-semibold text-gray-900">We Value Your Privacy.</span>
            Our site uses cookies to improve your browsing experience, analyze traffic, and serve you better.
        </div>
        <div class="flex gap-3 flex-wrap">
            <button @click="accept()"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                OK, Got it
            </button>
            <a href="{{ url('/privacy-policy') }}"
                class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors">
                Learn More
            </a>
        </div>
    </div>
</div>

