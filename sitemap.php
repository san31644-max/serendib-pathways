<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/xml; charset=UTF-8');

$base = 'https://serendibpathways.com';
$urls = [
    ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => $base . '/destinations.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => $base . '/packages.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => $base . '/activities.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => $base . '/about.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => $base . '/contact.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
];

try {
    $db = (new Database())->getConnection();
    foreach ($db->query('SELECT id, created_at FROM destinations ORDER BY id') as $row) {
        $urls[] = ['loc' => $base . '/destination-detail.php?id=' . (int) $row['id'], 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => substr((string) $row['created_at'], 0, 10)];
    }
    foreach ($db->query('SELECT id, created_at FROM packages ORDER BY id') as $row) {
        $urls[] = ['loc' => $base . '/package-detail.php?id=' . (int) $row['id'], 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => substr((string) $row['created_at'], 0, 10)];
    }
} catch (Throwable $error) {
    error_log('Sitemap database error: ' . $error->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as $url) {
    echo "  <url>\n    <loc>", htmlspecialchars($url['loc'], ENT_XML1), "</loc>\n";
    if (!empty($url['lastmod'])) echo '    <lastmod>', htmlspecialchars($url['lastmod'], ENT_XML1), "</lastmod>\n";
    echo '    <changefreq>', $url['changefreq'], "</changefreq>\n";
    echo '    <priority>', $url['priority'], "</priority>\n  </url>\n";
}
echo '</urlset>', "\n";
