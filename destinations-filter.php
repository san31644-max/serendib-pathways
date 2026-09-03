<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$category_id = isset($_POST['category_id']) && is_numeric($_POST['category_id']) ? intval($_POST['category_id']) : null;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// Base query with join to get category name
$sql = "SELECT d.*, c.category_name 
        FROM destinations d 
        LEFT JOIN categories c ON d.category_id = c.category_id 
        WHERE 1=1 ";

$params = [];

// Filter by category if selected
if ($category_id) {
    $sql .= " AND d.category_id = ? ";
    $params[] = $category_id;
}

// Filter by search term if provided
if ($search !== '') {
    $sql .= " AND (d.name LIKE ? OR d.description LIKE ?) ";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY d.name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($destinations)) {
    echo '<p class="text-center text-gray-600 col-span-full">No destinations found.</p>';
    exit;
}

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
?>
