<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, follow" />
    <title>Privacy Policy - FPL Managers</title>
    <script src="https://cdn.tailwindcss.com"></script>
      <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet" />
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
    
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-36Z6T6DYQ4"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-36Z6T6DYQ4');
</script>
    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>

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
            <h1 class="text-4xl font-extrabold text-white mb-6 text-center">Privacy Policy</h1>
            <p class="text-sm text-gray-500 text-center mb-8">
                Last Updated: October 28, 2025
            </p>

            <h2>Introduction</h2>
            <p>
                Welcome to FPL Managers Stats Tracker ("we," "our," or "us"). We are committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.
            </p>

            <h2>Information We Collect</h2>
            <h3>Personal Data</h3>
            <p>
                When you register as an Admin, we collect basic personal information necessary for account creation, such as your email address and a password (stored securely). We do not collect sensitive personal information.
            </p>

            <h3>Log Data</h3>
            <p>
                Like many site operators, we collect information that your browser sends whenever you visit our Site ("Log Data"). This Log Data may include:
            </p>
            <ul>
                <li>Your computer's Internet Protocol ("IP") address.</li>
                <li>Browser type and version.</li>
                <li>The pages of our Site that you visit, the time and date of your visit.</li>
                <li>The time spent on those pages and other statistics.</li>
            </ul>

            <h2>Use of Data</h2>
            <p>
                We use the collected data for the following purposes:
            </p>
            <ul>
                <li>To provide and maintain our Service (tracking FPL league statistics).</li>
                <li>To notify you about changes to our Service.</li>
                <li>To allow you to participate in interactive features of our Service when you choose to do so.</li>
                <li>To provide customer support and improve our service.</li>
            </ul>

            <h2>Cookies</h2>
            <p>
                We use "cookies" to collect information and improve our Service. Cookies are files with a small amount of data, which may include an anonymous unique identifier. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our Service.
            </p>

            <h2>Third-Party Advertising (AdSense Compliance)</h2>
            <p>
                We use third-party advertising companies to serve ads when you visit our website. These companies may use information (not including your name, address, email address, or telephone number) about your visits to this and other websites in order to provide advertisements about goods and services of interest to you.
            </p>
            <h3>Google AdSense</h3>
            <p>
                **Google, as a third-party vendor, uses cookies to serve ads on our site.** Google’s use of the DART cookie enables it to serve ads to our users based on their visit to our site and other sites on the Internet. Users may opt out of the use of the DART cookie by visiting the Google Ad and Content Network Privacy Policy.
            </p>

            <h2>Security of Data</h2>
            <p>
                The security of your Personal Data is important to us, but remember that no method of transmission over the Internet, or method of electronic storage, is 100% secure. While we strive to use commercially acceptable means to protect your Personal Data, we cannot guarantee its absolute security.
            </p>

            <h2>Changes to This Privacy Policy</h2>
            <p>
                We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page. You are advised to review this Privacy Policy periodically for any changes.
            </p>

            <h2>Contact Us</h2>
            <p>
                If you have any questions about this Privacy Policy, please contact us at: <a href="mailto:admin@thetechtower.com" class="text-indigo-400 hover:text-indigo-300">admin@thetechtower.com</a>
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
