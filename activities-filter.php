<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$search = $_POST['search'] ?? '';
$activity_type_id = isset($_POST['activity_type_id']) && is_numeric($_POST['activity_type_id']) ? intval($_POST['activity_type_id']) : null;

$sql = "SELECT a.*, at.activity_type_name AS category_name 
        FROM activities a 
        LEFT JOIN activity_types at ON a.activity_type_id = at.activity_type_id 
        WHERE 1=1 ";

$params = [];

if ($activity_type_id) {
    $sql .= " AND a.activity_type_id = ? ";
    $params[] = $activity_type_id;
}

if ($search !== '') {
    $sql .= " AND (a.name LIKE ? OR a.description LIKE ?) ";
    $likeSearch = '%' . $search . '%';
    $params[] = $likeSearch;
    $params[] = $likeSearch;
}

$sql .= " ORDER BY a.name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($activities)) {
    echo '<p class="text-center text-gray-600 col-span-full">No activities found.</p>';
    exit;
}

foreach ($activities as $activity):
?>
<div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
    <div class="h-64 bg-gradient-to-r from-green-400 to-blue-500 relative" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?: '/placeholder.svg?height=256&width=400'); ?>'); background-size: cover; background-position: center;">
        <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-sm font-semibold text-green-600">
            <?php echo htmlspecialchars($activity['category_name'] ?? 'Activity'); ?>
        </div>
        <?php if (!empty($activity['difficulty'])): ?>
        <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
            <?php echo htmlspecialchars($activity['difficulty']); ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="p-6">
        <h3 class="text-xl font-semibold mb-3"><?php echo htmlspecialchars($activity['name']); ?></h3>
        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($activity['description']); ?></p>
        <?php if (!empty($activity['inclusions'])): ?>
        <div class="mb-4">
            <h4 class="font-semibold text-gray-800 mb-2">What's Included:</h4>
            <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($activity['inclusions'])); ?></p>
        </div>
        <?php endif; ?>
        <div class="flex items-center">
            <div class="flex items-center text-yellow-500">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                <span class="ml-2 text-gray-600 text-sm">(4.8)</span>
            </div>
        </div>
    </div>
</div>
<?php
endforeach;
?>
