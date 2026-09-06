<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

require_once 'includes/experience-catalog.php';
$search = is_string($_POST['search'] ?? null) ? trim($_POST['search']) : '';
$activity_type_id = is_string($_POST['activity_type_id'] ?? null) ? $_POST['activity_type_id'] : '';
$activities = array_filter(experience_catalog($db)['activities'], static function ($activity) use ($search, $activity_type_id) {
    return ($activity_type_id === '' || (string) $activity['activity_type_id'] === $activity_type_id)
        && ($search === '' || stripos($activity['name'] . ' ' . $activity['description'], $search) !== false);
});

if (empty($activities)) {
    echo '<p class="text-center text-gray-600 col-span-full">No activities found.</p>';
    exit;
}

foreach ($activities as $activity):
?>
<article class="experience-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
    <div class="experience-card__photo h-64 bg-gradient-to-r from-green-400 to-blue-500 relative">
        <img src="<?php echo htmlspecialchars($activity['image'] ?: 'assets/about-2.jpg', ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($activity['name'], ENT_QUOTES); ?>" loading="lazy" width="960" height="640">
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
        <?php if (empty($activity['slug'])): ?>
        <div class="flex items-center">
            <div class="flex items-center text-yellow-500">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                <span class="ml-2 text-gray-600 text-sm">(4.8)</span>
            </div>
        </div>
        <?php endif; ?>
        <a class="experience-card__link" href="contact.php?experience=<?php echo rawurlencode($activity['name']); ?>">Plan this experience <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</article>
<?php
endforeach;
?>
