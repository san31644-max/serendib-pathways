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

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-cover bg-center bg-no-repeat text-white py-20" style="background-image: url('assets/about-6.jpg');">
    <div class="absolute inset-0 bg-gradient-to-r from-green-800 via-green-600 to-blue-600 opacity-30"></div>
    <div class="relative container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Explore Our Destinations</h1>
        <p class="text-xl max-w-3xl mx-auto">Discover the most beautiful and culturally rich locations across Sri Lanka</p>
    </div>
</section>

<!-- Search and Category Filter -->
<section class="py-8 bg-white shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search destinations..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            <div class="flex gap-4">
                <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</section>

<!-- Destinations Grid -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div id="destinationsGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            if (empty($destinations)):
                echo '<p class="text-center text-gray-600 col-span-full">No destinations found.</p>';
            else:
                foreach ($destinations as $destination):
            ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-r from-green-400 to-blue-500 relative" 
                     style="background-image: url('<?php echo htmlspecialchars($destination['image'] ?: '/placeholder.svg'); ?>'); background-size: cover; background-position: center;">
                    <div class="absolute top-4 right-4 bg-white px-2 py-1 rounded-full text-sm font-semibold text-green-600">
                        <?php echo htmlspecialchars($destination['category_name'] ?? 'Uncategorized'); ?>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($destination['name']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($destination['description'], 0, 120)) . '...'; ?></p>

                    <?php if (!empty($destination['highlights'])): ?>
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-800 mb-2">Highlights:</h4>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars(substr($destination['highlights'], 0, 100)) . '...'; ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($destination['category_name'] ?? 'Uncategorized'); ?></p>
                        </div>
                        <a href="destination-detail.php?id=<?php echo $destination['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition duration-300">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
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
