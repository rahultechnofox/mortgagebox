<li class="{{ Request::is('admin/home*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('home') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Dashboard</span></a>
</li>

<li class="{{ Request::is('admin/users*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/users') }}"><i data-feather="user"></i><span class="menu-title text-truncate" data-i18n="Users">Customers</span></a>
</li>

<li class="{{ Request::is('admin/advisors*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/advisors') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Advisors">Advisors</span></a>
</li>

<li class="{{ Request::is('admin/needList*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/needList') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Need List">Need List</span></a>
</li>

<li class="{{ Request::is('admin/companies*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/companies') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Companies">Companies</span></a>
</li>

<li class="{{ Request::is('admin/pages*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/pages') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Pages">Pages</span></a>
</li>

<li class="{{ Request::is('admin/services*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href="{{ route('admin/services') }}"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Services">Services</span></a>
</li>

<!-- <li class="navigation-header"><span data-i18n="Manage Material">Manage Material</span><i data-feather="more-horizontal"></i></li>
<li class=" nav-item has-sub {{ Request::is('admin/app/*') ? 'open':'' }}">
    <a class="d-flex align-items-center" href="#"><i data-feather="server"></i><span class="menu-title text-truncate" data-i18n="Manage Material">Manage Material</span><span class="badge badge-light-warning rounded-pill ms-auto me-1">6</span></a>
    <ul class="menu-content">
        <li class="{{ Request::is('admin/material_list*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Material list">Material List</span></a>
        </li>

        <li class="{{ Request::is('admin/category*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"> </i><span class="menu-title text-truncate" data-i18n="circle">Manage Category</span></a>
        </li>

        <li class="{{ Request::is('admin/keywords*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Keywords">Keywords</span></a>
        </li>

        <li class="{{ Request::is('admin/draft_book*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Draft Book">Draft Book</span></a>
        </li>

        <li class="{{ Request::is('admin/bulk*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Bulk upload">Bulk Upload</span></a>
        </li>

        <li class="{{ Request::is('admin/book_review*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Book reviews">Manage Book Reviews</span></a>
        </li>       
    </ul>
</li>    

<li class="navigation-header"><span data-i18n="Manage Institutes">Manage Institutes</span><i data-feather="more-horizontal"></i></li>
<li class=" nav-item has-sub {{ Request::is('admin/app/*') ? 'open':'' }}">
    <a class="d-flex align-items-center" href="#"><i data-feather="home"></i><span class="menu-title text-truncate" data-i18n="Manage Material">Manage Institutes</span><span class="badge badge-light-warning rounded-pill ms-auto me-1">5</span></a>
    <ul class="menu-content">
        <li class="{{ Request::is('') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="University">Pending University Approvals</span></a>
        </li>

        <li class="{{ Request::is('admin/university*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="University">Manage University</span></a>
        </li>
  
        <li class="{{ Request::is('admin/school*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="School">Manage School</span></a>
        </li>    

        <li class="{{ Request::is('admin/organization*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Organization">Manage Organization</span></a>
        </li>  

        <li class="{{ Request::is('admin/subscription*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Subscription">Subscription</span></a>
        </li>
    </ul>
</li>

<li class="{{ Request::is('admin/departments*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="clipboard"></i><span class="menu-title text-truncate" data-i18n="Departments">Departments/ Grade</span></a>
</li>

<li class="navigation-header"><span data-i18n="Manage Institutes">User Management</span><i data-feather="more-horizontal"></i></li>
<li class=" nav-item has-sub {{ Request::is('admin/app/*') ? 'open':'' }}">
    <a class="d-flex align-items-center" href="#"><i data-feather="user"></i><span class="menu-title text-truncate" data-i18n="Manage Material">User Management</span><span class="badge badge-light-warning rounded-pill ms-auto me-1">2</span></a>
    <ul class="menu-content">
        <li class="{{ Request::is('') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Users">Pending Id verification</span></a>
        </li>

        <li class="{{ Request::is('admin/users*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Users">User List</span></a>
        </li>
    </ul>
</li>


<li class="{{ Request::is('admin/discount*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="tag"></i><span class="menu-title text-truncate" data-i18n="Discount coupons">Discount Coupons</span></a>
</li>

<li class="{{ Request::is('admin/bookrequest*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="book"></i><span class="menu-title text-truncate" data-i18n="Book Requested">Book Requested</span></a>
</li>

<li class="{{ Request::is('admin/donation*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="dollar-sign"></i><span class="menu-title text-truncate" data-i18n="Track donation">Track Donation</span></a>
</li>

<li class="{{ Request::is('admin/verification*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="shield"></i><span class="menu-title text-truncate" data-i18n="Track donation">Pending verification</span></a>
</li>

<li class="{{ Request::is('admin/reviewer*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="message-square"></i><span class="menu-title text-truncate" data-i18n="Reviewer">Reviewer</span></a>
</li>

<li class="{{ Request::is('admin/commission*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="archive"></i><span class="menu-title text-truncate" data-i18n="Commission">Manage Commission</span></a>
</li>

<li class="{{ Request::is('admin/orders*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="shopping-cart"></i><span class="menu-title text-truncate" data-i18n="Orders">Manage Orders</span></a>
</li>

<li class="{{ Request::is('admin/contact_us*') ? 'active' : '' }} nav-item">
    <a class="d-flex align-items-center" href=""><i data-feather="message-circle"></i><span class="menu-title text-truncate" data-i18n="Contacts">Contact Us</span></a>
</li>

<li class="navigation-header"><span data-i18n="User Interface">Settings</span><i data-feather="more-horizontal"></i></li>
<li class=" nav-item has-sub {{ Request::is('admin/settings/app/*') ? 'open':'' }}">
    <a class="d-flex align-items-center" href="#"><i data-feather="settings"></i><span class="menu-title text-truncate" data-i18n="Settings">Settings</span><span class="badge badge-light-warning rounded-pill ms-auto me-1">7</span></a>
    <ul class="menu-content">
        <li class="{{ Request::is('admin/newstext*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="News Text">News Text</span></a>
        </li>

        <li class="{{ Request::is('admin/banners*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Banners">Banners</span></a>
        </li>
        <li class="{{ Request::is('admin/notifications*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Push Notification">Push Notification</span></a>
        </li>
        <li class="{{ Request::is('admin/site_contents*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="CMS">CMS</span></a>
        </li>

        <li class="{{ Request::is('admin/settings/app/globals*') ? 'active' : '' }}">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="eCommerce">App Settings</span></a>
        </li>

        <li class="{{ Request::is('admin/settings/app/websetting*') ? 'active' : '' }}">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-item text-truncate" data-i18n="eCommerce">Website Settings</span></a>
        </li>

        <li class="{{ Request::is('admin/settings/app/social*') ? 'active' : '' }} nav-item">
            <a class="d-flex align-items-center" href=""><i data-feather="circle"></i><span class="menu-title text-truncate" data-i18n="Social">Social</span></a>
        </li>
    </ul>
</li> -->


