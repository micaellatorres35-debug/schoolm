<div class="tab-pane fade" id="academic">

    <div class="row g-4 pt-3">

 


        <div class="col-xl-9">

            <!-- Academic Overview -->

            <div class="d-flex justify-content-between
                        align-items-center mb-3">

                <div>

                    <h5 class="fw-bold mb-1">
                        Academic Overview
                    </h5>

                    <p class="text-muted small mb-0">
                        Current academic information and student standing.
                    </p>

                </div>

                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary">

                    <i class="bi bi-three-dots"></i>

                </button>

            </div>


            <!-- ========================================= -->
            <!-- OVERVIEW CARD -->
            <!-- ========================================= -->

            <div class="card border shadow-none mb-4">

                <div class="card-body p-4">

                    <div class="row g-4">

                        <!-- Program -->

                        <div class="col-md-6">

                            <div class="d-flex">

                                <div
                                    class="rounded-circle
                                           bg-primary bg-opacity-10
                                           text-primary
                                           d-flex align-items-center
                                           justify-content-center
                                           me-3"
                                    style="width:40px;height:40px;">

                                    <i class="bi bi-mortarboard"></i>

                                </div>

                                <div>

                                    <div class="text-muted small">
                                        Program
                                    </div>

                                    <div class="fw-semibold">
                                        <?= $applicant_course_name  ?> 
                                    </div>

                                    <small class="text-muted">
                                       <?= $applicant_course_code  ?>
                                    </small>

                                </div>

                            </div>

                        </div>


                        <!-- Curriculum -->

                        <div class="col-md-6">

                            <div class="d-flex">

                                <div
                                    class="rounded-circle
                                           bg-primary bg-opacity-10
                                           text-primary
                                           d-flex align-items-center
                                           justify-content-center
                                           me-3"
                                    style="width:40px;height:40px;">

                                    <i class="bi bi-journal-text"></i>

                                </div>

                                <div>

                                    <div class="text-muted small">
                                        Curriculum
                                    </div>

                                    <div class="fw-semibold">
                                      <?= $curriculum['curriculum_name'] ?>
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Year Level -->

                        <div class="col-md-6">

                            <div class="d-flex">

                                <div
                                    class="rounded-circle
                                           bg-primary bg-opacity-10
                                           text-primary
                                           d-flex align-items-center
                                           justify-content-center
                                           me-3"
                                    style="width:40px;height:40px;">

                                    <i class="bi bi-bar-chart"></i>

                                </div>

                                <div>

                                    <div class="text-muted small">
                                        Year Level
                                    </div>

                                    <div class="fw-semibold">
                                       <?php switch($student_year) {

                                        case '1':
                                             echo '1st Year';
                                             break;
                                            case '2':
                                                echo '2nd Year';
                                                break;
                                                case '3':
                                                    echo '3rd Year';
                                                    break;
                                                    case '4':
                                                        echo '4th Year';
                                                        break;
                                         } 
                                         ?>
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Student Type -->

                        <div class="col-md-6">

                            <div class="d-flex">

                                <div
                                    class="rounded-circle
                                           bg-primary bg-opacity-10
                                           text-primary
                                           d-flex align-items-center
                                           justify-content-center
                                           me-3"
                                    style="width:40px;height:40px;">

                                    <i class="bi bi-person"></i>

                                </div>

                                <div>

                                    <div class="text-muted small">
                                        Student Type
                                    </div>

                                    <div class="fw-semibold">
                                       <?= ucfirst($admission_type)  ?>
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- Academic Standing -->

                        <div class="col-md-6">

                            <div class="d-flex">

                                <div
                                    class="rounded-circle
                                           bg-success bg-opacity-10
                                           text-success
                                           d-flex align-items-center
                                           justify-content-center
                                           me-3"
                                    style="width:40px;height:40px;">

                                    <i class="bi bi-check-circle"></i>

                                </div>

                                <div>

                                    <div class="text-muted small">
                                        Academic Standing
                                    </div>

                                    <span class="badge bg-success-subtle
                                                 text-success mt-1">

                                        Regular

                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- School Year -->

                        <div class="col-md-6">

                            <div class="d-flex">

                                <div
                                    class="rounded-circle
                                           bg-primary bg-opacity-10
                                           text-primary
                                           d-flex align-items-center
                                           justify-content-center
                                           me-3"
                                    style="width:40px;height:40px;">

                                    <i class="bi bi-calendar"></i>

                                </div>

                                <div>

                                    <div class="text-muted small">
                                        School Year
                                    </div>

                                    <div class="fw-semibold">
                                        <?= $schoolYear ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- ACADEMIC BACKGROUND -->
            <!-- ========================================= -->

            <h6 class="fw-bold text-primary mb-3">
                Academic Background
            </h6>


            <div class="card border shadow-none mb-4">

                <div class="card-body p-4">

                    <div class="row g-4">

                        <!-- Course Applied -->

                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Course Applied
                            </div>

                            <div class="fw-semibold">
                                <?= $applicant_course_name  ?>
                            </div>

                            <small class="text-muted">
                                <?= $applicant_course_code  ?>
                            </small>

                        </div>


                        <!-- Last School -->

                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                School Last Attended
                            </div>

                            <div class="fw-semibold">
                                <?= $applicant_last_school ?>
                            </div>

                        </div>


                        <!-- Year Graduated -->

                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Year Graduated
                            </div>

                            <div class="fw-semibold">
                                <?= $applicant_year_graduated ?>
                            </div>

                        </div>


                        <!-- Submitted -->

                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Application Submitted
                            </div>

                            <div class="fw-semibold">
                                <?= $applicant_submission_date  ?>
                            </div>


                        </div>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- CURRICULUM PROGRESS -->
            <!-- ========================================= -->

            <div class="d-flex justify-content-between
                        align-items-center mb-3">

                <h6 class="fw-bold text-primary mb-0">
                    Curriculum Progress
                </h6>

                <span class="small text-muted">
                0 / <?= $totalUnits  ?> units completed
                </span>

            </div>


           <div class="card border shadow-none">

    <div class="card-body p-4">

        <?php
        /*
        |--------------------------------------------------------------------------
        | Calculate curriculum progress
        |--------------------------------------------------------------------------
        */

        $totalCurriculumUnits = 0;
        $completedUnits = 0;

        foreach ($groupedCurriculum as $yearLevel => $semesters) {

            foreach ($semesters as $semesterName => $subjects) {

                foreach ($subjects as $subject) {

                    $totalCurriculumUnits += (float) $subject['subject_units'];

                    /*
                     * Later, when you have grades:
                     *
                     * if ($subject['status'] === 'Completed') {
                     *     $completedUnits += $subject['subject_units'];
                     * }
                     */
                }
            }
        }

        $progress = $totalCurriculumUnits > 0
            ? ($completedUnits / $totalCurriculumUnits) * 100
            : 0;
        ?>


        <!-- ========================================= -->
        <!-- CURRICULUM PROGRESS -->
        <!-- ========================================= -->

        <div class="d-flex justify-content-between align-items-center mb-2">

            <span class="small text-muted">
                Curriculum Progress
            </span>

            <span class="small fw-semibold">
                <?= number_format($progress, 0) ?>%
            </span>

        </div>

        <div class="progress mb-4"
             style="height: 8px;">

            <div
                class="progress-bar"
                style="width: <?= $progress ?>%;">
            </div>

        </div>


        <!-- ========================================= -->
        <!-- CURRICULUM -->
        <!-- ========================================= -->

        <?php if (!empty($groupedCurriculum)): ?>

            <?php foreach ($groupedCurriculum as $yearLevel => $semesters): ?>

                <!-- YEAR -->

                <div class="d-flex align-items-center mb-3 mt-4">

                    <div class="bg-primary rounded-circle
                                d-flex align-items-center
                                justify-content-center me-2"
                         style="width:32px;height:32px;">

                        <span class="text-white small fw-bold">
                            <?= $yearLevel ?>
                        </span>

                    </div>

                    <h6 class="fw-bold mb-0">

                        <?= match ((int) $yearLevel) {
                            1 => '1st Year',
                            2 => '2nd Year',
                            3 => '3rd Year',
                            4 => '4th Year',
                            default => $yearLevel . 'th Year'
                        } ?>

                    </h6>

                </div>


                <?php foreach ($semesters as $semesterName => $subjects): ?>

                    <!-- SEMESTER -->

                    <div class="border rounded mb-4">

                        <div class="bg-light px-3 py-2
                                    border-bottom">

                            <div class="d-flex
                                        justify-content-between
                                        align-items-center">

                                <strong class="small">

                                    <?= htmlspecialchars(
                                        $semesterName
                                    ) ?>

                                </strong>

                                <span class="small text-muted">

                                    <?= count($subjects) ?>
                                    subjects

                                </span>

                            </div>

                        </div>


                        <!-- SUBJECT TABLE -->

                        <div class="table-responsive">

                            <table class="table table-hover
                                          align-middle mb-0">

                                <thead>

                                    <tr>

                                        <th class="px-3">
                                            Code
                                        </th>

                                        <th>
                                            Subject
                                        </th>

                                        <th class="text-center">
                                            Units
                                        </th>

                                        <th class="text-center">
                                            Grade
                                        </th>

                                        <th class="text-center">
                                            Status
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php
                                    $semesterUnits = 0;
                                    ?>

                                    <?php foreach ($subjects as $subject): ?>

                                        <?php
                                        $semesterUnits +=
                                            (float) $subject['subject_units'];

                                        /*
                                         * These don't exist yet
                                         * in your curriculum query,
                                         * so we use defaults.
                                         */
                                        $grade = $subject['grade'] ?? null;
                                        $status = $subject['status'] ?? 'Not Taken';
                                        ?>


                                        <tr>

                                            <!-- CODE -->

                                            <td class="px-3 fw-semibold">

                                                <?= htmlspecialchars(
                                                    $subject['subject_code']
                                                ) ?>

                                            </td>


                                            <!-- SUBJECT -->

                                            <td>

                                                <div class="fw-medium">

                                                    <?= htmlspecialchars(
                                                        $subject['subject_name']
                                                    ) ?>

                                                </div>

                                            </td>


                                            <!-- UNITS -->

                                            <td class="text-center">

                                                <?= htmlspecialchars(
                                                    $subject['subject_units']
                                                ) ?>

                                            </td>


                                            <!-- GRADE -->

                                            <td class="text-center">

                                                <?php if ($grade !== null): ?>

                                                    <strong>
                                                        <?= htmlspecialchars(
                                                            $grade
                                                        ) ?>
                                                    </strong>

                                                <?php else: ?>

                                                    <span class="text-muted">
                                                        —
                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- STATUS -->

                                            <td class="text-center">

                                                <?php if ($status === 'Scheduled'): ?>

                                                    <span class="
                                                        badge
                                                        bg-success-subtle
                                                        text-success
                                                    ">
                                                        Enrolled
                                                    </span>

                                                <?php elseif ($status === 'In Progress'): ?>

                                                    <span class="
                                                        badge
                                                        bg-warning-subtle
                                                        text-warning
                                                    ">
                                                        In Progress
                                                    </span>

                                                <?php elseif ($status === 'Failed'): ?>

                                                    <span class="
                                                        badge
                                                        bg-danger-subtle
                                                        text-danger
                                                    ">
                                                        Failed
                                                    </span>

                                                <?php else: ?>

                                                    <span class="
                                                        badge
                                                        bg-secondary-subtle
                                                        text-secondary
                                                    ">
                                                        Not Taken
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>


                                <!-- SEMESTER TOTAL -->

                                <tfoot>

                                    <tr class="table-light">

                                        <th colspan="2"
                                            class="text-end">

                                            Semester Total

                                        </th>

                                        <th class="text-center">

                                            <?= $semesterUnits ?>

                                        </th>

                                        <th colspan="2"></th>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endforeach; ?>


        <?php else: ?>

            <!-- NO CURRICULUM -->

            <div class="text-center py-5">

                <i class="bi bi-journal-x
                          fs-1 text-muted">
                </i>

                <h6 class="mt-3">
                    No Curriculum Found
                </h6>

                <p class="text-muted small mb-0">
                    No curriculum subjects are currently
                    assigned to this student's course.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

        </div>


        <!-- ========================================= -->
        <!-- RIGHT : QUICK SUMMARY -->
        <!-- ========================================= -->

        <div class="col-xl-3">

            <div class="card border shadow-none mb-4">

                <div class="card-body p-3">

                    <h6 class="fw-bold mb-4">
                        Quick Summary
                    </h6>


                    <!-- Total Units Earned -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-book"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Total Units Earned
                            </div>

                        </div>

                        <strong>
                            24
                        </strong>

                    </div>


                    <!-- Total Units Enrolled -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-journal"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Total Units Enrolled
                            </div>

                        </div>

                        <strong>
                            9
                        </strong>

                    </div>


                    <!-- Completed -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-success bg-opacity-10
                                   text-success
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-check2-circle"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Subjects Completed
                            </div>

                        </div>

                        <strong>
                            8
                        </strong>

                    </div>


                    <!-- CGWA -->

                    <div class="d-flex align-items-center mb-4">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-bar-chart"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                CGWA
                            </div>

                        </div>

                        <strong>
                            1.75
                        </strong>

                    </div>


                    <!-- Last Academic Year -->

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle
                                   bg-primary bg-opacity-10
                                   text-primary
                                   d-flex align-items-center
                                   justify-content-center me-3"
                            style="width:38px;height:38px;">

                            <i class="bi bi-calendar3"></i>

                        </div>

                        <div class="flex-grow-1">

                            <div class="small text-muted">
                                Last Academic Year
                            </div>

                        </div>

                        <strong class="small">
                            2025-2026
                        </strong>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- ACADEMIC ACTIONS -->
            <!-- ========================================= -->

            <div class="card border shadow-none">

                <div class="card-body p-3">

                    <h6 class="fw-bold mb-3">
                        Actions
                    </h6>


                    <button
                        type="button"
                        class="btn btn-outline-primary
                               w-100 text-start mb-2">

                        <i class="bi bi-file-earmark-text me-2"></i>

                        Generate TOR

                    </button>


                    <button
                        type="button"
                        class="btn btn-outline-primary
                               w-100 text-start">

                        <i class="bi bi-clipboard-check me-2"></i>

                        Academic Evaluation

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>