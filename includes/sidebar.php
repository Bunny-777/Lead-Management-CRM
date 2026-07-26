<?php
// includes/sidebar.php
$currentUser = getCurrentUser();
$isAdmin = isAdmin();
$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
        <span>CRM</span> Lead System
    </div>

    <ul class="sidebar-menu">
        <li class="menu-category">Main Navigation</li>
        
        <?php if ($isAdmin): ?>
            <li class="<?php echo ($currentScript === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/dashboard.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
            </li>

            <li class="menu-category">Master Data</li>
            <li class="<?php echo ($currentScript === 'countries.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/countries.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                    Countries
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'states.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/states.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    States
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'cities.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/cities.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Cities
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'lead_types.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/lead_types.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    Lead Types
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'lead_statuses.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/lead_statuses.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Lead Statuses
                </a>
            </li>

            <li class="menu-category">User & Lead Management</li>
            <li class="<?php echo ($currentScript === 'users.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/users.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Users Management
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'lead_create.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/lead_create.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Create Lead
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'lead_list.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/lead_list.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    All Leads
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'reports.php') ? 'active' : ''; ?>">
                <a href="/CRM%20P/admin/reports.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Reports Module
                </a>
            </li>

        <?php else: ?>
            <!-- User Panel Menu -->
            <li class="<?php echo ($currentScript === 'dashboard.php' && strpos($_SERVER['REQUEST_URI'], '/user/') !== false) ? 'active' : ''; ?>">
                <a href="/CRM%20P/user/dashboard.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    User Dashboard
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'lead_create.php' && strpos($_SERVER['REQUEST_URI'], '/user/') !== false) ? 'active' : ''; ?>">
                <a href="/CRM%20P/user/lead_create.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Create Lead
                </a>
            </li>
            <li class="<?php echo ($currentScript === 'lead_list.php' && strpos($_SERVER['REQUEST_URI'], '/user/') !== false) ? 'active' : ''; ?>">
                <a href="/CRM%20P/user/lead_list.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    My Leads
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>
