<!DOCTYPE html>
<html lang="en">


<?php require('components/head.php'); ?>
<style>
    .btn-action:hover {
        color: black;
        background-color: whitesmoke;
    }
</style>

<body>
    <!-- Navigation -->

    <?php require('components/navbar.php'); ?>


    <div class="container main-container superadmins-container">
        <!-- Statistics Cards -->
        <div class="row stats-cards">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">
                        <?= isset($stats['total_superadmins']) ? $stats['total_superadmins'] : '0' ?>
                    </div>
                    <div class="stat-label">Total Superadmins</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">
                        <?= isset($stats['active_superadmins']) ? $stats['active_superadmins'] : '0' ?>
                    </div>
                    <div class="stat-label">Active Superadmins</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">
                        <?= isset($stats['inactive_superadmins']) ? $stats['inactive_superadmins'] : '0' ?>
                    </div>
                    <div class="stat-label">Inactive Superadmins</div>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-title">
                    <i class="fas fa-user-shield me-2"></i>
                    <?= isset($showing_inactive) && $showing_inactive ? 'Inactive Superadmins' : 'Active Superadmins' ?>
                </h2>
                <div class="d-flex gap-2">
                    <?php if (isset($showing_inactive) && $showing_inactive): ?>
                        <a href="<?= base_url('/superadmin/superadmins') ?>" class="btn btn-create">
                            <i class="fas fa-users me-2"></i>Active Superadmins
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('/superadmin/superadmins/inactive') ?>" class="btn btn-create">
                            <i class="fas fa-user-times me-2"></i>Inactive Superadmins
                        </a>
                        <a href="<?= base_url('/superadmin/add-superadmin') ?>" class="btn btn-create">
                            <i class="fas fa-user-plus me-2"></i>Add Superadmin
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive">
                <table id="superadminsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Account Status</th>
                            <th><?= isset($showing_inactive) && $showing_inactive ? 'Deactivated' : 'Created' ?></th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($superadmins as $superadmin): ?>
                            <tr>
                                <td>
                                    <div class="superadmin-avatar">
                                        <?= strtoupper(substr($superadmin['username'], 0, 1)) ?>
                                    </div>
                                </td>
                                <td><?= esc($superadmin['username']) ?></td>
                                <td><span class="role-badge"><?= esc($superadmin['role']) ?></span></td>
                                <td>
                                    <?php if ($superadmin['is_active'] == 1): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($superadmin['created_at'])) ?><br>
                                    <small
                                        class="text-muted"><?= date('H:i A', strtotime($superadmin['created_at'])) ?></small>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($superadmin['updated_at'])) ?><br>
                                    <small
                                        class="text-muted"><?= date('H:i A', strtotime($superadmin['updated_at'])) ?></small>
                                </td>
                                <td>
                                    <?php if (isset($showing_inactive) && $showing_inactive): ?>
                                        <!-- Actions for inactive superadmins -->
                                        <button class="btn btn-action btn-activate"
                                            onclick="activateSuperadmin(<?= $superadmin['user_id'] ?>)"
                                            title="Activate Superadmin">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Actions for active superadmins -->
                                        <button class="btn btn-action btn-edit"
                                            onclick="editSuperadmin(<?= $superadmin['user_id'] ?>)"
                                            title="Edit Username">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-action btn-password"
                                            onclick="resetPassword(<?= $superadmin['user_id'] ?>)"
                                            title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-action btn-delete"
                                            onclick="deleteSuperadmin(<?= $superadmin['user_id'] ?>)"
                                            title="Deactivate Superadmin">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- Edit Superadmin Modal -->
    <div class="modal fade" id="editSuperadminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Superadmin Username
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSuperadminForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                            <div class="error-feedback" id="edit_username_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Username
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div class="modal fade" id="passwordResetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset the password for this superadmin?</p>
                    <p class="text-muted">A new random password will be generated and displayed.</p>
                    <div id="newPasswordDisplay" class="alert alert-success" style="display: none;">
                        <strong>New Password: </strong>
                        <span id="newPasswordText" class="font-monospace"></span>
                        <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyPassword()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmPasswordReset">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php require('components/footer.php'); ?>
</body>

</html>