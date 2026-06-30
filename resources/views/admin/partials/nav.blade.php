<nav class="mb-6 flex gap-2 overflow-x-auto pb-1">
    <a class="nav-pill {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Tổng quan</a>
    <a class="nav-pill {{ request()->routeIs('admin.floors') ? 'active' : '' }}" href="{{ route('admin.floors') }}">Theo tầng</a>
    <a class="nav-pill {{ request()->routeIs('admin.floor-config') ? 'active' : '' }}" href="{{ route('admin.floor-config') }}">Cấu hình</a>
    <a class="nav-pill {{ request()->routeIs('admin.records.*') ? 'active' : '' }}" href="{{ route('admin.records.index') }}">Bản ghi</a>
    <a class="nav-pill {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}" href="{{ route('admin.teams.index') }}">Nhóm</a>
    <a class="nav-pill" href="{{ route('admin.export', request()->query()) }}">Xuất CSV</a>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button class="nav-pill" type="submit">Thoát</button>
    </form>
</nav>
