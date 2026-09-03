<?php
$page_title = "Package Details - Serendib Pathways";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$package = null;

if ($package_id) {
    $query = "SELECT * FROM packages WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$package_id]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Redirect if package not found
if (!$package) {
    header('Location: packages.php');
    exit;
}

include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-green-600 to-blue-600 text-white py-20">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative container mx-auto px-4 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo htmlspecialchars($package['name']); ?></h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">Complete Travel Experience - <?php echo htmlspecialchars($package['duration']); ?></p>
        </div>
    </section>

    <!-- Package Details -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <!-- Image Gallery -->
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="relative">
                        <div id="main-image" class="h-96 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg overflow-hidden cursor-pointer"
                             style="background-image: url('<?php echo htmlspecialchars($package['image'] ?: 'assets/packages.jpg'); ?>'); background-size: cover; background-position: center;"
                             onclick="openImageModal(this.style.backgroundImage)">
                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition duration-300 flex items-center justify-center">
                                <i class="fas fa-expand text-white text-2xl opacity-0 hover:opacity-100 transition duration-300"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery thumbnails -->
                    <div class="grid grid-cols-4 gap-2">
                        <?php
                        // Collect all potential gallery images
                        $gallery_images = [];
                        if (!empty($package['gallery_image1'])) {
                            $gallery_images[] = $package['gallery_image1'];
                        }
                        if (!empty($package['gallery_image2'])) {
                            $gallery_images[] = $package['gallery_image2'];
                        }
                        if (!empty($package['gallery_image3'])) {
                            $gallery_images[] = $package['gallery_image3'];
                        }
                        if (!empty($package['gallery_image4'])) {
                            $gallery_images[] = $package['gallery_image4'];
                        }

                        // Ensure we always have 4 thumbnails, filling with placeholders if actual images are less
                        $placeholder_base = '/placeholder.svg?height=80&width=120&text=View%20';
                        for ($i = count($gallery_images); $i < 4; $i++) {
                            $gallery_images[] = $placeholder_base . ($i + 1);
                        }

                        foreach ($gallery_images as $index => $image):
                            $imageUrl = htmlspecialchars($image); // Use htmlspecialchars for safety
                            // Determine if this is the first thumbnail (for initial active state)
                            $is_first_thumbnail = ($index === 0);
                        ?>
                        <div class="h-20 rounded cursor-pointer border-2
                            <?php echo $is_first_thumbnail ? 'border-blue-500' : 'border-transparent'; // Changed to blue-500 for package page ?>
                            hover:border-blue-500 transition duration-300 overflow-hidden"
                             style="background-image: url('<?php echo $imageUrl; ?>'); background-size: cover; background-position: center;"
                             onclick="changeMainImage('url(\'<?php echo $imageUrl; ?>\')', this)">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-6">
                    <div>
                        <div class="flex items-center space-x-4 mb-4">
                            <h2 class="text-3xl font-bold text-gray-800">Package Overview</h2>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo htmlspecialchars($package['duration']); ?>
                            </span>
                        </div>
                        <p class="text-gray-600 text-lg leading-relaxed"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                    </div>

                    <?php if ($package['destinations']): ?>
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Destinations Included</h3>
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($package['destinations'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($package['inclusions']): ?>
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4">What's Included</h3>
                        <div class="bg-green-50 p-6 rounded-lg">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($package['inclusions'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($package['exclusions']): ?>
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4">What's Not Included</h3>
                        <div class="bg-red-50 p-6 rounded-lg">
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($package['exclusions'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <a href="contact.php" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg text-center font-semibold transition duration-300">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Book This Package
                        </a>
                        <a href="contact.php" class="flex-1 border-2 border-green-600 text-green-600 hover:bg-green-600 hover:text-white py-3 px-6 rounded-lg text-center font-semibold transition duration-300">
                            <i class="fas fa-edit mr-2"></i>
                            Customize Package
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/package-itinerary.php'; ?>

<!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">
                <i class="fas fa-times"></i>
            </button>
            <img id="modalImage" src="/placeholder.svg" alt="Gallery Image" class="max-w-full max-h-full object-contain rounded-lg">
        </div>
    </div>

    <!-- Related Packages -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Other Packages You Might Like</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php
                // Get related packages
                $query = "SELECT * FROM packages WHERE id != ? ORDER BY RAND() LIMIT 3";
                $stmt = $db->prepare($query);
                $stmt->execute([$package_id]);
                $related = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($related as $related_pkg):
                ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                    <div class="h-48 bg-gradient-to-r from-green-400 to-blue-500" style="background-image: url('<?php echo $related_pkg['image'] ?: '/placeholder.svg?height=192&width=288&text=Related%20Package'; ?>'); background-size: cover; background-position: center;"></div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($related_pkg['name']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($related_pkg['description'], 0, 100)) . '...'; ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($related_pkg['duration']); ?></span>
                            <a href="package-detail.php?id=<?php echo $related_pkg['id']; ?>" class="text-green-600 hover:text-green-800 font-semibold">Learn More →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
        // Gallery functionality
        function changeMainImage(backgroundImageUrl, thumbnail) {
            const mainImage = document.getElementById('main-image');
            mainImage.style.backgroundImage = backgroundImageUrl;

            // Remove active class from all thumbnails
            // Ensure we only select thumbnails within this specific gallery
            document.querySelectorAll('.grid.grid-cols-4 div[onclick^="changeMainImage"]').forEach(thumb => {
                // Changed border-blue-500 to match the package page's highlight color
                thumb.classList.remove('border-blue-500');
                thumb.classList.add('border-transparent');
            });

            // Add active class to clicked thumbnail
            // Changed border-blue-500 to match the package page's highlight color
            thumbnail.classList.remove('border-transparent');
            thumbnail.classList.add('border-blue-500');
        }

        // Image Modal functionality
        function openImageModal(backgroundImage) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            // Extract URL from background-image style: removes 'url("' and '")'
            const imageUrl = backgroundImage.slice(5, -2);
            modalImage.src = imageUrl;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent scrolling background
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Close modal on background click
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) { // Only close if the click is directly on the modal backdrop
                closeImageModal();
            }
        });

        // Initialize active thumbnail (optional, but good for UX)
        document.addEventListener('DOMContentLoaded', () => {
            // Get all thumbnail divs
            const thumbnails = document.querySelectorAll('.grid.grid-cols-4 div[onclick^="changeMainImage"]');

            // If there are thumbnails, highlight the first one
            if (thumbnails.length > 0) {
                thumbnails[0].classList.remove('border-transparent');
                thumbnails[0].classList.add('border-blue-500');
                // Also set the main image if it's not already set by the package['image']
                // This might be redundant if package['image'] is always the first one, but good for consistency
                const firstThumbnailBg = thumbnails[0].style.backgroundImage;
                document.getElementById('main-image').style.backgroundImage = firstThumbnailBg;
            }
        });
    </script>

<?php include 'includes/footer.php'; ?>
