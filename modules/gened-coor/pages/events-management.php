<?php
require_once dirname(__DIR__) . '/classes/EventManager.php';

$genedEventSuggestions = [
    ['title' => 'Buwan ng Wika', 'event_type' => 'Cultural Event', 'description' => 'Debate, balagtasan, sabayang pagbigkas, tula, poster-making, o slogan-making para sa Filipino at GE.'],
    ['title' => 'Rizal Day / Rizal Month', 'event_type' => 'Academic', 'description' => 'Lecture, essay-writing, drama, o quiz tungkol kay Dr. Jose Rizal.'],
    ['title' => 'Nutrition Month / Science and Technology Week', 'event_type' => 'Academic', 'description' => 'Exhibit o forum na may kaugnayan sa Science, Technology and Society (STS).'],
    ['title' => 'Art and Culture Week', 'event_type' => 'Cultural Event', 'description' => 'Art Appreciation exhibit, film showing, o gallery walk.'],
    ['title' => 'Ethics / Values Forum', 'event_type' => 'Seminar', 'description' => 'Talk tungkol sa academic integrity, ethics, values, at citizenship.'],
    ['title' => 'Math / Science Fair', 'event_type' => 'Academic', 'description' => 'Math o science fair na may partisipasyon ng General Education.'],
    ['title' => 'Literary / Writing Contest', 'event_type' => 'Cultural Event', 'description' => 'Essay, poetry, o short-story writing contest.'],
    ['title' => 'PE Event', 'event_type' => 'Sports Event', 'description' => 'Intramurals, sportsfest, o dance competition kung saklaw ng GenEd ang PE.'],
    ['title' => 'NSTP Community Activity', 'event_type' => 'Institutional Event', 'description' => 'Community service, clean-up drive, tree planting, o outreach kung kasama sa GenEd ang NSTP.'],
    ['title' => 'Graduation / Enrollment GE Support', 'event_type' => 'Academic', 'description' => 'Pagsasaayos at paggabay sa mga estudyante tungkol sa GE subjects para sa graduation o enrollment.'],
];
$genedEventTypes = ['Academic', 'Meeting', 'Seminar', 'Institutional Event', 'Cultural Event', 'Sports Event', 'Orientation', 'Other'];
?>

<div class="events-management-wrapper ge-dashboard ge-events-overview" id="genedEventsDashboard">
    <div class="module-header">
        <div>
            <h1>GenEd Events</h1>
            <p class="text-muted small">Plan and track GenEd events for each semester.</p>
        </div>
        <button class="btn btn-primary" id="addEventBtn" type="button" hidden>
            <i class="fas fa-plus me-2"></i> Add Event
        </button>
    </div>

    <div class="module-content">
        <section class="ge-event-summary" aria-label="Event summary">
            <article><p>Total Events</p><strong id="eventTotal">--</strong><small>All events this semester</small></article>
            <article><p>Pending Approval</p><strong id="eventPending">--</strong><small>Waiting for admin / college coordinator</small></article>
            <article><p>Upcoming</p><strong id="eventUpcoming">--</strong><small>Approved events with future dates</small></article>
            <article><p>Completed</p><strong id="eventCompleted">--</strong><small>Events marked complete</small></article>
        </section>

        <section class="ge-event-period" aria-label="Active academic period">
            <label>School Year<input type="text" id="eventSchoolYear" value="Loading..." readonly></label>
            <label>Semester<input type="text" id="eventSemester" value="Loading..." readonly></label>
        </section>

        <nav class="ge-event-categories" aria-label="Filter events by category">
            <button type="button" class="active" data-event-category="">All Events</button>
            <button type="button" data-event-category="Language & Literature">Language &amp; Literature</button>
            <button type="button" data-event-category="History & Culture">History &amp; Culture</button>
            <button type="button" data-event-category="Science & Math">Science &amp; Math</button>
            <button type="button" data-event-category="Values & Ethics">Values &amp; Ethics</button>
            <button type="button" data-event-category="Sports">Sports</button>
            <button type="button" data-event-category="Community">Community</button>
            <button type="button" data-event-category="Academic Support">Academic Support</button>
        </nav>

        <section class="ge-event-table-card" aria-label="Events list">
            <div class="table-responsive">
                <table id="genedEventsTable">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Category</th>
                            <th>Date &amp; Venue</th>
                            <th>Submitted By</th>
                            <th>Status</th>
                            <th>Approval Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="genedEventsTableBody">
                        <tr><td colspan="7" class="ge-event-empty">Loading events...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <details class="ge-event-ideas">
            <summary>GenEd event ideas</summary>
            <p class="text-muted small">Choose an idea to prefill the event form. Events are submitted for approval.</p>
            <div class="gened-event-guide-grid">
                <?php foreach ($genedEventSuggestions as $suggestion): ?>
                    <article class="gened-event-suggestion">
                        <h3><?php echo htmlspecialchars($suggestion['title']); ?></h3>
                        <p><?php echo htmlspecialchars($suggestion['description']); ?></p>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary use-gened-event-suggestion"
                            data-title="<?php echo htmlspecialchars($suggestion['title'], ENT_QUOTES); ?>"
                            data-event-type="<?php echo htmlspecialchars($suggestion['event_type'], ENT_QUOTES); ?>"
                            data-description="<?php echo htmlspecialchars($suggestion['description'], ENT_QUOTES); ?>">
                            Use this idea
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>
        </details>
    </div>

    <div class="ge-event-modal" id="eventDetailsModal" hidden>
        <div class="ge-event-modal-backdrop" data-close-event-details></div>
        <section class="ge-event-modal-content" role="dialog" aria-modal="true" aria-labelledby="eventDetailsTitle">
            <div class="ge-event-modal-header">
                <h2 id="eventDetailsTitle">Event details</h2>
                <button type="button" class="ge-modal-close" aria-label="Close" data-close-event-details>&times;</button>
            </div>
            <div class="ge-event-details-grid">
                <p><strong>Type</strong><span id="eventDetailsType"></span></p>
                <p><strong>Date and time</strong><span id="eventDetailsDate"></span></p>
                <p><strong>Venue</strong><span id="eventDetailsVenue"></span></p>
                <p><strong>Scope</strong><span id="eventDetailsScope"></span></p>
                <p><strong>Status</strong><span id="eventDetailsStatus"></span></p>
                <p><strong>In charge</strong><span id="eventDetailsInCharge"></span></p>
                <p class="ge-event-details-full"><strong>Target audience</strong><span id="eventDetailsAudience"></span></p>
                <p class="ge-event-details-full"><strong>Description</strong><span id="eventDetailsDescription"></span></p>
            </div>
        </section>
    </div>

    <div class="ge-event-modal" id="addEventModal" hidden>
        <div class="ge-event-modal-backdrop" data-close-event-modal></div>
        <section class="ge-event-modal-content" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle">
            <div class="ge-event-modal-header">
                <h2 id="eventModalTitle">Submit GenEd Event</h2>
                <button type="button" class="ge-modal-close" aria-label="Close" data-close-event-modal>&times;</button>
            </div>
            <form id="eventForm" data-custom-submit="true">
                <div class="ge-event-form-grid">
                    <label class="ge-form-full">Event Title *
                        <input type="text" id="eventTitle" maxlength="255" required>
                    </label>
                    <label>Event Type *
                        <select id="eventType" required>
                            <option value="">Select type</option>
                            <?php foreach ($genedEventTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Category *
                        <select id="eventCategory" required>
                            <option value="">Select category</option>
                            <?php foreach (['Language & Literature', 'History & Culture', 'Science & Math', 'Values & Ethics', 'Sports', 'Community', 'Academic Support'] as $category): ?>
                                <option value="<?php echo htmlspecialchars($category, ENT_QUOTES); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Event Scope *
                        <select id="eventScope" required>
                            <option value="">Select scope</option>
                            <option value="School-wide">School-wide</option>
                            <option value="Department-level">Department-level</option>
                        </select>
                    </label>
                    <label>Date *
                        <input type="date" id="eventDate" required>
                    </label>
                    <label>Target Audience
                        <input type="text" id="targetAudience" maxlength="100" placeholder="Students, faculty, or all">
                    </label>
                    <label>Start Time *
                        <input type="time" id="startTime" required>
                    </label>
                    <label>End Time *
                        <input type="time" id="endTime" required>
                    </label>
                    <label class="ge-form-full">Location *
                        <input type="text" id="location" maxlength="255" required>
                    </label>
                    <label class="ge-form-full">Description
                        <textarea id="description" rows="3"></textarea>
                    </label>
                </div>
                <p class="ge-form-note">New events will appear as Pending until an administrator or college coordinator reviews them.</p>
                <div class="ge-event-modal-actions">
                    <button type="button" class="btn btn-secondary" data-close-event-modal>Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </section>
    </div>
    <div class="toast-container" id="toastContainer" aria-live="polite"></div>
</div>
