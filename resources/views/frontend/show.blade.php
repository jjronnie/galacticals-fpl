<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="index, follow" />
  <title>{{ $league->name }} - FPL Managers</title>
  @vite(['resources/css/app.css'])
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: "Nunito", sans-serif;
    }

    .glass {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border: 2px solid rgba(255, 255, 255, 0.18);
      border-radius: 1.5rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 255, 0.05);
      padding: 1.5rem;
    }

    html {
      scroll-behavior: smooth;
    }
  </style>
</head>

<body class="min-h-screen text-gray-200 bg-black">
  <!-- HEADER -->
  <header id="top" class="py-4 text-white bg-[#5B0E9B] shadow-lg">
    <h2 class="flex text-center text-1xl font-extrabold items-center justify-center gap-2">
      {{ $league->name }}
    </h2>
  </header>

  <!-- GAMEWEEK CARDS -->
  <main class="max-w-5xl mx-auto p-4 space-y-6">
    <!-- SEASON HIGHLIGHTS -->
    <section class="mt-8">
      <h2 class="mb-6 text-2xl font-bold text-center">{{ $season->name }} Season Stats</h2>

      <div class="flex my-4 justify-center">
        <a href="#performance"
          class="py-2 px-4 text-white font-semibold text-sm bg-[#5B0E9B] rounded-full shadow-md hover:bg-[#7C1FBF] transition duration-200">
          Scroll to GW Performance
        </a>
      </div>

      @if($stats)
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="p-4 rounded-lg shadow-lg glass">
          <p class="text-green-400 font-bold">MOST GW LEADS</p>
          <p class="text-sm">{{ $stats['most_gw_leads'] }}</p>
        </div>
        
        <div class="p-4 rounded-lg shadow-lg glass">
          <p class="text-red-400 font-bold">MOST GW LAST</p>
          <p class="text-sm">{{ $stats['most_gw_last'] }}</p>
        </div>
        
        <div class="p-4 rounded-lg shadow-lg glass">
          <p class="text-green-400 font-bold">HIGHEST GW POINTS</p>
          <p class="text-sm">{{ $stats['highest_gw_points'] }}</p>
        </div>
        
        <div class="p-4 rounded-lg shadow-lg glass">
          <p class="text-red-400 font-bold">LEAST GW POINTS</p>
          <p class="text-sm">{{ $stats['lowest_gw_points'] }}</p>
        </div>

        <div class="p-4 rounded-lg shadow-lg glass">
          <p class="text-green-400 font-bold">LONGEST TOP STREAK</p>
          <p class="text-sm">{{ $stats['longest_top_streak'] }}</p>
        </div>

        <div class="p-4 rounded-lg shadow-lg glass">
          <p class="text-yellow-400 font-bold">QUEEN MEDIOCRE</p>
          <p class="text-sm">
            @if(empty($stats['mediocres']))
              - - Never Best or Worst
            @else
              @foreach($stats['mediocres'] as $name)
                - {{ $name }}<br>
              @endforeach
            @endif
          </p>
        </div>

        <div class="p-4 rounded-lg shadow-lg glass sm:col-span-2 lg:col-span-3">
          <p class="text-purple-400 font-bold">
            ONLY MEN STANDING - HAVEN'T BEEN LAST BEFORE
          </p>
          @if(empty($stats['men_standing']))
            <p class="text-sm">---</p>
          @else
            @foreach($stats['men_standing'] as $name)
              <p class="text-sm">- {{ $name }}</p>
            @endforeach
          @endif
        </div>

        <div class="p-4 rounded-lg shadow-lg glass sm:col-span-2 lg:col-span-3">
          <p class="text-purple-400 font-bold">
            HALL OF SHAME - HAVE BEEN LAST 3 TIMES OR MORE
          </p>
          @if(empty($stats['hall_of_shame']))
            <p class="text-sm">---</p>
          @else
            @foreach($stats['hall_of_shame'] as $entry)
              <p class="text-sm">- {{ $entry }}</p>
            @endforeach
          @endif
        </div>

        <div class="p-4 rounded-lg shadow-lg glass sm:col-span-2 lg:col-span-3">
          <p class="text-pink-400 font-bold">THE 100+ LEAGUE</p>
          @if(empty($stats['hundred_plus']))
            <p class="text-sm">---</p>
          @else
            @foreach($stats['hundred_plus'] as $entry)
              <p class="text-sm">- {{ $entry }}</p>
            @endforeach
          @endif
        </div>
      </div>
      @endif
    </section>

    <h2 id="performance" class="mb-6 text-2xl font-bold text-center">
      GameWeek Performance
    </h2>

    <!-- Responsive Grid for GWs -->
    <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-3">
      @foreach($gameweeks as $gw)
        <div class="p-6 rounded-lg shadow-lg glass">
          <h2 class="mb-4 text-center text-white text-xl font-bold uppercase">
            GameWeek {{ $gw['number'] }}
          </h2>
          <div class="flex justify-between">
            <div>
              <p class="text-gray-400 text-sm">Best Manager</p>
              <p class="text-green-400 font-bold text-lg">{{ $gw['best']->manager->name }}</p>
              <p class="text-green-400 text-sm font-semibold">{{ $gw['best']->points }}pts</p>
            </div>
            <div class="text-right">
              <p class="text-gray-400 text-sm">Worst Manager</p>
              <p class="text-red-400 font-bold text-lg">{{ $gw['worst']->manager->name }}</p>
              <p class="text-red-400 text-sm font-semibold">{{ $gw['worst']->points }}pts</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="py-6 mt-8 text-center text-gray-500 text-sm border-t border-gray-800">
    © <span id="year"></span> {{ $league->name }}. Powered by FPL Manager Stats.
  </footer>

  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>