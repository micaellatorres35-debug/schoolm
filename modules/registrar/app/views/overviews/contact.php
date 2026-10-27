<div class="tab-pane fade" id="contact">

                    <!-- CONTACT INFORMATION -->
                    <div class="pt-3">

                        <h5 class="fw-bold mb-1">
                            CONTACT INFORMATION
                        </h5>

                        <p class="text-muted small mb-4">
                            Student's primary contact details.
                        </p>


                        <div class="row g-3">

                            <!-- Email -->
                            <div class="col-md-6">

                                <div class="border rounded-3 p-3 h-100 bg-white">

                                    <div class="d-flex align-items-start">

                                        <div
                                            class="rounded-circle bg-primary bg-opacity-10
                                                text-primary d-flex align-items-center
                                                justify-content-center me-3"
                                            style="width:42px;height:42px;">

                                            <i class="bi bi-envelope fs-5"></i>

                                        </div>

                                        <div>

                                            <div class="text-muted small mb-1">
                                                Email Address
                                            </div>

                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($applicant_email ?? '-') ?>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- Mobile Number -->
                            <div class="col-md-6">

                                <div class="border rounded-3 p-3 h-100 bg-white">

                                    <div class="d-flex align-items-start">

                                        <div
                                            class="rounded-circle bg-success bg-opacity-10
                                                text-success d-flex align-items-center
                                                justify-content-center me-3"
                                            style="width:42px;height:42px;">

                                            <i class="bi bi-phone fs-5"></i>

                                        </div>

                                        <div>

                                            <div class="text-muted small mb-1">
                                                Mobile Number
                                            </div>

                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($applicant_contact_number ?? '-') ?>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ADDRESS -->
                    <div class="pt-4">

                        <h5 class="fw-bold mb-1">
                            ADDRESS
                        </h5>

                        <p class="text-muted small mb-4">
                            Student's current residential address.
                        </p>


                        <div class="card border shadow-none">

                            <div class="card-body">

                                <div class="row g-4">

                                    <!-- Barangay -->
                                    <div class="col-md-4">

                                        <div class="text-muted small mb-1">
                                            Barangay
                                        </div>

                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($applicant_barangay ?? '-') ?>
                                        </div>

                                    </div>


                                    <!-- City -->
                                    <div class="col-md-4">

                                        <div class="text-muted small mb-1">
                                            City / Municipality
                                        </div>

                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($applicant_city ?? '-') ?>
                                        </div>

                                    </div>


                                    <!-- Province -->
                                    <div class="col-md-4">

                                        <div class="text-muted small mb-1">
                                            Province
                                        </div>

                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($applicant_province ?? '-') ?>
                                        </div>

                                    </div>


                                    <!-- Complete Address -->
                                    <div class="col-12">

                                        <hr class="my-0">

                                    </div>


                                    <div class="col-12">

                                        <div class="text-muted small mb-1">
                                            Complete Address
                                        </div>

                                        <div class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $applicant_address_complete ?? '-'
                                            ) ?>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>