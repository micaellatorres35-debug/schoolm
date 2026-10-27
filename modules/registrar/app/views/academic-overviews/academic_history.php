
<div class="tab-pane fade" id="history">

    <!-- Section Header -->
    <div class="pt-3 mb-4">

        <h5 class="fw-bold mb-1">
            Academic History
        </h5>

        <p class="text-muted small mb-0">
            Current academic history of the student.
        </p>

    </div>



    <!-- Academic Summary -->
    <div class="card border shadow-none mb-4">

        <div class="card-header bg-white border-bottom py-3 px-4">

            <h6 class="fw-semibold mb-0">
                Academic Summary
            </h6>

        </div>

        <div class="card-body p-4">

            <div class="row g-3">

                <!-- Subjects -->
                <div class="col-md-4">

                    <div class="border rounded-3 p-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 bg-primary-subtle
                                        text-primary d-flex
                                        align-items-center
                                        justify-content-center"
                                 style="width: 44px; height: 44px;">

                                <i class="bi bi-journal-bookmark"></i>

                            </div>

                            <div>

                                <div class="text-muted small">
                                    Subjects Enrolled
                                </div>

                                <div class="fs-5 fw-bold">
                                    <?= count($enrollments) ?>
                                </div>


                              
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Units -->
                <div class="col-md-4">

                    <div class="border rounded-3 p-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 bg-info-subtle
                                        text-info-emphasis d-flex
                                        align-items-center
                                        justify-content-center"
                                 style="width: 44px; height: 44px;">

                                <i class="bi bi-calculator"></i>

                            </div>

                            <div>

                                <div class="text-muted small">
                                    Units Enrolled
                                </div>

                                <div class="fs-5 fw-bold">
                                    <?= array_sum((array_column($enrollments,'units')))  ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Year -->
                <div class="col-md-4">

                    <div class="border rounded-3 p-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 bg-success-subtle
                                        text-success d-flex
                                        align-items-center
                                        justify-content-center"
                                 style="width: 44px; height: 44px;">

                                <i class="bi bi-mortarboard"></i>

                            </div>

                            <div>

                                <div class="text-muted small">
                                    Current Level
                                </div>

                                <div class="fs-5 fw-bold">
                            
                                    <?php 
                                      
                                      switch($student_year)
                                      {
                                        case '1':
                                            echo "1st Year";
                                            break;
                                            case '2':
                                                 echo "2nd Year";
                                                break;
                                                case '3':
                                                     echo "3rd Year";
                                                    break;
                                                    case '4':
                                                         echo "4th Year";
                                                        break;
                                      }

                                     ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

        <!-- Current Semester -->
    <div class="card border shadow-none">

        <div class="card-header bg-white border-bottom py-3 px-4 mb-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6 class="fw-semibold mb-1">
                        Current Semester
                    </h6>

                    <p class="text-muted small mb-0">
                        Subjects currently enrolled by the student.
                    </p>

                  

                </div>

                <span class="badge bg-light text-dark border">
                    <?= $semester['name'] ?>
                </span>


            </div>

        </div>




   <div class="accordion" id="academicHistory">

    <?php $yearIndex = 0; ?>

    <?php foreach ($groupedCurriculum as $yearLevel => $semesters): ?>

        <div class="mb-4">

            <h5 class="fw-bold p-3 mb-3 text-primary">               
                <?php 
                 
                 switch($yearLevel){
 
                    case '1':
                        echo "1st Year";
                        break;
                        case '2':
                            echo "2nd Year";
                            break;
                            case '3':
                                echo "3rd Year";
                                break;
                                case '4':
                                    echo "4th Year";
                                    break;
                 }
 

                ?>

            </h5>

            <?php $semesterIndex = 0; ?>

            <?php foreach ($semesters as $semesterName => $subjects): ?>

                <?php
                    $collapseId = "semester_{$yearIndex}_{$semesterIndex}";

                    $subjectCount = count($subjects);

                    $totalUnits = array_sum(
                        array_column($subjects, 'subject_units')
                    );
                ?>

                <div class="accordion-item border rounded-3 mb-3">

                    <h2 class="accordion-header">

                        <button
                            class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= $collapseId ?>"
                            aria-expanded="false"
                            aria-controls="<?= $collapseId ?>"
                        >

                            <div class="w-100 d-flex justify-content-between align-items-center me-3">

                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($semesterName) ?>
                                    </div>

                                    <div class="text-muted small">
                                        <?= $subjectCount ?> Subjects
                                    </div>
                                </div>

                                <span class="badge bg-light text-dark border">
                                    <?= $totalUnits ?> Units
                                </span>

                            </div>

                        </button>

                    </h2>


                    <div
                        id="<?= $collapseId ?>"
                        class="accordion-collapse collapse"
                        data-bs-parent="#academicHistory"
                    >

                        <div class="accordion-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject</th>
                                            <th>Units</th>
                                            <th>Grades</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($subjects as $subject): ?>

                                            <tr>

                                                <td>
                                                    <span class="fw-semibold">
                                                        <?= htmlspecialchars($subject['subject_code']) ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($subject['subject_name']) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($subject['subject_units']) ?>
                                                </td>
                                                <td>
                                                    -
                                                </td>

                                                <td>

                                                    <?php if ($subject['status'] === 'Scheduled'): ?>

                                                        <span class="badge bg-success">
                                                           Enrolled
                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge bg-secondary">
                                                            Not Enrolled
                                                        </span>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

                <?php $semesterIndex++; ?>

            <?php endforeach; ?>

        </div>

        <?php $yearIndex++; ?>

    <?php endforeach; ?>

</div>




</div>

