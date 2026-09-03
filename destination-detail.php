<?php
$page_title = "Destination Details - Serendib Pathways";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$destination_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$destination = null;

if ($destination_id) {
    $query = "SELECT d.*, c.category_name, c.category_id
              FROM destinations d
              LEFT JOIN categories c ON d.category_id = c.category_id
              WHERE d.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$destination_id]);
    $destination = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Redirect if destination not found
if (!$destination) {
    header('Location: destinations.php');
    exit;
}

$page_title = $destination['name'] . ' Travel Guide | Serendib Pathways';
$page_description = mb_substr(trim(strip_tags((string) $destination['description'])), 0, 155);
$page_image = $destination['image'] ?: 'assets/serendib-pathways-horizontal.png';

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-green-600 to-blue-600 text-white py-20">
    <div class="absolute inset-0 bg-black opacity-40"></div>
    <div class="relative container mx-auto px-4 text-center">
        <h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo htmlspecialchars($destination['name']); ?></h1>
        <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
            <?php echo htmlspecialchars($destination['category_name'] ?? 'Uncategorized'); ?>
        </p>
    </div>
</section>

<!-- Destination Details -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-start">
            <!-- Image Gallery -->
            <div class="space-y-4">
                <!-- Main Image -->
                <div class="relative">
                    <div id="main-image" class="h-96 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg overflow-hidden cursor-pointer"
                         style="background-image: url('<?php echo htmlspecialchars($destination['image'] ?: '/placeholder.svg?height=400&width=600&text=No%20Image'); ?>'); background-size: cover; background-position: center;"
                         onclick="openImageModal(this.style.backgroundImage)">
                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-20 transition duration-300 flex items-center justify-center">
                            <i class="fas fa-expand text-white text-2xl opacity-0 hover:opacity-100 transition duration-300"></i>
                        </div>
                    </div>
                </div>

                <!-- Gallery thumbnails -->
                <div class="grid grid-cols-4 gap-2">
                    <?php
                    $gallery_images = [];
                    if (!empty($destination['gallery_image1'])) {
                        $gallery_images[] = $destination['gallery_image1'];
                    }
                    if (!empty($destination['gallery_image2'])) {
                        $gallery_images[] = $destination['gallery_image2'];
                    }
                    if (!empty($destination['gallery_image3'])) {
                        $gallery_images[] = $destination['gallery_image3'];
                    }
                    if (!empty($destination['gallery_image4'])) {
                        $gallery_images[] = $destination['gallery_image4'];
                    }

                    $placeholder_base = '/placeholder.svg?height=80&width=120&text=View%20';
                    for ($i = count($gallery_images); $i < 4; $i++) {
                        $gallery_images[] = $placeholder_base . ($i + 1);
                    }

                    foreach ($gallery_images as $index => $image):
                        $imageUrl = htmlspecialchars($image);
                        $is_first_thumbnail = ($index === 0);
                    ?>
                    <div class="h-20 rounded cursor-pointer border-2
                        <?php echo $is_first_thumbnail ? 'border-green-500' : 'border-transparent'; ?>
                        hover:border-green-500 transition duration-300 overflow-hidden"
                         style="background-image: url('<?php echo $imageUrl; ?>'); background-size: cover; background-position: center;"
                         onclick="changeMainImage('url(\'<?php echo $imageUrl; ?>\')', this)">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Details -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">About This Destination</h2>
                    <p class="text-gray-600 text-lg leading-relaxed"><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
                </div>

                <?php if ($destination['highlights']): ?>
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Highlights</h3>
                    <div class="bg-green-50 p-6 rounded-lg">
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($destination['highlights'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Category Details</h3>
                    <div class="flex items-center space-x-2 text-gray-600">
                        <i class="fas fa-tag text-green-600"></i>
                        <span class="text-lg"><?php echo htmlspecialchars($destination['category_name'] ?? 'Uncategorized'); ?></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <a href="contact.php" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg text-center font-semibold transition duration-300">
                        <i class="fas fa-envelope mr-2"></i>
                        Inquire Now
                    </a>
                    <button onclick="openGeminiChat()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg text-center font-semibold transition duration-300">
                        <i class="fas fa-comments mr-2"></i>
                        Chat with Us
                    </button>
                    <a href="packages.php" class="flex-1 border-2 border-green-600 text-green-600 hover:bg-green-600 hover:text-white py-3 px-6 rounded-lg text-center font-semibold transition duration-300">
                        <i class="fas fa-box mr-2"></i>
                        View Packages
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/destination-hotels.php'; ?>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImage" src="/placeholder.svg" alt="Gallery Image" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<!-- Related Destinations -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Other Destinations You Might Like</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            // Get related destinations excluding the current one
            $query = "SELECT * FROM destinations WHERE id != ? ORDER BY RAND() LIMIT 3";
            $stmt = $db->prepare($query);
            $stmt->execute([$destination_id]);
            $related = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($related as $related_dest):
            ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                <div class="h-48 bg-gradient-to-r from-green-400 to-blue-500" style="background-image: url('<?php echo htmlspecialchars($related_dest['image'] ?: '/placeholder.svg?height=192&width=288&text=Related%20Image'); ?>'); background-size: cover; background-position: center;"></div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($related_dest['name']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($related_dest['description'], 0, 100)) . '...'; ?></p>
                    <a href="destination-detail.php?id=<?php echo $related_dest['id']; ?>" class="text-green-600 hover:text-green-800 font-semibold">Learn More →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    function changeMainImage(backgroundImageUrl, thumbnail) {
        const mainImage = document.getElementById('main-image');
        mainImage.style.backgroundImage = backgroundImageUrl;

        // Remove active border from all thumbnails
        document.querySelectorAll('.grid div[onclick^="changeMainImage"]').forEach(thumb => {
            thumb.classList.remove('border-green-500');
            thumb.classList.add('border-transparent');
        });

        // Add active border to clicked thumbnail
        thumbnail.classList.remove('border-transparent');
        thumbnail.classList.add('border-green-500');
    }

    function openImageModal(backgroundImage) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        // Extract URL from background-image style
        const imageUrl = backgroundImage.slice(5, -2);
        modalImage.src = imageUrl;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Disable background scroll
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // Enable background scroll
    }

    // Handle escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });

</script>

<?php include 'includes/footer.php'; ?>
