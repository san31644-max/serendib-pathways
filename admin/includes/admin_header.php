<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Admin Dashboard - Serendib Pathways</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f7f6f1 !important; color: #14241f; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; letter-spacing: -.025em; }
        header.bg-green-800 { background: #10261f !important; }
        .admin-sidebar { border-right: 1px solid rgba(20,36,31,.1); box-shadow: none !important; }
        .nav-item.active { background: #e7efc1 !important; color: #173e31 !important; }
        main .bg-white { border: 1px solid rgba(20,36,31,.08); box-shadow: 0 14px 45px rgba(20,36,31,.06) !important; }
        main button, main a.bg-green-600 { border-radius: 999px !important; background: #2f7657 !important; }
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 50;
                width: 250px;
                transition: transform 0.3s ease-in-out;
            }
            .admin-sidebar.active {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            .sidebar-overlay.active {
                display: block;
            }
        }
        .nav-item {
            transition: all 0.3s ease;
        }
        .nav-item:hover {
            transform: translateX(4px);
        }
        .nav-item.active {
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
        }
        .rotate-180 {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-white">
    <!-- Admin Header -->
    <header class="bg-green-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <button id="sidebar-toggle" class="md:hidden text-white text-xl mr-2 hover:text-green-300 transition duration-300">
                        <i class="fas fa-bars"></i>
                    </button>
                
                    <h1 class="text-xl md:text-2xl font-bold truncate">Serendib Pathways</h1>
                </div>
                <div class="flex items-center space-x-2 md:space-x-4">
                    <span class="text-green-300 hidden md:inline">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <div class="flex items-center space-x-2">
                        <a href="../index.php" class="bg-green-600 hover:bg-green-700 px-2 py-1 md:px-4 md:py-2 rounded-lg transition duration-300 text-sm md:text-base">
                            <i class="fas fa-globe mr-1"></i><span class="hidden md:inline">View Site</span>
                        </a>
                        <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-2 py-1 md:px-4 md:py-2 rounded-lg transition duration-300 text-sm md:text-base">
                            <i class="fas fa-sign-out-alt mr-1"></i><span class="hidden md:inline">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="admin-sidebar w-64 bg-white shadow-lg min-h-screen">
    <div class="flex justify-between items-center p-4 md:hidden border-b border-gray-200">
        <span class="font-semibold text-green-800">Admin Menu</span>
        <button id="sidebar-close" class="text-gray-500 hover:text-gray-700 transition duration-300">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    <nav class="p-4">
        <ul class="space-y-2">

            <li>
                <a href="dashboard.php" class="nav-item flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-green-50 hover:text-green-600 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-green-50 text-green-600 active' : ''; ?>">
                    <i class="fas fa-tachometer-alt text-lg"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
            </li>

            <li>
                <button 
                    type="button" 
                    aria-expanded="false"
                    class="nav-item flex items-center space-x-3 text-gray-700 p-3 rounded-lg w-full hover:bg-green-50 hover:text-green-600 transition duration-300 focus:outline-none"
                    id="destinations-toggle"
                >
                    <i class="fas fa-map-marker-alt text-lg"></i>
                    <span class="font-medium flex-1 text-left">Destinations</span>
                    <i class="fas fa-chevron-down text-sm mt-1 transition-transform duration-200" aria-hidden="true" id="destinations-chevron"></i>
                </button>

                <ul id="submenu-categories" class="ml-8 mt-2 space-y-1 hidden" aria-labelledby="destinations-toggle">
                    <li>
                        <a href="categories.php" class="nav-item flex items-center space-x-3 text-gray-700 p-2 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'bg-blue-50 text-blue-600 active' : ''; ?>">
                            <i class="fas fa-tags text-base"></i>
                            <span class="font-medium">Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="destinations.php" class="block mt-2 text-center bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-semibold text-sm transition duration-300">
                            <i class="fas fa-plus mr-2"></i> Add Destination
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="packages.php" class="nav-item flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-green-50 hover:text-green-600 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'bg-green-50 text-green-600 active' : ''; ?>">
                    <i class="fas fa-box text-lg"></i>
                    <span class="font-medium">Packages</span>
                </a>
            </li>

            <li>
                <a href="activities.php" class="nav-item flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-green-50 hover:text-green-600 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'activities.php' ? 'bg-green-50 text-green-600 active' : ''; ?>">
                    <i class="fas fa-hiking text-lg"></i>
                    <span class="font-medium">Activities</span>
                </a>
            </li>

            <li>
                <a href="messages.php" class="nav-item flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-green-50 hover:text-green-600 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'bg-green-50 text-green-600 active' : ''; ?>">
                    <i class="fas fa-envelope text-lg"></i>
                    <span class="font-medium">Messages</span>
                </a>
            </li>
            <li>
                <a href="human-chats.php" class="nav-item flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-green-50 hover:text-green-600 transition duration-300 <?php echo basename($_SERVER['PHP_SELF']) == 'human-chats.php' ? 'bg-green-50 text-green-600 active' : ''; ?>">
                    <i class="fas fa-headset text-lg"></i><span class="font-medium">Live Chats</span>
                </a>
            </li>
        </ul>
    </nav>

   
</aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-6 overflow-x-hidden">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('overflow-hidden');
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
    });
    if (overlay) overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
    });

    sidebar.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('overflow-hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('overflow-hidden');
        }
    });

    // Nav hover effect
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateX(4px)';
        });
        item.addEventListener('mouseleave', () => {
            item.style.transform = 'translateX(0)';
        });
    });

    // Destinations submenu toggle
    const destinationsToggle = document.getElementById('destinations-toggle');
    const submenuCategories = document.getElementById('submenu-categories');
    const chevronIcon = document.getElementById('destinations-chevron');

    if (destinationsToggle && submenuCategories && chevronIcon) {
        destinationsToggle.addEventListener('click', () => {
            submenuCategories.classList.toggle('hidden');

            const expanded = destinationsToggle.getAttribute('aria-expanded') === 'true';
            destinationsToggle.setAttribute('aria-expanded', !expanded);

            chevronIcon.classList.toggle('rotate-180');
        });

        // Open submenu by default if on categories.php or add-destination.php
        <?php if(in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'add-destination.php'])): ?>
            submenuCategories.classList.remove('hidden');
            destinationsToggle.setAttribute('aria-expanded', 'true');
            chevronIcon.classList.add('rotate-180');
        <?php endif; ?>
    }
});

// Enhanced sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('overflow-hidden');
    }

    // Open sidebar
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.classList.add('overflow-hidden');
    }

    // Close sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
    }

    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar when clicking on nav links (mobile)
    const navLinks = sidebar.querySelectorAll('nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                closeSidebar();
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            closeSidebar();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Add smooth transitions to nav items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
});
</script>
