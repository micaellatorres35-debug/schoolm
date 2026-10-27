<!-- Parent / Guardian -->
<div class="tab-pane fade" id="parent">

    <!-- Section Header -->
    <div class="pt-3 mb-4">

        <h5 class="fw-bold mb-1">
            Parent / Guardian Information
        </h5>

        <p class="text-muted small mb-0">
            Contact information of the student's parent or guardian.
        </p>

    </div>


    <!-- Parent / Guardian Information -->
    <div class="card border shadow-none">

        <div class="card-body p-4">

            <div class="row g-4">


                <!-- Parent Name -->
                <div class="col-md-6 col-xl-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="d-flex align-items-center mb-2">

                            <div
                                class="rounded-circle
                                       bg-primary bg-opacity-10
                                       text-primary
                                       d-flex align-items-center
                                       justify-content-center me-2"
                                style="width:36px;height:36px;">

                                <i class="bi bi-person"></i>

                            </div>

                            <div class="text-muted small">
                                Parent / Guardian Name
                            </div>

                        </div>

                        <div class="fw-semibold">

                            <?= htmlspecialchars(
                                $applicant_parent_name ?? '-'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- Parent Contact -->
                <div class="col-md-6 col-xl-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="d-flex align-items-center mb-2">

                            <div
                                class="rounded-circle
                                       bg-primary bg-opacity-10
                                       text-primary
                                       d-flex align-items-center
                                       justify-content-center me-2"
                                style="width:36px;height:36px;">

                                <i class="bi bi-telephone"></i>

                            </div>

                            <div class="text-muted small">
                                Contact Number
                            </div>

                        </div>

                        <div class="fw-semibold">

                            <?= htmlspecialchars(
                                $applicant_parent_contact ?? '-'
                            ) ?>

                        </div>

                    </div>

                </div>


               
                <div class="col-md-12 col-xl-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="d-flex align-items-center mb-2">

                            <div
                                class="rounded-circle
                                       bg-primary bg-opacity-10
                                       text-primary
                                       d-flex align-items-center
                                       justify-content-center me-2"
                                style="width:36px;height:36px;">

                                <i class="bi bi-geo-alt"></i>

                            </div>

                            <div class="text-muted small">
                                Address
                            </div>

                        </div>

                        <div class="fw-semibold">

                            <?= htmlspecialchars(
                                $applicant_parent_address ?? '-'
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="mt-4">

        <h6 class="fw-bold text-primary mb-3">
            Emergency Contact
        </h6>

        <div class="card border shadow-none">

            <div class="card-body p-4">

                <div class="row g-4">

                    <div class="col-md-6">

                        <div class="text-muted small mb-1">
                            Contact Person
                        </div>

                        <div class="fw-semibold">
                            <?= $applicant_parent_name  ?>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="text-muted small mb-1">
                            Contact Number
                        </div>

                        <div class="fw-semibold">
                            <?= $applicant_parent_contact ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>