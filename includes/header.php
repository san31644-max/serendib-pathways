<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_title) ? str_replace('Serendib Pathways', 'Serendib Pathways', $page_title) : 'Serendib Pathways | Sri Lanka, beautifully explored';
$nav_items = ['index.php'=>'Home','about.php'=>'Our Story','destinations.php'=>'Destinations','packages.php'=>'Journeys','activities.php'=>'Experiences','contact.php'=>'Contact'];
$site_url = 'https://serendibpathways.com';
$seo_descriptions = [
    'index.php' => 'Discover Sri Lanka with Serendib Pathways. Explore private tours, authentic local experiences, wildlife, beaches, heritage cities and tailor-made journeys.',
    'about.php' => 'Meet Serendib Pathways, a Sri Lankan travel company creating thoughtful, locally guided and responsible journeys across the island.',
    'destinations.php' => 'Explore Sri Lanka destinations including Sigiriya, Kandy, Ella, Galle, Yala, beaches, ancient cities, tea country and national parks.',
    'packages.php' => 'Discover thoughtfully designed Sri Lanka tour packages with day-by-day itineraries, private transport, local guides and memorable stays.',
    'activities.php' => 'Find unforgettable Sri Lanka experiences, from wildlife safaris and hiking to culture, wellness, food and water adventures.',
    'contact.php' => 'Contact Serendib Pathways to plan a personalised Sri Lanka holiday with local destination specialists.',
    'photo-credits.php' => 'Photography sources and credits for destination imagery used by Serendib Pathways.',
];
$page_description = $page_description ?? ($seo_descriptions[$current_page] ?? 'Plan an unforgettable Sri Lanka journey with Serendib Pathways, your local travel specialist.');
$canonical_path = $current_page === 'index.php' ? '/' : '/' . $current_page;
if (in_array($current_page, ['destination-detail.php', 'package-detail.php'], true) && isset($_GET['id'])) {
    $canonical_path .= '?id=' . max(0, (int) $_GET['id']);
}
$canonical_url = $site_url . $canonical_path;
$page_image = $page_image ?? 'assets/serendib-pathways-horizontal.png';
$page_image_url = preg_match('~^https?://~i', $page_image) ? $page_image : $site_url . '/' . ltrim($page_image, '/');
$travel_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'TravelAgency',
    'name' => 'Serendib Pathways',
    'url' => $site_url . '/',
    'logo' => $site_url . '/assets/serendib-pathways-logo.png',
    'image' => $page_image_url,
    'email' => 'hello@serendibpathways.com',
    'description' => 'Locally guided, tailor-made journeys and travel experiences across Sri Lanka.',
    'areaServed' => ['@type' => 'Country', 'name' => 'Sri Lanka'],
];
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="<?= htmlspecialchars($page_description, ENT_QUOTES) ?>"><meta name="robots" content="index,follow,max-image-preview:large"><meta name="theme-color" content="#10261f">
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="canonical" href="<?= htmlspecialchars($canonical_url, ENT_QUOTES) ?>"><meta property="og:type" content="website"><meta property="og:site_name" content="Serendib Pathways"><meta property="og:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES) ?>"><meta property="og:description" content="<?= htmlspecialchars($page_description, ENT_QUOTES) ?>"><meta property="og:url" content="<?= htmlspecialchars($canonical_url, ENT_QUOTES) ?>"><meta property="og:image" content="<?= htmlspecialchars($page_image_url, ENT_QUOTES) ?>"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?= htmlspecialchars($page_title, ENT_QUOTES) ?>"><meta name="twitter:description" content="<?= htmlspecialchars($page_description, ENT_QUOTES) ?>"><meta name="twitter:image" content="<?= htmlspecialchars($page_image_url, ENT_QUOTES) ?>"><script type="application/ld+json"><?= json_encode($travel_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<link rel="icon" type="image/png" href="assets/serendib-pathways-logo.png"><link rel="apple-touch-icon" href="assets/serendib-pathways-logo.png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Great+Vibes&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"><script src="https://cdn.tailwindcss.com"></script><link href="assets/css/style.css?v=18" rel="stylesheet"><link href="assets/css/catalog.css?v=1" rel="stylesheet"><?php if (!empty($extra_stylesheet)): ?><link href="<?= htmlspecialchars($extra_stylesheet, ENT_QUOTES) ?>" rel="stylesheet"><?php endif; ?></head>
<body><a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header" id="site-header"><div class="nav-shell"><a class="brand" href="index.php" aria-label="Serendib Pathways home"><span class="brand-scene"><img src="assets/serendib-pathways-transparent.png" alt=""></span><span class="brand-name"><strong>Serendib</strong><small>Pathways</small></span></a>
<nav class="desktop-nav" aria-label="Primary navigation"><?php foreach($nav_items as $url=>$label): ?><a href="<?= $url ?>" <?= $current_page===$url?'class="active" aria-current="page"':'' ?>><?= $label ?></a><?php endforeach; ?></nav>
<a class="nav-cta" href="contact.php">Plan my trip <i class="fa-solid fa-arrow-up-right-from-square"></i></a><button class="menu-toggle" id="menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span></button></div>
<div class="mobile-nav" id="mobile-nav"><?php foreach($nav_items as $url=>$label): ?><a href="<?= $url ?>"><?= $label ?></a><?php endforeach; ?><a class="mobile-cta" href="contact.php">Start planning</a></div></header><main id="main-content">
