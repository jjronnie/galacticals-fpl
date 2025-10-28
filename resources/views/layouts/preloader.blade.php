{{-- 
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        #refreshPreloader {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s ease-out;
        }

        #refreshPreloader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .preloader-content {
            text-align: center;
            position: relative;
        }

        /* Animated football */
        .football-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
        }

        .football {
            width: 64px;
            height: 64px;
            position: relative;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            animation: bounce 1.2s cubic-bezier(0.28, 0.84, 0.42, 1) infinite;
        }

        .football svg {
            width: 100%;
            height: 100%;
            filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.3));
        }

        /* Shadow under ball */
        .football-shadow {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 8px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            filter: blur(4px);
            animation: shadowPulse 1.2s cubic-bezier(0.28, 0.84, 0.42, 1) infinite;
        }

        /* Rotating field lines */
        .field-lines {
            position: absolute;
            inset: -20px;
            animation: rotate 3s linear infinite;
            opacity: 0.15;
        }

        .field-line {
            position: absolute;
            background: white;
            border-radius: 2px;
        }

        .field-line:nth-child(1) {
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            transform: translateY(-50%);
        }

        .field-line:nth-child(2) {
            top: 0;
            bottom: 0;
            left: 50%;
            width: 2px;
            transform: translateX(-50%);
        }

        /* Loading text */
        .loading-text {
            color: #e2e8f0;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
            animation: pulse 2s ease-in-out infinite;
        }

        /* Loading dots */
        .dots {
            display: inline-flex;
            gap: 4px;
            margin-left: 2px;
        }

        .dot {
            width: 4px;
            height: 4px;
            background: #3b82f6;
            border-radius: 50%;
            animation: dotPulse 1.4s ease-in-out infinite;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translate(-50%, -50%) translateY(-20px);
            }
            50% {
                transform: translate(-50%, -50%) translateY(0px);
            }
        }

        @keyframes shadowPulse {
            0%, 100% {
                transform: translateX(-50%) scale(0.6);
                opacity: 0.3;
            }
            50% {
                transform: translateX(-50%) scale(1);
                opacity: 0.5;
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @keyframes dotPulse {
            0%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.2);
                opacity: 1;
            }
        }
    </style>


    <!-- Preloader Overlay -->
    <div id="refreshPreloader">
        <div class="preloader-content">
            <!-- Football Animation -->
            <div class="football-container">
                <div class="field-lines">
                    <div class="field-line"></div>
                    <div class="field-line"></div>
                </div>
                <div class="football">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="30" fill="white" stroke="#1e293b" stroke-width="2"/>
                        <path d="M32 8 L38 20 L32 28 L26 20 Z" fill="#1e293b"/>
                        <path d="M48 18 L52 28 L44 34 L38 26 Z" fill="#1e293b"/>
                        <path d="M56 38 L52 48 L42 46 L44 36 Z" fill="#1e293b"/>
                        <path d="M42 54 L32 56 L26 46 L34 44 Z" fill="#1e293b"/>
                        <path d="M16 54 L12 44 L20 38 L28 44 Z" fill="#1e293b"/>
                        <path d="M8 32 L12 22 L22 24 L20 34 Z" fill="#1e293b"/>
                        <path d="M20 12 L30 10 L32 20 L24 22 Z" fill="#1e293b"/>
                    </svg>
                </div>
                <div class="football-shadow"></div>
            </div>
            
            <!-- Loading Text -->
            <p class="loading-text">
                Loading
                <span class="dots">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </span>
            </p>
        </div>
    </div>

    <script>
        const preloader = document.getElementById('refreshPreloader');

        // Show preloader before unload
        window.addEventListener('beforeunload', () => {
            preloader.classList.remove('hidden');
        });

        // Hide preloader when coming back via history (back button, forward button)
        window.addEventListener('pageshow', (event) => {
            // If restored from bfcache (browser cache), hide preloader
            if (event.persisted) {
                preloader.classList.add('hidden');
            }
        });

        // Also handle popstate for SPA/PWA navigation
        window.addEventListener('popstate', () => {
            preloader.classList.add('hidden');
        });

        // Hide preloader on load
        window.addEventListener('load', () => {
            setTimeout(() => {
                preloader.classList.add('hidden');
            }, 500);
        });
    </script> --}}
