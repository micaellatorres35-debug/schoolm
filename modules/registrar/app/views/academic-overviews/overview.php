
<div class="tab-pane fade show active" id="overview">

    <!-- Section Header -->
    <div class="pt-3 mb-4">

        <h5 class="fw-bold mb-1">
            Academic Overview
        </h5>

        <p class="text-muted small mb-0">
            Current academic information and enrollment status of the student.
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

        <div class="card-header bg-white border-bottom py-3 px-4">

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

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th class="px-4">
                                Subject Code
                            </th>

                            <th>
                                Subject
                            </th>

                            <th>
                                Units
                            </th>


                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($enrollments)): ?>

                             <?php foreach ($enrollments as $enrollment): ?>
                            
                             <tr>
                                <td class="px-4 fw-semibold"> 
                                    <?= htmlspecialchars( $enrollment['subject_code'] ?? '-' ) ?>
                                </td> 
                                <td class="px-4 fw-semibold"> 
                                    <?= htmlspecialchars( $enrollment['subject_name'] ?? '-' ) ?>
                                </td> 

                                 <td class="px-4 fw-semibold"> 
                                    <?= htmlspecialchars( $enrollment['units'] ?? '-' ) ?>
                                </td> 

                             </tr>

                        <?php endforeach; ?> 
                        <?php else: ?>
                        <tr> 
                        <td colspan="4" class="text-center text-muted py-4">
                        No enrolled subjects found.
                        </td> 
                        </tr>
                        <?php endif; ?> 
                    </tbody>

                </table>

            </div>

        </div>

    </div>



</div>

