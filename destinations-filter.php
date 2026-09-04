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
?>
