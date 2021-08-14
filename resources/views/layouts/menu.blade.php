<li class="{{ Request::is('/') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ url('/') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Dashboard</span></a>
</li>

<li class="{{ Request::is('admin/users*') || Request::is('admin/users/show*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/users') }}"><i data-feather="user"></i><span class="menu-title text-truncate" data-i18n="Users">Customers</span></a>
</li>

<li class="{{ Request::is('admin/advisors*') || Request::is('admin/advisors/show*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/advisors') }}"><i data-feather="list"></i><span class="menu-title text-truncate" data-i18n="Professionals">Professionals</span></a>
</li>

<li class="{{ Request::is('admin/need*') || Request::is('admin/need/show*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/need') }}"><i data-feather="clipboard"></i><span class="menu-title text-truncate" data-i18n="Need List">Need List</span></a>
</li>

<li class="{{ Request::is('admin/companies*') || Request::is('admin/company/show*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/companies') }}"><i data-feather="database"></i><span class="menu-title text-truncate" data-i18n="Companies">Companies</span></a>
</li>

<li class="{{ Request::is('admin/pages*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/pages') }}"><i data-feather="book-open"></i><span class="menu-title text-truncate" data-i18n="Pages">Pages</span></a>
</li>

<li class="{{ Request::is('admin/services*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/services') }}"><i data-feather="settings"></i><span class="menu-title text-truncate" data-i18n="Services">Services</span></a>
</li>
<li class="{{ Request::is('admin/faq-category*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/faq-category') }}"><i data-feather="menu"></i><span class="menu-title text-truncate" data-i18n="Services">Faq Categories</span></a>
</li>
<li class="{{ Request::segment(2)=='faq' || Request::segment(3)=='faq' ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/faq') }}"><i data-feather="server"></i><span class="menu-title text-truncate" data-i18n="Services">Faq</span></a>
</li>
<li class="{{ Request::is('admin/setting*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ url('admin/setting/promotion') }}"><i data-feather="layout"></i><span class="menu-title text-truncate" data-i18n="Services">Promotion</span></a>
</li>