<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdminBase - Unified Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        navy: {
                            950: '#0B0E14',
                            900: '#111620',
                            800: '#1A2130',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0B0E14;
            color: #E2E8F0;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }
        .text-glow {
            text-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0B0E14;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500/30">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-[#0B0E14]/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-gradient-to-tr from-indigo-500 to-cyan-500 flex items-center justify-center text-white">
                    <i data-lucide="database" class="w-4 h-4"></i>
                </div>
                <span class="font-semibold tracking-tight text-lg">AdminBase</span>
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-400">
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#security" class="hover:text-white transition-colors">Security</a>
                <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium bg-white text-black px-4 py-2 rounded-full hover:bg-slate-200 transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-400 hover:text-white hidden sm:block">Log in</a>
                    <a href="{{ route('login') }}" class="text-sm font-medium bg-white text-black px-4 py-2 rounded-full hover:bg-slate-200 transition-colors">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] -z-10 opacity-50"></div>

        <div class="max-w-7xl mx-auto px-6 text-center z-10 relative">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-indigo-500/30 bg-indigo-500/10 text-indigo-300 text-xs font-mono mb-8">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                v1.0 Public Beta is Live
            </div>

            <h1 class="text-5xl md:text-7xl font-semibold tracking-tight text-white mb-6 leading-[1.1]">
                One Admin Panel for All Your <br class="hidden md:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Side-Project Databases</span>
            </h1>

            <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                View, debug, and analyze your databases without building admin panels again.
                Secure, read-only connections for your peace of mind.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white font-medium rounded-lg transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 group">
                    Connect Your Database
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <button class="w-full sm:w-auto px-8 py-3.5 glass-card text-white font-medium rounded-lg hover:bg-white/10 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4 text-slate-400"></i>
                    View Interactive Demo
                </button>
            </div>

            <!-- Hero Visual -->
            <div class="relative w-full max-w-4xl mx-auto h-[400px] md:h-[500px] mt-12">
                <!-- Central Hub -->
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-b from-slate-800 to-navy-900 rounded-2xl border border-white/10 shadow-2xl flex flex-col items-center justify-center z-20">
                    <div class="w-16 h-16 bg-indigo-500 rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/30">
                        <i data-lucide="layout-dashboard" class="w-8 h-8 text-white"></i>
                    </div>
                    <div class="text-sm font-medium text-white mb-1">AdminBase</div>
                    <div class="text-xs text-slate-500 font-mono">status: connected</div>
                    <div class="w-48 mt-4 space-y-2">
                        <div class="h-1.5 w-full bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full w-2/3 bg-cyan-500 rounded-full"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-500 font-mono">
                            <span>QPS: 124</span>
                            <span>Latency: 24ms</span>
                        </div>
                    </div>
                </div>

                <!-- Floating Cards (Left) -->
                <div class="absolute left-0 md:left-10 top-20 animate-float z-10 hidden md:block">
                    <div class="glass-card p-4 rounded-xl flex items-center gap-3 w-48 border-l-4 border-l-blue-500">
                        <div class="w-8 h-8 rounded bg-blue-900/50 flex items-center justify-center text-blue-400">
                            <i data-lucide="database" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-200">PostgreSQL</div>
                            <div class="text-[10px] font-mono text-emerald-400">● Active</div>
                        </div>
                    </div>
                </div>

                <!-- Floating Cards (Right) -->
                <div class="absolute right-0 md:right-10 bottom-32 animate-float-delayed z-10 hidden md:block">
                    <div class="glass-card p-4 rounded-xl flex items-center gap-3 w-48 border-l-4 border-l-orange-500">
                        <div class="w-8 h-8 rounded bg-orange-900/50 flex items-center justify-center text-orange-400">
                            <i data-lucide="server" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-200">MySQL / AWS</div>
                            <div class="text-[10px] font-mono text-emerald-400">● Active</div>
                        </div>
                    </div>
                </div>

                <!-- Floating Cards (Top Right) -->
                <div class="absolute right-20 top-0 animate-float z-10 hidden md:block opacity-70 scale-90">
                    <div class="glass-card p-3 rounded-lg flex items-center gap-3 w-40 border-l-4 border-l-emerald-500">
                        <div class="w-8 h-8 rounded bg-emerald-900/50 flex items-center justify-center text-emerald-400">
                            <i data-lucide="cloud" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-200">Supabase</div>
                        </div>
                    </div>
                </div>

                <!-- Connection Lines (SVG) -->
                <svg class="absolute inset-0 w-full h-full -z-10 pointer-events-none stroke-white/10 hidden md:block" fill="none">
                    <path d="M220 120 C 350 120, 350 250, 430 250" stroke-width="1.5" stroke-dasharray="4 4"></path>
                    <path d="M680 380 C 550 380, 550 250, 470 250" stroke-width="1.5" stroke-dasharray="4 4"></path>
                </svg>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="py-24 bg-navy-900/50 border-y border-white/5">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-sm font-mono text-indigo-400 mb-2">THE PROBLEM</h2>
                <h3 class="text-3xl font-semibold tracking-tight text-white">Why build another admin panel?</h3>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl bg-[#0B0E14] border border-white/5 hover:border-white/10 transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="refresh-cw" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white mb-3">Endless Rebuilding</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Wasting weekends coding basic CRUD views for every new side project instead of focusing on the product core.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-[#0B0E14] border border-white/5 hover:border-white/10 transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="eye-off" class="w-6 h-6 text-orange-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white mb-3">No Central View</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Jumping between 5 different browser tabs, CLI terminals, and local clients just to check user signups.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-[#0B0E14] border border-white/5 hover:border-white/10 transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-yellow-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="shield-alert" class="w-6 h-6 text-yellow-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white mb-3">Security Risks</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Sharing raw credentials or leaving ports open just to get quick access to production data.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-24 max-w-7xl mx-auto px-6">
        <div class="mb-16">
            <h2 class="text-3xl font-semibold tracking-tight text-white">From Database to Dashboard in seconds</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-12 relative">
            <div class="hidden md:block absolute top-12 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent"></div>

            <div class="relative pt-8">
                <div class="absolute top-0 left-0 -mt-3 w-6 h-6 rounded-full bg-navy-900 border-2 border-indigo-500 z-10"></div>
                <div class="glass-card p-6 rounded-xl h-full hover:-translate-y-1 transition-transform duration-300">
                    <div class="font-mono text-xs text-indigo-400 mb-4">STEP 01</div>
                    <div class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center mb-4 text-white">
                        <i data-lucide="plug" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white mb-2">Connect Database</h4>
                    <p class="text-sm text-slate-400">Securely connect via connection string. We verify read-only access by default.</p>
                </div>
            </div>

            <div class="relative pt-8">
                <div class="absolute top-0 left-1/2 -mt-3 w-6 h-6 rounded-full bg-navy-900 border-2 border-indigo-500 z-10"></div>
                <div class="glass-card p-6 rounded-xl h-full hover:-translate-y-1 transition-transform duration-300">
                    <div class="font-mono text-xs text-indigo-400 mb-4">STEP 02</div>
                    <div class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center mb-4 text-white">
                        <i data-lucide="wand-2" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white mb-2">Auto-Generate Views</h4>
                    <p class="text-sm text-slate-400">AdminBase scans your schema and builds intelligent table views, filters, and relationship links automatically.</p>
                </div>
            </div>

            <div class="relative pt-8">
                <div class="absolute top-0 right-0 -mt-3 w-6 h-6 rounded-full bg-navy-900 border-2 border-indigo-500 z-10"></div>
                <div class="glass-card p-6 rounded-xl h-full hover:-translate-y-1 transition-transform duration-300">
                    <div class="font-mono text-xs text-indigo-400 mb-4">STEP 03</div>
                    <div class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center mb-4 text-white">
                        <i data-lucide="layout" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-lg font-medium text-white mb-2">Manage Everything</h4>
                    <p class="text-sm text-slate-400">Switch between projects instantly. Query, export, and visualize data from a single unified interface.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-navy-900/30">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="col-span-1 lg:col-span-2 glass-card p-8 rounded-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i data-lucide="table-2" class="w-48 h-48"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 bg-indigo-500/20 rounded-lg flex items-center justify-center mb-4 text-indigo-400">
                            <i data-lucide="table" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Smart Table Viewer</h3>
                        <p class="text-slate-400 text-sm max-w-md">Not just a raw dump. We detect foreign keys to create clickable links, render JSON fields beautifully, and allow quick inline filtering without writing SQL.</p>

                        <div class="mt-8 bg-[#0B0E14] rounded-lg border border-white/10 p-4 font-mono text-xs text-slate-400">
                            <div class="flex border-b border-white/5 pb-2 mb-2 text-indigo-400">
                                <span class="w-16">ID</span>
                                <span class="w-32">User</span>
                                <span class="flex-1">Status</span>
                            </div>
                            <div class="flex py-1">
                                <span class="w-16 text-slate-500">#8492</span>
                                <span class="w-32 text-white">alex@dev.io</span>
                                <span class="flex-1 text-emerald-400 bg-emerald-400/10 px-2 rounded w-fit">active</span>
                            </div>
                            <div class="flex py-1">
                                <span class="w-16 text-slate-500">#8493</span>
                                <span class="w-32 text-white">sarah@co.com</span>
                                <span class="flex-1 text-orange-400 bg-orange-400/10 px-2 rounded w-fit">pending</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-8 rounded-2xl relative overflow-hidden">
                    <div class="w-10 h-10 bg-cyan-500/20 rounded-lg flex items-center justify-center mb-4 text-cyan-400">
                        <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Instant Analytics</h3>
                    <p class="text-slate-400 text-sm">Visualize new signups, revenue, or error logs with one-click chart generation.</p>
                </div>

                <div class="glass-card p-8 rounded-2xl relative overflow-hidden">
                    <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center mb-4 text-emerald-400">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Multi-Project</h3>
                    <p class="text-slate-400 text-sm">Indie hacking involves many bets. Manage DBs for 5+ projects in one sidebar.</p>
                </div>

                <div class="col-span-1 lg:col-span-2 glass-card p-8 rounded-2xl relative overflow-hidden flex items-center justify-between">
                    <div>
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mb-4 text-white">
                            <i data-lucide="code-2" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Developer Friendly</h3>
                        <p class="text-slate-400 text-sm max-w-xs">Shortcuts (Cmd+K), dark mode by default, and monospace fonts where it matters.</p>
                    </div>
                    <div class="hidden sm:block bg-[#0B0E14] p-4 rounded-lg border border-white/10 text-[10px] font-mono text-slate-500 w-64 opacity-80">
                        <div class="flex gap-1 mb-2">
                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        </div>
                        <p><span class="text-indigo-400">SELECT</span> * <span class="text-indigo-400">FROM</span> users</p>
                        <p><span class="text-indigo-400">WHERE</span> created_at &gt; <span class="text-emerald-400">NOW()</span></p>
                        <p><span class="text-indigo-400">LIMIT</span> 10;</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section id="security" class="py-24 bg-gradient-to-b from-[#0B0E14] to-navy-900 border-t border-white/5">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-xs font-mono mb-6">
                        <i data-lucide="lock" class="w-3 h-3"></i> Security First
                    </div>
                    <h2 class="text-3xl font-semibold tracking-tight text-white mb-6">Your data stays yours. <br>We just visualize it.</h2>

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="mt-1 bg-emerald-500/10 p-1.5 rounded text-emerald-400 h-fit">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-medium mb-1">Read-only Credentials</h4>
                                <p class="text-sm text-slate-400">We encourage using read-only database users. We can't drop tables if we don't have permission.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="mt-1 bg-emerald-500/10 p-1.5 rounded text-emerald-400 h-fit">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-medium mb-1">Encrypted Secrets</h4>
                                <p class="text-sm text-slate-400">Connection strings are AES-256 encrypted and stored securely. We never log your data.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="mt-1 bg-emerald-500/10 p-1.5 rounded text-emerald-400 h-fit">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-medium mb-1">Self-Host Option</h4>
                                <p class="text-sm text-slate-400">Want full control? Self-host AdminBase for free on your own infrastructure.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-1 relative flex justify-center">
                    <div class="absolute inset-0 bg-emerald-500/20 blur-[80px] rounded-full"></div>
                    <i data-lucide="shield-check" class="relative z-10 w-64 h-64 text-emerald-400 drop-shadow-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Section -->
    <section class="py-24 max-w-4xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-2xl font-semibold text-white">Why AdminBase?</h2>
        </div>

        <div class="overflow-hidden rounded-xl border border-white/10">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-white/5 text-slate-200 border-b border-white/10">
                        <th class="p-4 font-medium">Feature</th>
                        <th class="p-4 font-medium text-indigo-400">AdminBase</th>
                        <th class="p-4 font-medium text-slate-500">Metabase/Retool</th>
                        <th class="p-4 font-medium text-slate-500">Custom Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-400">
                    <tr>
                        <td class="p-4 text-slate-200">Setup Time</td>
                        <td class="p-4 text-emerald-400 font-mono">2 minutes</td>
                        <td class="p-4">Hours (Docker/Cloud)</td>
                        <td class="p-4">Days/Weeks</td>
                    </tr>
                    <tr>
                        <td class="p-4 text-slate-200">Maintenance</td>
                        <td class="p-4 text-emerald-400">Zero</td>
                        <td class="p-4">Self-hosting updates</td>
                        <td class="p-4">Endless dependency hell</td>
                    </tr>
                    <tr>
                        <td class="p-4 text-slate-200">Multi-DB Support</td>
                        <td class="p-4 text-emerald-400 flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> Native</td>
                        <td class="p-4">Complex config</td>
                        <td class="p-4">Manual implementation</td>
                    </tr>
                    <tr>
                        <td class="p-4 text-slate-200">Cost</td>
                        <td class="p-4 text-emerald-400">Free / $7.99 Pro</td>
                        <td class="p-4">Expensive / Heavy resource usage</td>
                        <td class="p-4">Your hourly rate $$</td>
                    </tr>
                    <tr>
                        <td class="p-4 text-slate-200">Self-Hosted</td>
                        <td class="p-4 text-emerald-400 flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> Free forever</td>
                        <td class="p-4">Complex setup</td>
                        <td class="p-4">You build it</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-navy-900/30 border-t border-white/5">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-sm font-mono text-indigo-400 mb-2">PRICING</h2>
                <h3 class="text-3xl font-semibold tracking-tight text-white mb-4">Simple, transparent pricing</h3>
                <p class="text-slate-400">Start free, upgrade when you need more.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- Free Plan -->
                <div class="glass-card p-8 rounded-2xl relative">
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-white mb-2">Free</h4>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-bold text-white">$0</span>
                            <span class="text-slate-500">/forever</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mb-6">Perfect for trying out AdminBase on your side projects.</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Up to 2 projects
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            2 database connections
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            1,000 rows per query
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Community support
                        </li>
                    </ul>
                    <a href="{{ route('login') }}" class="block w-full py-3 text-center rounded-lg border border-white/10 text-white font-medium hover:bg-white/5 transition-colors">
                        Get Started Free
                    </a>
                </div>

                <!-- Pro Plan -->
                <div class="glass-card p-8 rounded-2xl relative border-indigo-500/50 bg-indigo-500/5">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-indigo-500 text-white text-xs font-medium rounded-full">
                        Most Popular
                    </div>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-white mb-2">Pro</h4>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-bold text-white">$7.99</span>
                            <span class="text-slate-500">/month</span>
                        </div>
                        <p class="text-xs text-indigo-400 mt-1">or $87.89/year (1 month free)</p>
                    </div>
                    <p class="text-sm text-slate-400 mb-6">For developers serious about their side projects.</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Unlimited projects
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Unlimited connections
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Multiple team members
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Unlimited rows
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Data export (CSV, JSON)
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Priority email support
                        </li>
                    </ul>
                    <a href="{{ route('login') }}" class="block w-full py-3 text-center rounded-lg bg-indigo-500 text-white font-medium hover:bg-indigo-400 transition-colors">
                        Start Pro Trial
                    </a>
                </div>

                <!-- Self-Hosted Plan -->
                <div class="glass-card p-8 rounded-2xl relative">
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-white mb-2">Self-Hosted</h4>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-bold text-white">$0</span>
                            <span class="text-slate-500">/forever</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mb-6">Full control on your own infrastructure.</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            All features included
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Unlimited everything
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Your server, your data
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Community support
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            Open source
                        </li>
                    </ul>
                    <a href="https://github.com" target="_blank" class="block w-full py-3 text-center rounded-lg border border-white/10 text-white font-medium hover:bg-white/5 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="github" class="w-4 h-4"></i>
                        View on GitHub
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-32 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-indigo-900/20 via-[#0B0E14] to-[#0B0E14]"></div>
        <div class="relative z-10 max-w-3xl mx-auto text-center px-6">
            <h2 class="text-4xl md:text-5xl font-semibold tracking-tight text-white mb-6">Stop Rebuilding Admin Panels.</h2>
            <p class="text-slate-400 text-lg mb-10">
                Focus on your product. Let us handle the boring database management interface.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white text-black font-semibold rounded-lg hover:bg-slate-200 transition-colors shadow-[0_0_40px_-10px_rgba(255,255,255,0.3)]">
                    Get Started in 2 Minutes
                </a>
            </div>
            <p class="mt-6 text-xs text-slate-600 font-mono">No credit card required for hobby plan.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-[#0B0E14] py-12">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between gap-8">
            <div class="col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-6 h-6 rounded bg-gradient-to-tr from-indigo-500 to-cyan-500 flex items-center justify-center text-white">
                        <i data-lucide="database" class="w-3 h-3"></i>
                    </div>
                    <span class="font-semibold tracking-tight text-white">AdminBase</span>
                </div>
                <p class="text-sm text-slate-500">
                    Built by developers, for developers.<br>
                    © {{ date('Y') }} AdminBase
                </p>
            </div>

            <div class="flex gap-16 text-sm">
                <div>
                    <h4 class="font-semibold text-white mb-4">Product</h4>
                    <ul class="space-y-2 text-slate-500">
                        <li><a href="#features" class="hover:text-indigo-400 transition-colors">Features</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Integrations</a></li>
                        <li><a href="#pricing" class="hover:text-indigo-400 transition-colors">Pricing</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Changelog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4">Resources</h4>
                    <ul class="space-y-2 text-slate-500">
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Documentation</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">API Reference</a></li>
                        <li><a href="#security" class="hover:text-indigo-400 transition-colors">Security</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4">Legal</h4>
                    <ul class="space-y-2 text-slate-500">
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Privacy</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Terms</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>
