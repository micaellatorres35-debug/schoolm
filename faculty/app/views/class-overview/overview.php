
<div
    class="tab-pane fade show active"
    id="overview"
    role="tabpanel"
>

    <!-- Section Header -->
    <div class="mb-4">

        <h5 class="fw-bold mb-1">
            Class Overview
        </h5>

        <div class="text-muted small">
            Overview of your assigned class
        </div>

    </div>


    <!-- Class Summary -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-4 align-items-center">

                <!-- Subject -->
                <div class="col-md-6">

                    <div class="d-flex align-items-start">

                        <div
                            class="bg-primary-subtle text-primary rounded-3
                                   d-flex align-items-center justify-content-center
                                   me-3"
                            style="width:48px;height:48px;"
                        >
                            <i class="fa-solid fa-book fs-5"></i>
                        </div>

                        <div>

                            <div class="text-muted small">
                                Subject
                            </div>

                            <h5 class="fw-bold mb-1">
                                 <?= $subject['name'] ?? 'Subject Name' ?>
                            </h5>

                            <div class="text-muted small">
                                  <?= $subject['units'] ?? 'Subject Unit' ?> units
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Section -->
                <div class="col-md-3">

                    <div class="text-muted small mb-1">
                        Section
                    </div>

                    <div class="fw-semibold">
                        <?= $section['section_code'] ?? 'section' ?>
                    </div>

                </div>


                <!-- Status -->
                <div class="col-md-3">

                    <div class="text-muted small mb-1">
                        Class Status
                    </div>

                    <?php
                    switch ($schedule['status']) {

                        case 'Scheduled':
                            $badgeClass = 'text-bg-primary';
                            $statusText = 'Scheduled';
                            break;

                        case 'Completed':
                            $badgeClass = 'text-bg-success';
                            $statusText = 'Completed';
                            break;

                        case 'Cancelled':
                            $badgeClass = 'text-bg-danger';
                            $statusText = 'Cancelled';
                            break;

                        default:
                            $badgeClass = 'text-bg-secondary';
                            $statusText = $schedule['status'];
                            break;
                    }
                ?>

                <span class="badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($statusText) ?>
                </span>

                </div>

            </div>

        </div>

    </div>


    <!-- Statistics -->
    <div class="row g-3 mb-4">

        <!-- Students -->
        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="text-muted small">
                                Students
                            </div>

                            <h3 class="fw-bold mb-1">
                                <?= $numberOfStudent['student_count'] ?? 0 ?>
                            </h3>

                            <small class="text-muted">
                                Currently enrolled
                            </small>

                        </div>

                        <div class="text-primary">
                            <i class="fa-solid fa-users fs-4"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Units -->
        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="text-muted small">
                                Subject Units
                            </div>

                            <h3 class="fw-bold mb-1">
                                <?= $subject['units'] ?? 'Subject Units' ?> units
                            </h3>

                            <small class="text-muted">
                                Academic units
                            </small>

                        </div>

                        <div class="text-warning">
                            <i class="fa-solid fa-layer-group fs-4"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-3">

        <!-- Schedule -->
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 pt-3">

                    <h6 class="fw-bold mb-0">
                        <i class="fa-regular fa-calendar me-2"></i>
                        Class Schedule
                    </h6>

                </div>

                <div class="card-body">

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="bg-primary-subtle text-primary rounded-3
                                   d-flex align-items-center justify-content-center
                                   me-3"
                            style="width:48px;height:48px;"
                        >
                            <i class="fa-regular fa-calendar fs-5"></i>
                        </div>

                        <div>

                            <div class="fw-semibold">
                                <?=  $schedule['day_of_week'] ?? 'Day' ?>
                            </div>

                            <div class="text-muted small">
                                <?=  $schedule['start_time'] ?? 'start' ?> -  <?=  $schedule['end_time'] ?? 'end' ?>
                            </div>

                        </div>

                    </div>


                    <div class="row g-3">

                        <div class="col-6">

                            <div class="text-muted small">
                                Room
                            </div>

                            <div class="fw-semibold">
                                <?= $room['room_name'] ?? 'room' ?>
                            </div>

                        </div>


                        <div class="col-6">

                            <div class="text-muted small">
                                Building
                            </div>

                            <div class="fw-semibold">
                               <?= $room['building'] ?? 'building' ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Academic Information -->
        <div class="col-lg-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 pt-3">

                    <h6 class="fw-bold mb-0">
                        <i class="fa-solid fa-graduation-cap me-2"></i>
                        Academic Information
                    </h6>

                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-6">

                            <div class="text-muted small">
                                School Year
                            </div>

                            <div class="fw-semibold">
                                <?= $school_year['name'] ?>
                            </div>

                        </div>


                        <div class="col-6">

                            <div class="text-muted small">
                                Semester
                            </div>

                            <div class="fw-semibold">
                                 <?= $semester['name'] ?>
                            </div>

                        </div>


                        <div class="col-6">

                            <div class="text-muted small">
                                Faculty
                            </div>

                            <div class="fw-semibold">
                               <?= $faculty['first_name'] ?? 'first_name' ?>  <?= $faculty['last_name'] ?? 'last_name' ?>
                            </div>

                        </div>

                         <div class="col-6">

                            <div class="text-muted small">
                                Faculty Code
                            </div>

                            <div class="fw-semibold">
                                <?= $faculty['faculty_code'] ?? 'faculty_name' ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
