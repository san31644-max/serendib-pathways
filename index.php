<?php
$page_title = "Serendib Pathways - Discover Sri Lanka's Natural Beauty";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get featured destinations
$query = "SELECT * FROM destinations ORDER BY created_at DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get featured packages
$query = "SELECT * FROM packages ORDER BY created_at DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ranking_blueprint = [
    ['Sigiriya', 'History & iconic views'],
    ['Ella', 'Mountains & nature'],
    ['Kandy', 'Culture & heritage'],
    ['Galle', 'Fort & coastal history'],
    ['Yala', 'Wildlife safari'],
    ['Nuwara Eliya', 'Tea country'],
    ['Mirissa', 'Beach & whale watching'],
    ['Bentota', 'Beach & water activities'],
    ['Anuradhapura', 'Ancient civilization'],
    ['Trincomalee', 'East coast beaches'],
];
$ranked_destinations = [];
$rank_stmt = $db->prepare('SELECT id,name,image,location FROM destinations WHERE name=? ORDER BY id LIMIT 1');
foreach ($ranking_blueprint as $index => [$name, $experience]) {
    $rank_stmt->execute([$name]);
    $place = $rank_stmt->fetch(PDO::FETCH_ASSOC);
    if ($place) {
        $place['rank'] = $index + 1;
        $place['experience'] = $experience;
        if ($name === 'Trincomalee') $place['display_name'] = 'Trincomalee / Nilaveli';
        $ranked_destinations[] = $place;
    }
}
$extra_stylesheet = 'assets/css/home-ranking.css?v=1';
include 'includes/header.php';
?>

    <!-- Hero Section with Responsive Aspect Ratios -->
    <section class="relative w-full">
        <!-- Container with aspect ratio - 10:15 on mobile, 16:9 on desktop -->
        <div class="relative w-full aspect-[10/15] md:aspect-[16/9] overflow-hidden">
            <!-- Video Background -->
            <video
                id="hero-video"
                autoplay
                loop
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover"
            >
                <source src="assets/master-hero.mp4" type="video/mp4" />
                Your browser does not support the video tag.
            </video>
            
            <!-- Background overlay -->
            <div class="absolute inset-0 bg-black opacity-40"></div>

            <button class="hero-sound-toggle" id="hero-sound-toggle" type="button" aria-label="Play video sound" aria-pressed="false">
                <i class="fa-solid fa-volume-xmark" aria-hidden="true"></i>
                <span>Play sound</span>
            </button>
            
            <!-- Content container - bottom left on mobile, centered on desktop -->
            <div class="absolute inset-0 flex items-end md:items-center justify-start md:justify-center">
                <div class="container mx-auto px-4 pb-8 md:pb-0 text-left md:text-center text-white">
                    <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">Discover Sri Lanka's Natural Wonders</h1>
                    <!-- Description hidden on mobile, shown on desktop -->
                    <p class="hidden md:block text-lg sm:text-xl md:text-2xl mb-6 md:mb-8 max-w-3xl mx-auto">Experience sustainable tourism in the pearl of the Indian Ocean. Explore pristine beaches, ancient temples, and lush tea plantations.</p>
                    
                    <!-- Mobile buttons - small and left aligned -->
                    <div class="flex md:hidden flex-col gap-2 mt-4">
                        <a href="packages.php" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm font-semibold transition duration-300 w-fit">View Packages</a>
                        <a href="destinations.php" class="border border-white hover:bg-white hover:text-green-600 px-4 py-2 rounded text-sm font-semibold transition duration-300 w-fit">Explore Destinations</a>
                    </div>
                    
                    <!-- Desktop buttons - centered and larger -->
                    <div class="hidden md:flex flex-col sm:flex-row justify-center gap-4 sm:space-x-4">
                        <a href="packages.php" class="bg-green-600 hover:bg-green-700 px-6 md:px-8 py-3 rounded-lg text-base md:text-lg font-semibold transition duration-300">View Packages</a>
                        <a href="destinations.php" class="border-2 border-white hover:bg-white hover:text-green-600 px-6 md:px-8 py-3 rounded-lg text-base md:text-lg font-semibold transition duration-300">Explore Destinations</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Mobile-Optimized Image Gallery Section -->
<section class="w-full">
    <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-0 sm:gap-0">
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-1.jpg" 
                 alt="Gallery image 1"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-2.jpg" 
                 alt="Gallery image 2"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-3.png" 
                 alt="Gallery image 3"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-4.jpg" 
                 alt="Gallery image 4"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-5.jpg" 
                 alt="Gallery image 5"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-6.jpg" 
                 alt="Gallery image 6"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-7.jpg" 
                 alt="Gallery image 7"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-8.jpg" 
                 alt="Gallery image 8"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-9.jpg" 
                 alt="Gallery image 9"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-10.jpg" 
                 alt="Gallery image 10"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-11.jpg" 
                 alt="Gallery image 11"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
        <div class="aspect-square overflow-hidden rounded-sm">
            <img src="assets/gallery-12.jpg" 
                 alt="Gallery image 12"
                 class="w-full h-full object-cover hover:scale-105 active:scale-98 transition-transform duration-300"
                 loading="lazy">
        </div>
    </div>
</section>

<!-- Signature Destination Ranking -->
<section class="home-ranking" aria-labelledby="ranking-title">
    <div class="catalog-shell">
        <div class="ranking-heading">
            <div><span class="ranking-kicker"><i></i> THE SERENDIB TEN</span><h2 id="ranking-title">Ten places.<br><em>One unforgettable island.</em></h2></div>
            <p>Our essential Sri Lanka collection—ranked by the experience each place delivers at its very best.</p>
        </div>

        <div class="ranking-podium">
            <?php foreach (array_slice($ranked_destinations, 0, 3) as $place): ?>
            <a class="ranking-hero ranking-hero--<?= (int) $place['rank'] ?>" href="destination-detail.php?id=<?= (int) $place['id'] ?>">
                <img src="<?= htmlspecialchars($place['image']) ?>" alt="<?= htmlspecialchars($place['name']) ?>" loading="lazy">
                <span class="ranking-hero__veil"></span>
                <span class="ranking-medal"><?= ['🥇','🥈','🥉'][$place['rank'] - 1] ?></span>
                <span class="ranking-hero__content"><small>NO. 0<?= (int) $place['rank'] ?> · <?= htmlspecialchars($place['experience']) ?></small><strong><?= htmlspecialchars($place['name']) ?></strong><b>Explore <i class="fas fa-arrow-right"></i></b></span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="ranking-list">
            <?php foreach (array_slice($ranked_destinations, 3) as $place): ?>
            <a class="ranking-row" href="destination-detail.php?id=<?= (int) $place['id'] ?>">
                <span class="ranking-row__number"><?= str_pad((string) $place['rank'], 2, '0', STR_PAD_LEFT) ?></span>
                <span class="ranking-row__photo"><img src="<?= htmlspecialchars($place['image']) ?>" alt="" loading="lazy"></span>
                <span class="ranking-row__name"><strong><?= htmlspecialchars($place['display_name'] ?? $place['name']) ?></strong><small><?= htmlspecialchars($place['location'] ?: 'Sri Lanka') ?></small></span>
                <span class="ranking-row__experience"><?= htmlspecialchars($place['experience']) ?></span>
                <i class="fas fa-arrow-up-right-from-square"></i>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="ranking-footer"><span>01—10 · Curated by Serendib Pathways</span><a href="destinations.php">Explore all destinations <i class="fas fa-arrow-right"></i></a></div>
    </div>
</section>


    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Why Choose Serendib Pathways?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">We're committed to sustainable tourism that preserves Sri Lanka's natural beauty for future generations.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-leaf text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Eco-Friendly</h3>
                    <p class="text-gray-600">All our tours are designed to minimize environmental impact while maximizing your experience.</p>
                </div>
                <div class="text-center p-6">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Local Guides</h3>
                    <p class="text-gray-600">Expert local guides who know the hidden gems and cultural significance of each destination.</p>
                </div>
                <div class="text-center p-6">
                    <div class="bg-yellow-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Premium Experience</h3>
                    <p class="text-gray-600">Carefully curated experiences that showcase the best of Sri Lankan culture and nature.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Destinations -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Featured Destinations</h2>
                <p class="text-xl text-gray-600">Discover the most breathtaking locations in Sri Lanka</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php if (empty($destinations)): ?>
                    <!-- Default destinations when database is empty -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="h-48 bg-gradient-to-r from-green-400 to-blue-500"></div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">Sigiriya Rock Fortress</h3>
                            <p class="text-gray-600 mb-4">Ancient rock fortress and palace ruins with stunning frescoes and panoramic views of the surrounding landscape.</p>
                            <a href="destinations.php" class="text-green-600 hover:text-green-800 font-semibold">Discover more &rarr;</a>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="h-48 bg-gradient-to-r from-blue-400 to-purple-500"></div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">Ella Tea Country</h3>
                            <p class="text-gray-600 mb-4">Rolling hills covered in tea plantations with breathtaking mountain views and cool climate.</p>
                            <a href="destinations.php" class="text-green-600 hover:text-green-800 font-semibold">Discover more &rarr;</a>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="h-48 bg-gradient-to-r from-yellow-400 to-orange-500"></div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">Yala National Park</h3>
                            <p class="text-gray-600 mb-4">Wildlife sanctuary home to leopards, elephants, and exotic birds in their natural habitat.</p>
                            <a href="destinations.php" class="text-green-600 hover:text-green-800 font-semibold">Discover more &rarr;</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($destinations as $destination): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="h-48 bg-gradient-to-r from-green-400 to-blue-500" style="background-image: url('<?php echo $destination['image'] ?: ''; ?>'); background-size: cover; background-position: center;"></div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($destination['name']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($destination['description'], 0, 100)) . '...'; ?></p>
                            <a href="destination-detail.php?id=<?php echo (int)$destination['id']; ?>" class="text-green-600 hover:text-green-800 font-semibold">Discover more &rarr;</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-green-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Explore Sri Lanka?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Join us for an unforgettable eco-friendly adventure through the beautiful landscapes of Ceylon.</p>
            <a href="contact.php" class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold transition duration-300">Get Started Today</a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
