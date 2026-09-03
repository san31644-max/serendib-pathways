<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';
$edit_activity = null;

// Fetch activity types for category dropdown
$activityTypesQuery = "SELECT activity_type_id, activity_type_name FROM activity_types ORDER BY activity_type_name ASC";
$activityTypesStmt = $db->prepare($activityTypesQuery);
$activityTypesStmt->execute();
$activityTypes = $activityTypesStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_POST) {
    $action = $_POST['action'];

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    $activity_type_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $difficulty = trim($_POST['difficulty']);
    $inclusions = trim($_POST['inclusions']);

    if ($action === 'add') {
        if ($name && $description && $activity_type_id) {
            $query = "INSERT INTO activities (name, description, image, activity_type_id, difficulty, inclusions) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$name, $description, $image, $activity_type_id, $difficulty, $inclusions])) {
                $message = "Activity added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding activity.";
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }

    if ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);

        if ($name && $description && $activity_type_id && $id) {
            $query = "UPDATE activities SET name = ?, description = ?, image = ?, activity_type_id = ?, difficulty = ?, inclusions = ? WHERE id = ?";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$name, $description, $image, $activity_type_id, $difficulty, $inclusions, $id])) {
                $message = "Activity updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating activity.";
                $message_type = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $message_type = "error";
        }
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "DELETE FROM activities WHERE id = ?";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$id])) {
            $message = "Activity deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting activity.";
            $message_type = "error";
        }
    }
}

// Check if editing
if (isset($_GET['edit']) && $_GET['edit']) {
    $edit_id = intval($_GET['edit']);
    $query = "SELECT a.*, at.activity_type_name FROM activities a LEFT JOIN activity_types at ON a.activity_type_id = at.activity_type_id WHERE a.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$edit_id]);
    $edit_activity = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all activities with activity type names
$query = "SELECT a.*, at.activity_type_name FROM activities a LEFT JOIN activity_types at ON a.activity_type_id = at.activity_type_id ORDER BY a.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Manage Activities</h2>
            <p class="text-sm sm:text-base text-gray-600">Add, edit, or remove adventure activities</p>
        </div>
        <button onclick="showAddActivityForm()" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 sm:px-6 py-2 sm:py-2 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">
            <i class="fas fa-plus mr-2"></i>Add New Activity
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg text-sm sm:text-base <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Activity Form -->
<div id="activity-form" class="<?php echo $edit_activity ? '' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
    <h3 id="form-title" class="text-lg sm:text-xl font-semibold mb-4"><?php echo $edit_activity ? 'Edit Activity' : 'Add New Activity'; ?></h3>
    <form method="POST" action="">
        <input type="hidden" id="form-action" name="action" value="<?php echo $edit_activity ? 'edit' : 'add'; ?>">
        <input type="hidden" id="activity-id" name="id" value="<?php echo $edit_activity['id'] ?? ''; ?>">

        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="activity_name" class="block text-sm font-medium text-gray-700 mb-2">Activity Name *</label>
                    <input type="text" id="activity_name" name="name" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm sm:text-base" value="<?php echo htmlspecialchars($edit_activity['name'] ?? ''); ?>">
                </div>
                <div>
                    <label for="activity_category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                    <select id="activity_category" name="category" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm sm:text-base">
                        <option value="">Select Category</option>
                        <?php foreach ($activityTypes as $type): ?>
                            <option value="<?php echo $type['activity_type_id']; ?>" <?php echo (isset($edit_activity['activity_type_id']) && $edit_activity['activity_type_id'] == $type['activity_type_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['activity_type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="activity_description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea id="activity_description" name="description" rows="3" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm sm:text-base resize-y"><?php echo htmlspecialchars($edit_activity['description'] ?? ''); ?></textarea>
            </div>

            <div>
                <label for="activity_inclusions" class="block text-sm font-medium text-gray-700 mb-2">What's Included (one per line)</label>
                <textarea id="activity_inclusions" name="inclusions" rows="3" placeholder="Professional guide&#10;Safety equipment&#10;Transportation&#10;Refreshments" class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm sm:text-base resize-y"><?php echo htmlspecialchars($edit_activity['inclusions'] ?? ''); ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="activity_difficulty" class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                    <select id="activity_difficulty" name="difficulty" class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm sm:text-base">
                        <option value="">Select Difficulty</option>
                        <option value="Easy" <?php echo (isset($edit_activity['difficulty']) && $edit_activity['difficulty'] == 'Easy') ? 'selected' : ''; ?>>Easy</option>
                        <option value="Moderate" <?php echo (isset($edit_activity['difficulty']) && $edit_activity['difficulty'] == 'Moderate') ? 'selected' : ''; ?>>Moderate</option>
                        <option value="Challenging" <?php echo (isset($edit_activity['difficulty']) && $edit_activity['difficulty'] == 'Challenging') ? 'selected' : ''; ?>>Challenging</option>
                        <option value="Expert" <?php echo (isset($edit_activity['difficulty']) && $edit_activity['difficulty'] == 'Expert') ? 'selected' : ''; ?>>Expert</option>
                    </select>
                </div>
                <div>
                    <label for="activity_image" class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                    <input type="url" id="activity_image" name="image" placeholder="https://example.com/image.jpg" class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm sm:text-base" value="<?php echo htmlspecialchars($edit_activity['image'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-6">
            <button type="submit" id="submit-btn" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 sm:px-6 py-2 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">
                <?php echo $edit_activity ? 'Update Activity' : 'Save Activity'; ?>
            </button>
            <button type="button" onclick="hideActivityForm()" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 rounded-lg font-semibold transition duration-300 text-sm sm:text-base">
                Cancel
            </button>
        </div>
    </form>
</div>

<!-- Activities List -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <?php if (empty($activities)): ?>
        <div class="p-6 sm:p-8 text-center">
            <i class="fas fa-hiking text-3xl sm:text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 text-base sm:text-lg">No activities found. Add your first activity to get started!</p>
        </div>
    <?php else: ?>
        <!-- Mobile Card View -->
        <div class="block sm:hidden divide-y divide-gray-200">
            <?php foreach ($activities as $activity): ?>
            <div class="p-4 hover:bg-gray-50">
                <div class="flex items-start space-x-3">
                    <div class="h-12 w-12 flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-green-400 to-blue-500" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?: ''); ?>'); background-size: cover; background-position: center;"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($activity['name']); ?></div>
                        <div class="text-xs text-gray-500 mb-2"><?php echo htmlspecialchars(substr($activity['description'], 0, 60)) . '...'; ?></div>

                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($activity['activity_type_name']); ?>
                            </span>
                            <?php if ($activity['difficulty']): ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php 
                                    switch($activity['difficulty']) {
                                        case 'Easy': echo 'bg-green-100 text-green-800'; break;
                                        case 'Moderate': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Challenging': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'Expert': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($activity['difficulty']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-400">
                                <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                            </span>
                        </div>

                        <div class="flex space-x-4">
                            <button onclick='editActivity(<?php echo json_encode($activity); ?>)' class="text-blue-600 hover:text-blue-900 transition duration-300 text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $activity['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 transition duration-300 text-sm" onclick="return confirm('Are you sure you want to delete this activity?')">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($activities as $activity): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gradient-to-r from-green-400 to-blue-500" style="background-image: url('<?php echo htmlspecialchars($activity['image'] ?: ''); ?>'); background-size: cover; background-position: center;"></div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900 text-sm lg:text-base"><?php echo htmlspecialchars($activity['name']); ?></div>
                                    <div class="text-xs lg:text-sm text-gray-500"><?php echo htmlspecialchars(substr($activity['description'], 0, 50)) . '...'; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($activity['activity_type_name']); ?>
                            </span>
                        </td>
                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                            <?php if ($activity['difficulty']): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($activity['difficulty']) {
                                        case 'Easy': echo 'bg-green-100 text-green-800'; break;
                                        case 'Moderate': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'Challenging': echo 'bg-orange-100 text-orange-800'; break;
                                        case 'Expert': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($activity['difficulty']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-xs lg:text-sm text-gray-900">
                            <?php echo date('M j, Y', strtotime($activity['created_at'])); ?>
                        </td>
                        <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-xs lg:text-sm font-medium space-x-2">
                            <button onclick='editActivity(<?php echo json_encode($activity); ?>)' class="text-blue-600 hover:text-blue-900 transition duration-300">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $activity['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 transition duration-300" onclick="return confirm('Are you sure you want to delete this activity?')">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function showAddActivityForm() {
    document.getElementById('form-title').textContent = 'Add New Activity';
    document.getElementById('form-action').value = 'add';
    document.getElementById('activity-id').value = '';
    document.getElementById('submit-btn').textContent = 'Save Activity';

    // Clear all form fields
    document.getElementById('activity_name').value = '';
    document.getElementById('activity_description').value = '';
    document.getElementById('activity_image').value = '';
    document.getElementById('activity_category').value = '';
    document.getElementById('activity_difficulty').value = '';
    document.getElementById('activity_inclusions').value = '';

    document.getElementById('activity-form').classList.remove('hidden');
    document.getElementById('activity-form').scrollIntoView({ behavior: 'smooth' });
}

function editActivity(activity) {
    document.getElementById('form-title').textContent = 'Edit Activity';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('activity-id').value = activity.id;
    document.getElementById('submit-btn').textContent = 'Update Activity';

    document.getElementById('activity_name').value = activity.name || '';
    document.getElementById('activity_description').value = activity.description || '';
    document.getElementById('activity_image').value = activity.image || '';
    document.getElementById('activity_category').value = activity.activity_type_id || '';
    document.getElementById('activity_difficulty').value = activity.difficulty || '';
    document.getElementById('activity_inclusions').value = activity.inclusions || '';

    document.getElementById('activity-form').classList.remove('hidden');
    document.getElementById('activity-form').scrollIntoView({ behavior: 'smooth' });
}

function hideActivityForm() {
    document.getElementById('activity-form').classList.add('hidden');
}

// Auto-resize textareas
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>
