<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// Prepare base SQL with LIKE on name, description, destinations
$sql = "SELECT * FROM packages WHERE 1=1 ";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR destinations LIKE ?) ";
    $likeSearch = '%' . $search . '%';
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
}

$sql .= " ORDER BY name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($packages)) {
    echo '<p class="text-center text-gray-600 col-span-full">No packages found.</p>';
    exit;
}

foreach ($packages as $package) {
    ?>
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
    <?php
}
