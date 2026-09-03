<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

$query = "SELECT COUNT(*) as count FROM destinations";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['destinations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM packages";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['packages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM activities";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['activities'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM contact_messages";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get recent messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities
$query = "SELECT * FROM activities ORDER BY created_at DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

            <div class="mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Dashboard</h2>
                <p class="text-gray-600">Overview of your tourism business</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-6 md:mb-8">
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs md:text-sm">Total Destinations</p>
                            <p class="text-xl md:text-3xl font-bold text-green-600"><?php echo $stats['destinations']; ?></p>
                        </div>
                        <div class="bg-green-100 p-2 md:p-3 rounded-full">
                            <i class="fas fa-map-marker-alt text-green-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs md:text-sm">Active Packages</p>
                            <p class="text-xl md:text-3xl font-bold text-blue-600"><?php echo $stats['packages']; ?></p>
                        </div>
                        <div class="bg-blue-100 p-2 md:p-3 rounded-full">
                            <i class="fas fa-box text-blue-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs md:text-sm">Total Activities</p>
                            <p class="text-xl md:text-3xl font-bold text-purple-600"><?php echo $stats['activities']; ?></p>
                        </div>
                        <div class="bg-purple-100 p-2 md:p-3 rounded-full">
                            <i class="fas fa-hiking text-purple-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-xs md:text-sm">New Messages</p>
                            <p class="text-xl md:text-3xl font-bold text-yellow-600"><?php echo $stats['messages']; ?></p>
                        </div>
                        <div class="bg-yellow-100 p-2 md:p-3 rounded-full">
                            <i class="fas fa-envelope text-yellow-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Grid -->
            <div class="grid gap-6 mb-6">
                <!-- Recent Messages -->
                <div class="bg-white rounded-lg shadow-lg p-4 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg md:text-xl font-semibold">Recent Messages</h3>
                        <a href="messages.php" class="text-green-600 hover:text-green-800 text-sm font-semibold">View All</a>
                    </div>
                    <?php if (empty($recent_messages)): ?>
                        <p class="text-gray-500">No messages yet.</p>
                    <?php else: ?>
                        <div class="space-y-3 md:space-y-4">
                            <?php foreach ($recent_messages as $message): ?>
                            <div class="flex items-start space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-lg">
                                <div class="bg-green-100 p-2 rounded-full hidden md:block">
                                    <i class="fas fa-envelope text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm truncate"><?php echo htmlspecialchars($message['subject']); ?></p>
                                    <p class="text-xs text-gray-500">From: <?php echo htmlspecialchars($message['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow-lg p-4 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg md:text-xl font-semibold">Recent Activities</h3>
                        <a href="activities.php" class="text-green-600 hover:text-green-800 text-sm font-semibold">View All</a>
                    </div>
                    <?php if (empty($recent_activities)): ?>
                        <p class="text-gray-500">No activities yet.</p>
                    <?php else: ?>
                        <div class="space-y-3 md:space-y-4">
                            <?php foreach ($recent_activities as $activity): ?>
                            <div class="flex items-start space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-lg">
                                <div class="h-8 w-8 md:h-10 md:w-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg" style="background-image: url('<?php echo $activity['image'] ?: ''; ?>'); background-size: cover; background-position: center;"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm truncate"><?php echo htmlspecialchars($activity['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($activity['category']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 md:mt-8 bg-white rounded-lg shadow-lg p-4 md:p-6">
                <h3 class="text-lg md:text-xl font-semibold mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <a href="destinations.php" class="flex flex-col md:flex-row items-center md:space-x-3 p-3 md:p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-300">
                        <i class="fas fa-map-marker-alt text-green-600 text-xl mb-2 md:mb-0"></i>
                        <span class="font-medium text-green-800 text-sm md:text-base text-center md:text-left">Add Destination</span>
                    </a>
                    <a href="packages.php" class="flex flex-col md:flex-row items-center md:space-x-3 p-3 md:p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-300">
                        <i class="fas fa-box text-blue-600 text-xl mb-2 md:mb-0"></i>
                        <span class="font-medium text-blue-800 text-sm md:text-base text-center md:text-left">Add Package</span>
                    </a>
                    <a href="activities.php" class="flex flex-col md:flex-row items-center md:space-x-3 p-3 md:p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-300">
                        <i class="fas fa-hiking text-purple-600 text-xl mb-2 md:mb-0"></i>
                        <span class="font-medium text-purple-800 text-sm md:text-base text-center md:text-left">Add Activity</span>
                    </a>
                    <a href="messages.php" class="flex flex-col md:flex-row items-center md:space-x-3 p-3 md:p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-300">
                        <i class="fas fa-envelope text-yellow-600 text-xl mb-2 md:mb-0"></i>
                        <span class="font-medium text-yellow-800 text-sm md:text-base text-center md:text-left">View Messages</span>
                    </a>
                </div>
            </div>

<?php include 'includes/admin_footer.php'; ?>