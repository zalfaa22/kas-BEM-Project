
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Dashboard') · SGE Workspace Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="app-shell">

        {{-- SIDEBAR --}}
        <aside class="sidebar">

            {{-- BRAND --}}
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">S</span>

                <span>
                    <b>SGE Workspace</b>
                    <small>FINANCE PORTAL</small>
                </span>
            </a>

            <div class="side-label">WORKSPACE</div>

            <nav>

                {{-- OVERVIEW --}}
                <a
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}"
                >
                    <i>▦</i> Overview
                </a>

                {{-- MENU BENDAHARA --}}
                @if(auth()->user()->role === 'bendahara')

                    <div class="side-label">MANAGEMENT</div>

                    <a
                        class="nav-link {{ request()->routeIs('members*') ? 'active' : '' }}"
                        href="{{ route('members') }}"
                    >
                        <i>♧</i> Data Anggota
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('dues*') ? 'active' : '' }}"
                        href="{{ route('dues') }}"
                    >
                        <i>◷</i> Aturan Iuran
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('invoices*') ? 'active' : '' }}"
                        href="{{ route('invoices.index') }}"
                    >
                        <i>▤</i> Tagihan
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('payments*') ? 'active' : '' }}"
                        href="{{ route('payments') }}"
                    >
                        <i>⇧</i> Verifikasi Pembayaran

                        <span class="nav-count">
                            {{ \App\Models\Payment::where('status', 'menunggu_verifikasi')->count() }}
                        </span>
                    </a>

                    <div class="side-label">FINANCE</div>

                    <a
                        class="nav-link {{ request()->routeIs('my-invoices') ? 'active' : '' }}"
                        href="{{ route('my-invoices') }}"
                    >
                        <i>▤</i> Tagihan Pribadi
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('transactions*') ? 'active' : '' }}"
                        href="{{ route('transactions') }}"
                    >
                        <i>⇄</i> Transaksi Kas
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}"
                        href="{{ route('reports') }}"
                    >
                        <i>▥</i> Laporan
                    </a>

                {{-- MENU KETUA BEM --}}
                @elseif(auth()->user()->role === 'ketua')

                    <div class="side-label">MONITORING</div>

                    <a
                        class="nav-link {{ request()->routeIs('monitoring') ? 'active' : '' }}"
                        href="{{ route('monitoring') }}"
                    >
                        <i>▥</i> Monitoring Kas
                    </a>

                    <div class="side-label">MY FINANCE</div>

                    <a
                        class="nav-link {{ request()->routeIs('my-invoices') ? 'active' : '' }}"
                        href="{{ route('my-invoices') }}"
                    >
                        <i>▤</i> Tagihan Saya
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('reminders') ? 'active' : '' }}"
                        href="{{ route('reminders') }}"
                    >
                        <i>◷</i> Pengingat
                    </a>

                {{-- MENU ANGGOTA --}}
                @else

                    <div class="side-label">MY FINANCE</div>

                    <a
                        class="nav-link {{ request()->routeIs('my-invoices') ? 'active' : '' }}"
                        href="{{ route('my-invoices') }}"
                    >
                        <i>▤</i> Tagihan Saya
                    </a>

                    <a
                        class="nav-link {{ request()->routeIs('reminders') ? 'active' : '' }}"
                        href="{{ route('reminders') }}"
                    >
                        <i>◷</i> Pengingat
                    </a>

                @endif

                {{-- NOTIFIKASI --}}
                <a
                    class="nav-link {{ request()->routeIs('notices') ? 'active' : '' }}"
                    href="{{ route('notices') }}"
                >
                    <i>♧</i> Notifikasi
                </a>

            </nav>

            {{-- SIDEBAR BOTTOM --}}
            <div class="sidebar-bottom">

                <div class="help-card">
                    <span class="help-icon">✦</span>
                    <b>Need a hand?</b>

                    <p>
                        Kelola kas lebih rapi, transparan, dan mudah dipantau.
                    </p>
                </div>

                {{-- USER PROFILE --}}
                <div class="user-mini">

                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <div class="user-meta">
                        <b>{{ auth()->user()->name }}</b>
                        <small>{{ ucfirst(auth()->user()->role) }}</small>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="logout" title="Keluar">
                            ↗
                        </button>
                    </form>

                </div>
            </div>

        </aside>

        {{-- MAIN CONTENT --}}
        <main class="main">

            {{-- TOPBAR --}}
            <header class="topbar">

                <div>
                    <div class="crumb">
                        SGE WORKSPACE PORTAL
                        <span>/</span>
                        @yield('crumb', 'Overview')
                    </div>

                    <div class="mobile-brand">
                        SGE’s Workspace Portal
                    </div>
                </div>

                <div class="top-actions">

                    <span class="today">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>

                    <a class="icon-btn" href="{{ route('notices') }}">
                        ♧

                        @if(
                            \App\Models\Notice::where('user_id', auth()->id())
                                ->whereNull('read_at')
                                ->count()
                        )
                            <em></em>
                        @endif
                    </a>

                    <a
                        href="{{ route('profile') }}"
                        class="avatar"
                        title="Profil saya"
                    >
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </a>

                </div>

            </header>

            {{-- PAGE CONTENT --}}
            <div class="content">

                {{-- SUCCESS MESSAGE --}}
                @if(session('success'))
                    <div class="alert success">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                {{-- ERROR MESSAGE --}}
                @if(session('error'))
                    <div class="alert error">
                        ! {{ session('error') }}
                    </div>
                @endif

                {{-- VALIDATION ERROR --}}
                @if($errors->any())
                    <div class="alert error">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- DYNAMIC CONTENT --}}
                @yield('content')

                {{-- FOOTER --}}
                <footer>
                    © {{ date('Y') }} SGE Workspace Portal · BEM FILKOM UB
                </footer>

            </div>

        </main>

    </div>

    {{-- JAVASCRIPT --}}
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')

</body>
</html>
