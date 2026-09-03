<?php
$page_title = "Activities - Serendib Pathways";
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch activity types for filter dropdown
$activityTypesQuery = "SELECT activity_type_id, activity_type_name FROM activity_types ORDER BY activity_type_name ASC";
$activityTypesStmt = $db->prepare($activityTypesQuery);
$activityTypesStmt->execute();
$activityTypes = $activityTypesStmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-cover bg-center bg-no-repeat text-white py-20" style="background-image: url('assets/about-2.jpg');">
    <div class="absolute inset-0 bg-gradient-to-r from-green-300 via-green-300 to-blue-600 opacity-30"></div>
    <div class="relative container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Adventure Activities</h1>
        <p class="text-xl max-w-3xl mx-auto">Discover exciting eco-friendly activities and adventures across Sri Lanka</p>
    </div>
</section>

<!-- Search and Activity Type Filter -->
<section class="py-6 bg-white">
  <div class="container mx-auto px-4">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 justify-between">
      
      <!-- Search Input - Reduced Size -->
      <div class="flex-1 sm:flex-initial sm:w-2/5 lg:w-2/5 relative">
        <input 
          type="search" 
          id="searchInput"
          placeholder="Search activities..."
          class="w-full rounded-md border border-gray-300 bg-white py-3 pl-11 pr-4 text-gray-900 placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-400 focus:outline-none transition text-sm sm:text-base"
          autocomplete="off"
          aria-label="Search activities"
        >
        <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
        </svg>
      </div>
      
      <!-- Activity Type Filter Dropdown - Reduced Size -->
      <div class="flex-1 sm:flex-initial sm:w-1/4 lg:w-1/4 sm:max-w-48 sm:ml-4">
        <select 
          id="activityTypeFilter"
          class="w-full rounded-md border border-gray-300 bg-white py-3 px-4 text-gray-900 focus:border-green-500 focus:ring-2 focus:ring-green-400 focus:outline-none transition text-sm sm:text-base appearance-none bg-no-repeat bg-right pr-10"
          style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 20 20&quot; fill=&quot;%236B7280&quot;><path fill-rule=&quot;evenodd&quot; d=&quot;M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z&quot; clip-rule=&quot;evenodd&quot;/></svg>'); background-position: right 0.75rem center; background-size: 1.25rem;"
          aria-label="Filter activities by type"
        >
          <option value="">All Types</option>
          <?php foreach ($activityTypes as $type): ?>
            <option value="<?php echo htmlspecialchars($type['activity_type_id']); ?>">
              <?php echo htmlspecialchars($type['activity_type_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
     
    </div>
    
    <!-- Mobile-specific improvements -->
    <div class="mt-4 sm:hidden">
      <p class="text-xs text-gray-500 text-center">Use search or filter to find specific activities</p>
    </div>
  </div>
</section>

<!-- Activities Grid -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div id="activitiesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Initially loading -->
            <div class="col-span-full flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading activities...</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- jQuery CDN for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let ajaxRequest;
    let debounceTimer;

    function fetchActivities() {
        const searchTerm = $('#searchInput').val();
        const activityTypeId = $('#activityTypeFilter').val();

        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        // Show loading state
        $('#activitiesGrid').html(`
            <div class="col-span-full flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading activities...</p>
                </div>
            </div>
        `);

        ajaxRequest = $.ajax({
            url: 'activities-filter.php',
            type: 'POST',
            data: {
                search: searchTerm,
                activity_type_id: activityTypeId
            },
            success: function(response) {
                $('#activitiesGrid').html(response);
            },
            error: function(xhr, status, error) {
                if (status !== 'abort') {
                    $('#activitiesGrid').html(`
                        <div class="col-span-full text-center py-12">
                            <div class="text-red-600 mb-4">
                                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <p class="text-red-600 font-semibold mb-2">Failed to load activities</p>
                            <p class="text-gray-600 text-sm">Please check your connection and try again</p>
                            <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                Retry
                            </button>
                        </div>
                    `);
                }
            }
        });
    }

    // Debounced search for better performance on mobile
    function debouncedFetchActivities() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchActivities, 300); // 300ms delay
    }

    // Initial load: fetch all activities
    fetchActivities();

    // Trigger fetch on input with debounce for search, immediate for dropdown
    $('#searchInput').on('input', debouncedFetchActivities);
    $('#activityTypeFilter').on('change', fetchActivities);

    // Touch-friendly improvements for mobile
    if ('ontouchstart' in window) {
        $('#searchInput, #activityTypeFilter').on('focus', function() {
            $(this).addClass('ring-2 ring-green-400');
        }).on('blur', function() {
            $(this).removeClass('ring-2 ring-green-400');
        });
    }

    // Clear search functionality
    let clearButton = $('<button type="button" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition" aria-label="Clear search"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>');

    $('#searchInput').parent().append(clearButton);
    clearButton.hide();

    $('#searchInput').on('input', function() {
        if ($(this).val().length > 0) {
            clearButton.show();
        } else {
            clearButton.hide();
        }
    });

    clearButton.on('click', function() {
        $('#searchInput').val('').trigger('input');
        clearButton.hide();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
