<?php require __DIR__ . '/../partials/notif.php' ?>

<div class="table-responsive">

    <table id="approvalDSTable"
        class="table table-striped table-bordered align-middle"
        style="width:100%">

        <thead class="text-nowrap">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Submitted By</th>
                <th>Department</th>
                <th>Decided By</th>
                <th>Status</th>
                <th>Attachment</th>
                <th>Approved At</th>
                <th>Decision</th>
                <th>Remarks</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>

        <tbody>

            <?php if (!empty($approvals)) : ?>

                <?php foreach ($approvals as $approval) : ?>

                    <tr data-approval-id="<?= htmlspecialchars($approval['approval_id']) ?>">

                        <!-- ID -->
                        <td class="text-nowrap">
                            <?= htmlspecialchars($approval['approval_id']) ?>
                        </td>

                        <!-- Title -->
                        <td>
                            <strong>
                                <?= htmlspecialchars($approval['title'] ?? 'N/A') ?>
                            </strong>
                        </td>

                        <!-- Submitted By -->
                        <td class="text-nowrap">
                            <?= htmlspecialchars($approval['submit_by'] ?? 'N/A') ?>
                        </td>

                        <!-- Department -->
                        <td>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($approval['department_name'] ?? 'N/A') ?>
                            </span>
                        </td>

                        <!-- Decided By -->
                        <td class="text-nowrap">
                            <?= htmlspecialchars($approval['approver_id'] ?? '-') ?>
                        </td>

                        <!-- Status -->
                        <td class="text-nowrap">
                            <?php
                            $decision = strtolower($approval['decision'] ?? 'pending');

                            $badgeClass = match ($decision) {
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default => 'bg-warning text-dark'
                            };
                            ?>

                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst($decision) ?>
                            </span>
                        </td>

                        <!-- Attachment -->
                        <td class="text-center text-nowrap">

                            <?php if (!empty($approval['file_path'])): ?>

                                <a href="<?= BASE_URL . '/public/' . ltrim($approval['file_path'], '/') ?>"
                                    class="btn btn-outline-primary btn-sm"
                                    target="_blank">
                                    <i class="fas fa-file me-1"></i>
                                    View File
                                </a>

                            <?php else: ?>

                                <span class="text-muted">
                                    No file
                                </span>

                            <?php endif; ?>

                        </td>

                        <!-- Approved At -->
                        <td class="text-center text-nowrap">

                            <?php if (($approval['decision'] ?? '') === 'Rejected'): ?>

                                <span class="text-danger">
                                    Rejected
                                </span>

                            <?php else: ?>

                                <?= !empty($approval['approved_at'])
                                    ? date('M d, Y', strtotime($approval['approved_at']))
                                    : '-' ?>

                            <?php endif; ?>

                        </td>

                        <!-- Decision -->
                        <td class="text-center text-nowrap">
                            <?= ucfirst($approval['decision'] ?? '-') ?>
                        </td>

                        <!-- Remarks -->
                        <td>
                            <?= htmlspecialchars($approval['remarks'] ?? '-') ?>
                        </td>

                        <!-- Action -->
                        <td class="text-center">

                            <div class="dropdown">

                                <button
                                    class="btn btn-secondary btn-sm dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown">

                                    Action

                                </button>

                                <ul class="dropdown-menu">

                                    <!-- Approve -->
                                    <li>
                                        <a href="#"
                                            class="dropdown-item text-success btn-decision"
                                            data-id="<?= $approval['approval_id'] ?>"
                                            data-action="approved">

                                            <i class="fas fa-check me-2"></i>
                                            Approve

                                        </a>
                                    </li>

                                    <!-- Reject -->
                                    <li>
                                        <a href="#"
                                            class="dropdown-item text-danger btn-decision"
                                            data-id="<?= $approval['approval_id'] ?>"
                                            data-action="rejected">

                                            <i class="fas fa-times me-2"></i>
                                            Reject

                                        </a>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <!-- Delete -->
                                    <!-- <li>

                                        <form method="POST"
                                            action="approval-decision-support/delete">

                                            <input
                                                type="hidden"
                                                name="approval_id"
                                                value="<?= htmlspecialchars($approval['approval_id']) ?>">

                                            <button
                                                type="submit"
                                                class="dropdown-item text-danger">

                                                <i class="fas fa-trash me-2"></i>
                                                Delete

                                            </button>

                                        </form>

                                    </li> -->

                                </ul>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else : ?>

                <tr>
                    <td colspan="11" class="text-center text-muted py-4">

                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>

                        No approvals found

                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>