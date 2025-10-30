<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, follow" />
    <title>Terms & Conditions - FPL Galaxy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

     <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>

        <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-36Z6T6DYQ4"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-36Z6T6DYQ4');
</script>

      <!--Start of Tawk.to Script-->
  <script type="text/javascript">
    var Tawk_API = Tawk_API || {},
      Tawk_LoadStart = new Date();
    (function () {
      var s1 = document.createElement("script"),
        s0 = document.getElementsByTagName("script")[0];
      s1.async = true;
      s1.src = "https://embed.tawk.to/67ada27b3a842732607e284f/1ijv45d63";
      s1.charset = "UTF-8";
      s1.setAttribute("crossorigin", "*");
      s0.parentNode.insertBefore(s1, s0);
    })();
  </script>
  <!--End of Tawk.to Script-->

    <style>
        body { font-family: "Nunito", sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.18);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
        }
        h2 { @apply text-2xl font-bold mt-6 mb-3 text-indigo-300; }
        h3 { @apply text-xl font-semibold mt-4 mb-2 text-white; }
        p { @apply text-gray-400 mb-4; }
        ul { @apply list-disc list-inside ml-4 mb-4; }
        li { @apply text-gray-400; }
    </style>
    
   

         <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo.webp') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#001529">

    <meta name="mobile-web-app-capable" content="yes">
</head>

<body class="min-h-screen text-gray-200 bg-black">

        <header id="top" class="py-4 text-white bg-[#5B0E9B] shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">

            <h1 class="flex items-center gap-2 text-xl font-extrabold">
                <a href="/" class="flex items-center gap-2 hover:text-indigo-300 transition">
                    <x-logo class="w-12 h-12" />
                    FPL Galaxy
                </a>
            </h1>

            {{-- Login/Register Buttons (Right) --}}
            <nav class="flex space-x-4">
                {{-- Login Button --}}
                <a href="{{ route('login') }}"
                    class="px-3 py-1.5 text-sm font-semibold text-white bg-indigo-600 rounded-md 
                      hover:bg-indigo-700 transition duration-150 ease-in-out 
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-[#5B0E9B]">
                    Login
                </a>

                {{-- Register Button (Hidden on small screens, shown on medium and up) --}}
                <a href="{{ route('register') }}" class=" md:inline-block px-3 py-1.5 text-sm font-semibold text-indigo-100 border border-indigo-100 rounded-md 
                      hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">
                    Get Started
                </a>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-4 space-y-4">

        <x-adsense/>

        <div class="glass mt-8 p-6 sm:p-8">
            <h1 class="text-4xl font-extrabold text-white mb-6 text-center">Terms & Conditions</h1>
            <p class="text-sm text-gray-500 text-center mb-8">
                Last Updated: October 28, 2025
            </p>

            <h2>Introduction</h2>
            <p>
                Welcome to FPL Galaxy ("we," "our," or "us"). By using our website and services, you agree to comply with these Terms & Conditions. Please read them carefully.
            </p>

            <h2>Account Registration</h2>
            <p>
                Users must create an account and verify it before entering their FPL league ID. You are only allowed to register leagues that you personally own. Using leagues that do not belong to you may result in account suspension or permanent banning.
            </p>

            <h2>Use of FPL APIs</h2>
            <p>
                FPL Galaxy uses official Fantasy Premier League (FPL) APIs to fetch league statistics. By using our service, you acknowledge and agree that data is retrieved from FPL's public API and updated automatically after each gameweek. For more information, see <a href="https://fantasy.premierleague.com/help/terms" class="text-indigo-400 hover:text-indigo-300">FPL API policies</a>.
            </p>

            <h2>Free Service & Advertising</h2>
            <p>
                Our site is free to use. To maintain our platform, we display advertisements through Google AdSense. For more details, visit <a href="https://policies.google.com/technologies/ads" class="text-indigo-400 hover:text-indigo-300">AdSense Policies</a>.
            </p>

            <h2>Donations & Suggestions</h2>
            <p>
                Users may support our platform through donations. You can contact us via:
            </p>
            <ul>
                <li>WhatsApp: <a href="https://wa.me/256703283529" class="text-indigo-400 hover:text-indigo-300">+256703283529</a></li>
                <li>Email: <a href="mailto:admin@thetechtower.com" class="text-indigo-400 hover:text-indigo-300">admin@thetechtower.com</a></li>
            </ul>
            <p>
                These contacts can also be used to suggest new features, report issues, or provide feedback.
            </p>

            <h2>User Responsibilities</h2>
            <ul>
                <li>Only register leagues you own.</li>
                <li>Do not attempt to manipulate league data or interfere with the service.</li>
                <li>Respect other users and adhere to applicable laws.</li>
            </ul>

            <h2>Automatic Updates</h2>
            <p>
                FPL Galaxy automatically fetches and updates league statistics after each gameweek. Users do not need to manually refresh or submit data.
            </p>

            <h2>External Links</h2>
            <p>
                Our site may link to external websites for information such as FPL policies or AdSense. We are not responsible for content or practices of third-party sites.
            </p>

            <h2>Changes to Terms</h2>
            <p>
                We may update these Terms & Conditions from time to time. Updates will be posted on this page. Continued use of FPL Galaxy after updates indicates acceptance of the new terms.
            </p>

            <h2>Contact Us</h2>
            <p>
                For questions about these Terms & Conditions, donations, or feature requests, contact us at: 
                <a href="mailto:admin@thetechtower.com" class="text-indigo-400 hover:text-indigo-300">admin@thetechtower.com</a> or 
                <a href="https://wa.me/256703283529" class="text-indigo-400 hover:text-indigo-300">+256703283529 (WhatsApp)</a>.
            </p>
        </div>

        <x-adsense/>

    </main>

    <footer class="py-6 mt-12 text-center text-gray-500 text-sm border-t border-gray-800">
        © <span id="year"></span>
        <a href="https://techtowerinc.com" class="text-gray-400 hover:text-white transition">TechTower Inc.</a>. All rights reserved.
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>
