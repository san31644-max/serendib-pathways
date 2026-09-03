<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = isset($page_title) ? str_replace('Serendib Pathways', 'Serendib Pathways', $page_title) : 'Serendib Pathways | Sri Lanka, beautifully explored';
$nav_items = ['index.php'=>'Home','about.php'=>'Our Story','destinations.php'=>'Destinations','packages.php'=>'Journeys','activities.php'=>'Experiences','contact.php'=>'Contact'];
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Serendib Pathways creates thoughtful, locally guided journeys across Sri Lanka."><meta name="theme-color" content="#10261f">
<title><?= htmlspecialchars($page_title) ?></title>
<link rel="icon" type="image/png" href="assets/serendib-pathways-logo.png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Great+Vibes&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"><script src="https://cdn.tailwindcss.com"></script><link href="assets/css/style.css?v=17" rel="stylesheet"><link href="assets/css/catalog.css?v=1" rel="stylesheet"></head>
<body><a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header" id="site-header"><div class="nav-shell"><a class="brand" href="index.php" aria-label="Serendib Pathways home"><span class="brand-scene"><img src="assets/serendib-pathways-transparent.png" alt=""></span><span class="brand-name"><strong>Serendib</strong><small>Pathways</small></span></a>
<nav class="desktop-nav" aria-label="Primary navigation"><?php foreach($nav_items as $url=>$label): ?><a href="<?= $url ?>" <?= $current_page===$url?'class="active" aria-current="page"':'' ?>><?= $label ?></a><?php endforeach; ?></nav>
<a class="nav-cta" href="contact.php">Plan my trip <i class="fa-solid fa-arrow-up-right-from-square"></i></a><button class="menu-toggle" id="menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span></button></div>
<div class="mobile-nav" id="mobile-nav"><?php foreach($nav_items as $url=>$label): ?><a href="<?= $url ?>"><?= $label ?></a><?php endforeach; ?><a class="mobile-cta" href="contact.php">Start planning</a></div></header><main id="main-content">
