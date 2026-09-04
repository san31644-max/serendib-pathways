<?php
$page_title = "Destinations - Serendib Pathways";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch categories for filter dropdown
$categories_stmt = $db->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Initially load all destinations - or defer to AJAX load on page ready
$query = "SELECT d.*, c.category_name 
          FROM destinations d 
          LEFT JOIN categories c ON d.category_id = c.category_id 
          ORDER BY d.name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$extra_stylesheet = 'assets/css/destinations-showcase.css?v=2';
$extra_stylesheet_secondary = 'assets/css/destinations-visibility.css?v=1';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="destinations-hero" style="--hero-image:url('assets/about-6.jpg')">
    <div class="destinations-hero__veil"></div>
    <div class="catalog-shell destinations-hero__content">
        <span class="destinations-hero__eyebrow"><i></i> The island, revealed</span>
        <h1>Find your<br><em>Sri Lanka.</em></h1>
        <p>Sacred cities, mist-wrapped highlands and shores shaped by the Indian Ocean—choose the place that calls you.</p>
        <a href="#discover-destinations">Begin exploring <i class="fas fa-arrow-down"></i></a>
    </div>
</section>

<!-- Search and Category Filter -->
<section class="destination-discovery-bar" id="discover-destinations">
    <div class="catalog-shell">
        <div class="destination-search-row">
            <div class="destination-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Where would you like to go?" aria-label="Search destinations">
                    <span>SEARCH</span>
                </div>
                <select id="categoryFilter" class="destination-category" aria-label="Filter by category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
</section>

<!-- Destinations Grid -->
<section class="destinations-collection">
    <div class="catalog-shell">
        <div class="destinations-intro"><div><span>CURATED ACROSS THE ISLAND</span><h2>Places worth<br><em>feeling.</em></h2></div><p>Every destination opens into a full visual story, local context and thoughtful ways to experience it.</p></div>
        <div id="destinationsGrid" class="destination-card-grid">
            <?php
            if (empty($destinations)):
                echo '<p class="text-center text-gray-600 col-span-full">No destinations found.</p>';
            else:
                foreach ($destinations as $destination):
            ?>
            <a class="destination-card" href="destination-detail.php?id=<?php echo (int) $destination['id']; ?>">
                <img src="<?php echo htmlspecialchars($destination['image'] ?: 'assets/about-6.jpg'); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>">
                <div class="destination-card__shade"></div>
                <div class="destination-card__top"><span><?php echo htmlspecialchars($destination['category_name'] ?? 'Sri Lanka'); ?></span><b><?php echo str_pad((string) ($destination['id'] % 100), 2, '0', STR_PAD_LEFT); ?></b></div>
                <div class="destination-card__content">
                    <small><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($destination['location'] ?: 'Sri Lanka'); ?></small>
                    <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                    <p><?php echo htmlspecialchars(mb_substr($destination['description'], 0, 118)) . '…'; ?></p>
                    <span class="destination-card__link">Discover this place <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- Pagination placeholder, you can implement later -->
    </div>
</section>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    function fetchDestinations(categoryId = '', searchQuery = '') {
        $.ajax({
            url: 'destinations-filter.php',
            type: 'POST',
            data: {
                category_id: categoryId,
                search: searchQuery
            },
            success: function(response) {
                $('#destinationsGrid').html(response);
            },
            error: function() {
                $('#destinationsGrid').html('<p class="text-center text-red-600 col-span-full">Failed to load destinations.</p>');
            }
        });
    }

    // Fetch all initially
    fetchDestinations();

    $('#categoryFilter, #searchInput').on('change keyup', function() {
        const categoryId = $('#categoryFilter').val();
        const search = $('#searchInput').val();
        fetchDestinations(categoryId, search);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
