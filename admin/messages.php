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

// Handle message actions
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "DELETE FROM contact_messages WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id])) {
            $message = "Message deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting message.";
            $message_type = "error";
        }
    }
    
    if ($action == 'mark_read' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "UPDATE contact_messages SET is_read = 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id])) {
            $message = "Message marked as read!";
            $message_type = "success";
        } else {
            $message = "Error updating message.";
            $message_type = "error";
        }
    }

    if ($action == 'mark_unread' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = "UPDATE contact_messages SET is_read = 0 WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id])) {
            $message = "Message marked as unread!";
            $message_type = "success";
        } else {
            $message = "Error updating message.";
            $message_type = "error";
        }
    }
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query based on filters
$where_conditions = [];
$params = [];

if ($filter == 'unread') {
    $where_conditions[] = "is_read = 0";
} elseif ($filter == 'read') {
    $where_conditions[] = "is_read = 1";
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get messages with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM contact_messages $where_clause";
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_messages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_messages / $per_page);

$query = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get message counts for filters
$unread_query = "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0";
$stmt = $db->prepare($unread_query);
$stmt->execute();
$unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$read_query = "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 1";
$stmt = $db->prepare($read_query);
$stmt->execute();
$read_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

include 'includes/admin_header.php';
?>

            <!-- Mobile-optimized header -->
            <div class="mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-1">Contact Messages</h2>
                        <p class="text-sm sm:text-base text-gray-600">Manage customer inquiries</p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold text-center">
                            <?php echo $total_messages; ?> Total
                        </span>
                        <?php if ($unread_count > 0): ?>
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold text-center">
                            <?php echo $unread_count; ?> Unread
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-lg text-sm sm:text-base <?php echo $message_type == 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Mobile-optimized filters and search -->
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
                <!-- Filter Tabs - Stack on mobile -->
                <div class="mb-4">
                    <div class="grid grid-cols-3 gap-1 bg-gray-100 rounded-lg p-1">
                        <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-2 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium transition duration-300 text-center <?php echo $filter == 'all' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'; ?>">
                            All<br><span class="text-xs">(<?php echo $unread_count + $read_count; ?>)</span>
                        </a>
                        <a href="?filter=unread<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-2 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium transition duration-300 text-center <?php echo $filter == 'unread' ? 'bg-white text-red-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'; ?>">
                            Unread<br><span class="text-xs">(<?php echo $unread_count; ?>)</span>
                        </a>
                        <a href="?filter=read<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="px-2 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium transition duration-300 text-center <?php echo $filter == 'read' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'; ?>">
                            Read<br><span class="text-xs">(<?php echo $read_count; ?>)</span>
                        </a>
                    </div>
                </div>

                <!-- Search - Full width on mobile -->
                <form method="GET" class="space-y-3 sm:space-y-0 sm:flex sm:items-center sm:space-x-2">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search messages..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-300 text-sm">
                            Search
                        </button>
                        <?php if ($search): ?>
                        <a href="?filter=<?php echo htmlspecialchars($filter); ?>" class="flex items-center justify-center px-3 py-2 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg">
                            <i class="fas fa-times text-sm"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Messages List - Mobile optimized -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php if (empty($messages)): ?>
                    <div class="p-6 sm:p-8 text-center">
                        <i class="fas fa-envelope text-3xl sm:text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500 text-base sm:text-lg">
                            <?php if ($search): ?>
                                No messages found matching your search.
                            <?php elseif ($filter == 'unread'): ?>
                                No unread messages.
                            <?php elseif ($filter == 'read'): ?>
                                No read messages.
                            <?php else: ?>
                                No messages found.
                            <?php endif; ?>
                        </p>
                        <p class="text-gray-400 text-xs sm:text-sm mt-2">Customer messages will appear here when they contact you.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-gray-200">
                        <?php foreach ($messages as $msg): ?>
                        <div class="p-4 sm:p-6 hover:bg-gray-50 transition duration-300 <?php echo !$msg['is_read'] ? 'bg-blue-50 border-l-4 border-blue-500' : ''; ?>">
                            <!-- Mobile-first layout -->
                            <div class="space-y-3">
                                <!-- Header with name and status -->
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-2 flex-1 min-w-0">
                                        <div class="bg-green-100 p-1.5 sm:p-2 rounded-full flex-shrink-0">
                                            <i class="fas fa-user text-green-600 text-xs sm:text-sm"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center space-x-2">
                                                <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                                    <?php echo htmlspecialchars($msg['name']); ?>
                                                </h4>
                                                <?php if (!$msg['is_read']): ?>
                                                    <span class="bg-red-500 w-2 h-2 rounded-full flex-shrink-0"></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs sm:text-sm text-gray-500 truncate"><?php echo htmlspecialchars($msg['email']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Action buttons - Mobile dropdown -->
                                    <div class="flex-shrink-0">
                                        <button onclick="toggleActions(<?php echo $msg['id']; ?>)" class="text-gray-400 hover:text-gray-600 p-1">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Date and status badges -->
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full font-medium">
                                        <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                                    </span>
                                    <?php if (!$msg['is_read']): ?>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">
                                            New
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Subject and message -->
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-2 text-sm sm:text-base">
                                        Subject: <?php echo htmlspecialchars($msg['subject']); ?>
                                    </h5>
                                    <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                        <p class="text-gray-700 text-sm sm:text-base whitespace-pre-wrap"><?php echo htmlspecialchars($msg['message']); ?></p>
                                    </div>
                                </div>
                                
                                <!-- Action buttons - Hidden by default on mobile -->
                                <div id="actions-<?php echo $msg['id']; ?>" class="hidden border-t pt-3 space-y-2">
                                    <button onclick="replyToMessage('<?php echo htmlspecialchars($msg['email']); ?>', '<?php echo htmlspecialchars($msg['subject']); ?>')" 
                                            class="w-full sm:w-auto bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition duration-300">
                                        <i class="fas fa-reply mr-2"></i>Reply
                                    </button>
                                    
                                    <div class="flex space-x-2">
                                        <?php if (!$msg['is_read']): ?>
                                            <form method="POST" action="" class="flex-1">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                <button type="submit" class="w-full bg-green-50 text-green-600 hover:bg-green-100 px-4 py-2 rounded-lg text-sm font-medium transition duration-300">
                                                    <i class="fas fa-check mr-2"></i>Mark Read
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="" class="flex-1">
                                                <input type="hidden" name="action" value="mark_unread">
                                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                <button type="submit" class="w-full bg-yellow-50 text-yellow-600 hover:bg-yellow-100 px-4 py-2 rounded-lg text-sm font-medium transition duration-300">
                                                    <i class="fas fa-undo mr-2"></i>Mark Unread
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="" class="flex-1">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                            <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded-lg text-sm font-medium transition duration-300" onclick="return confirm('Are you sure you want to delete this message?')">
                                                <i class="fas fa-trash mr-2"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mobile-optimized pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="bg-gray-50 px-4 sm:px-6 py-3 border-t border-gray-200">
                        <!-- Mobile pagination - simplified -->
                        <div class="flex items-center justify-between sm:hidden">
                            <div class="text-xs text-gray-600">
                                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                            </div>
                            <div class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium">‹</a>
                                <?php endif; ?>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                       class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium">›</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Desktop pagination -->
                        <div class="hidden sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium"><?php echo $offset + 1; ?></span> to <span class="font-medium"><?php echo min($offset + $per_page, $total_messages); ?></span> of <span class="font-medium"><?php echo $total_messages; ?></span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                           class="<?php echo $i == $page ? 'bg-green-50 border-green-500 text-green-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <script>
                function replyToMessage(email, subject) {
                    const mailtoLink = `mailto:${email}?subject=Re: ${encodeURIComponent(subject)}`;
                    window.location.href = mailtoLink;
                }

                function toggleActions(messageId) {
                    const actionsDiv = document.getElementById(`actions-${messageId}`);
                    if (actionsDiv.classList.contains('hidden')) {
                        // Hide all other action divs first
                        document.querySelectorAll('[id^="actions-"]').forEach(div => {
                            div.classList.add('hidden');
                        });
                        // Show current actions
                        actionsDiv.classList.remove('hidden');
                    } else {
                        actionsDiv.classList.add('hidden');
                    }
                }

                // Close actions when clicking outside
                document.addEventListener('click', function(event) {
                    if (!event.target.closest('[onclick^="toggleActions"]') && !event.target.closest('[id^="actions-"]')) {
                        document.querySelectorAll('[id^="actions-"]').forEach(div => {
                            div.classList.add('hidden');
                        });
                    }
                });

                // Auto-refresh unread count every 30 seconds
                setInterval(function() {
                    fetch('messages.php?ajax=unread_count')
                        .then(response => response.json())
                        .then(data => {
                            if (data.unread_count !== undefined) {
                                const unreadBadge = document.querySelector('.bg-red-100');
                                if (unreadBadge && data.unread_count > 0) {
                                    unreadBadge.textContent = data.unread_count + ' Unread';
                                } else if (unreadBadge && data.unread_count === 0) {
                                    unreadBadge.style.display = 'none';
                                }
                            }
                        })
                        .catch(error => console.log('Error fetching unread count:', error));
                }, 30000);
            </script>

<?php include 'includes/admin_footer.php'; ?>