<nav class="mb-6 flex gap-2 overflow-x-auto pb-1">
    <a class="nav-pill {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Tổng quan</a>
    <a class="nav-pill {{ request()->routeIs('admin.records.*') ? 'active' : '' }}" href="{{ route('admin.records.index') }}">Bản ghi</a>
    <a class="nav-pill" href="{{ route('admin.export', request()->query()) }}">Xuất CSV</a>
</nav>
