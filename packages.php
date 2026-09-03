<?php
$page_title = "Packages - Serendib Pathways";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Initially fetch all packages (optional - used for initial load)
$query = "SELECT * FROM packages ORDER BY name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-cover bg-center bg-no-repeat text-white py-20" style="background-image: url('assets/packages.jpg');">
    <div class="absolute inset-0 bg-gradient-to-r from-green-800 via-green-600 to-blue-600 opacity-30"></div>
    <div class="relative container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Tour Packages</h1>
        <p class="text-xl max-w-3xl mx-auto">Carefully crafted eco-friendly tour packages for every type of traveler</p>
    </div>
</section>

<!-- AJAX Search Bar -->
<section class="py-8 bg-white shadow-sm">
    <div class="container mx-auto px-4 max-w-lg mx-auto">
        <div class="relative">
            <input
                type="text"
                id="searchInput"
                placeholder="Search packages by name, description or destinations..."
                class="w-full pl-4 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <i class="fas fa-search absolute right-4 top-3 text-gray-400"></i>
        </div>
    </div>
</section>

<!-- Packages Grid -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div id="packagesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($packages)): ?>
                <p class="text-center text-gray-600 col-span-full">No packages found.</p>
            <?php else: ?>
                <?php foreach ($packages as $package): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                        <div class="h-48 bg-gradient-to-r from-green-400 to-blue-500 relative"
                             style="background-image: url('<?php echo htmlspecialchars($package['image'] ?: ''); ?>'); background-size: cover; background-position: center;">
                            <div class="absolute top-4 right-4 bg-white px-2 py-1 rounded-full text-sm font-semibold text-gray-800">
                                <?php echo htmlspecialchars($package['duration']); ?>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($package['description']); ?></p>

                            <?php if ($package['destinations']): ?>
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-800 mb-2">Destinations Included:</h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($package['destinations']); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="border-t pt-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center text-yellow-500">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <span class="ml-2 text-gray-600 text-sm">(4.8)</span>
                                    </div>
                                </div>
                                <a href="package-detail.php?id=<?php echo $package['id']; ?>" class="block w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold transition duration-300 text-center">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Optional: Pagination or Load More Button can be added here -->

    </div>
</section>

<!-- jQuery for AJAX, you can use vanilla JS fetch if preferred -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var ajaxRequest;

    $('#searchInput').on('input', function() {
        var searchTerm = $(this).val();

        // Cancel previous request if already ongoing
        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        ajaxRequest = $.ajax({
            url: 'packages-filter.php',
            method: 'POST',
            data: { search: searchTerm },
            success: function(response) {
                $('#packagesGrid').html(response);
            },
            error: function() {
                $('#packagesGrid').html('<p class="text-center text-red-600 col-span-full">Failed to load packages.</p>');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
