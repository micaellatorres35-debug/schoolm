<?php require __DIR__ . '/../partials/notif.php' ?>

<div class="table-responsive">

    <table class="table table-bordered table-hover align-middle text-sm concern-table">

        <thead class="table-dark text-nowrap">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Department</th>
                <th>Submitted By</th>
                <th>Submitted On</th>
                <th>Status</th>
                <th>Attachment</th>

                <!--
                <th class="text-center">Actions</th>
                -->

            </tr>
        </thead>

        <tbody id="concernTableBody">

            <?php if (!empty($issues)) : ?>

                <?php foreach ($issues as $issue) : ?>

                    <tr>

                        <!-- ID -->
                        <td>
                            <?= htmlspecialchars($issue['issue_id']) ?>
                        </td>

                        <!-- Title -->
                        <td>
                            <strong>
                                <?= htmlspecialchars($issue['title']) ?>
                            </strong>
                        </td>

                        <!-- Department -->
                        <td>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($issue['department_name'] ?? 'N/A') ?>
                            </span>
                        </td>

                        <!-- Submitted By -->
                        <td>
                            <?= htmlspecialchars($issue['submitted_by_name'] ?? 'N/A') ?>
                        </td>

                        <!-- Submitted On -->
                        <td class="text-nowrap">
                            <?= date(
                                'M d, Y',
                                strtotime($issue['submitted_on'])
                            ) ?>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            $status = strtolower($issue['status'] ?? 'open');

                            $badgeClass = match ($status) {
                                'resolved' => 'bg-success',
                                'open' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>

                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>

                        <!-- Attachment -->
                        <td>

                            <?php if (!empty($issue['file_path'])): ?>

                                <a
                                    href="http://localhost/SchoolManagementSystem/Modules/laboratory/public/<?= ltrim($issue['file_path'], '/') ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    target="_blank">
                                    View
                                </a>

                            <?php else: ?>

                                <span class="text-muted">
                                    No file
                                </span>

                            <?php endif; ?>

                        </td>

                        <!--
                        ==========================================
                        ACTIONS - CURRENTLY DISABLED
                        ==========================================

                        <td class="text-center">

                            <div class="d-flex justify-content-center align-items-center gap-1 flex-wrap">

                                <?php if ($status !== 'resolved'): ?>

                                    <form
                                        method="POST"
                                        action="concern-issue-tracking/resolve"
                                        class="m-0">

                                        <input
                                            type="hidden"
                                            name="issue_id"
                                            value="<?= htmlspecialchars($issue['issue_id']) ?>">

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="resolved">

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-success">
                                            Resolve
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span class="badge bg-success">
                                        Resolved
                                    </span>

                                <?php endif; ?>

                                <form
                                    method="POST"
                                    action="concern-issue-tracking/delete"
                                    class="m-0">

                                    <input
                                        type="hidden"
                                        name="issue_id"
                                        value="<?= htmlspecialchars($issue['issue_id']) ?>">

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger">
                                        Delete
                                    </button>

                                </form>

                            </div>

                        </td>

                        ==========================================
                        END ACTIONS
                        ==========================================
                        -->

                    </tr>

                <?php endforeach; ?>

            <?php else : ?>

                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        No records found
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</div>