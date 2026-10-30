<!-- Events Management Page - Display and manage events -->
<?php
require_once dirname(dirname(dirname(__DIR__))) . '/database/db.php';
require_once dirname(__DIR__) . '/classes/EventManager.php';

// Create database connection and manager
$database = new Database();
$conn = $database->getConnection();
$eventManager = new EventManager($conn);

// Fetch all CC events from database
try {
    $events = $eventManager->getAllEvents();
    $eventTypes = $eventManager->getEventTypeEnums();
    $statuses = $eventManager->getStatusEnums();
} catch (Exception $e) {
    $events = [];
    $eventTypes = ['Academic', 'Meeting','Institutional Event', 'Sports', 'Cultural', 'Social', 'Workshop', 'Seminar'];
  $statuses = ['upcoming', 'ongoing', 'completed', 'cancelled'];
    $error = $e->getMessage();
}
?>

<div class="events-management-wrapper">
    <!-- Page Header -->
    <div class="module-header">
        <div>
            <h1>Events Management</h1>
            <p class="text-muted small">Manage college events, activities, and celebrations</p>
        </div>
        <button class="btn btn-primary" id="addEventBtn" type="button">
            <i class="fas fa-plus me-2"></i> Add Event
        </button>
    </div>

    <div class="module-content">
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-info">
                <h6>Total Events</h6>
                <h3 id="totalEvents">0</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon upcoming">
                <i class="fas fa-arrow-right"></i>
            </div>
            <div class="stat-info">
                <h6>Upcoming</h6>
                <h3 id="upcomingEvents">0</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon ongoing">
                <i class="fas fa-circle"></i>
            </div>
            <div class="stat-info">
                <h6>Ongoing</h6>
                <h3 id="ongoingEvents">0</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h6>Completed</h6>
                <h3 id="completedEvents">0</h3>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="filters">
                <div class="form-group">
                    <label class="form-label">Search by Title</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search events...">
                </div>
                <div class="form-group">
                    <label class="form-label">Event Type</label>
                    <select class="form-select" id="filterType">
                        <option value="">All Types</option>
                        <option value="Academic">Academic</option>
                        <option value="Sports">Sports</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Social">Social</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Seminar">Seminar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                   <select class="form-select" id="filterStatus">
    <option value="">All Status</option>
    <option value="upcoming">Upcoming</option>
    <option value="ongoing">Ongoing</option>
    <option value="completed">Completed</option>
    <option value="cancelled">Cancelled</option>
</select>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Events List</h5>
        </div>
        <div class="table-responsive">
            <table id="eventsTable">
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Type</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Target Audience</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i> Loading events...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <!-- Event Statistics Cards -->
    


<!-- Add/Edit Event Modal -->
<div class="modal" id="addEventModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Add New Event</h5>
                <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="eventForm" data-custom-submit="true">
                <div class="modal-body">
                    <input type="hidden" id="eventId">

                    <div class="form-group">
                        <label class="form-label">Use Template</label>
                        <select class="form-select" id="templateSelect">
                            <option value="">(none) - choose a template</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="eventTitle" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Event Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="eventType" required>
                            <option value="">Select Type</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="eventDate" required>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="startTime" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="endTime" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" placeholder="e.g., Gymnasium, Auditorium" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" placeholder="Event details..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Audience</label>
                        <input type="text" class="form-control" id="targetAudience" placeholder="e.g., Grade 10-12, Faculty, All Students">
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="priority">
                                <option value="">Select Priority</option>
                                <option value="Normal">Normal</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="eventStatus" required>
                                <option value="">Select Status</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
     </div>

<!-- Toast Notification -->
<div id="toastContainer" class="toast-container"></div>

<!-- Hidden JSON data for events-management.js module -->
<script id="preload-eventData" type="application/json">
<?php echo json_encode($events ?? []); ?>
</script>