<!-- Personal -->
<div class="tab-pane fade show active" id="personal">

    <div class="pt-3 mb-4">

        <h5 class="fw-bold mb-1">
            Personal Information
        </h5>

        <p class="text-muted small mb-0">
            Basic personal information of the student.
        </p>

    </div>


    <div class="card border shadow-none">

        <div class="card-body p-4">

            <div class="row g-4">


                
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            First Name
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_first_name ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>


               
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Last Name
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_surname ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>


                
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Middle Name
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_middle_name ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>


               
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Suffix
                        </div>

                        <div class="fw-semibold">
                            <?= !empty($applicant_suffix)
                                ? htmlspecialchars($applicant_suffix)
                                : '-' ?>
                        </div>

                    </div>

                </div>


            
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Birth Date
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_dob ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>


               
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Sex
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_sex ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>


              
                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Place of Birth
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_place_of_birth ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>


                <div class="col-md-6 col-xl-3">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="text-muted small mb-1">
                            Civil Status
                        </div>

                        <div class="fw-semibold">
                            <?= htmlspecialchars(
                                $applicant_civil_status ?? '-'
                            ) ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>