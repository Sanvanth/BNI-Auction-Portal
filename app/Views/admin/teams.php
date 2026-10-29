<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Teams Management<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="page-title">Teams Management</h2>
                <div class="d-flex align-items-center">
                    <!-- <span class="badge bg-primary me-2">Event ID: <?= $eventId ?></span> -->
                    <span class="text-muted">Total Teams: <?= count($teams) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Current Auction Settings </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Max Purse:</strong> ₹<span class="indian-price"><?= $settings['max_purse'] ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Base Price:</strong> ₹<span class="indian-price"><?= $settings['base_price'] ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Auction Players:</strong> <?= $settings['total_players_per_team'] ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Hold Amount:</strong> ₹<span class="indian-price"><?= $settings['total_players_per_team'] * $settings['base_price'] ?></span>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <strong>Note:</strong> Hold Amount = Auction Players × Base Price. Captain & Owner prices are separate from Hold Amount.
                        Total Players per Team = Auction Players + Captain + Owner.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Teams Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 d-flex justify-content-between align-items-center">
                        <?= $showDeleted ? 'Deleted Teams' : 'Active Teams' ?>
                        <!-- <small class="text-muted">(Event: <?= $eventId ?>)</small> -->
                        <div>
                            <?php if (!$showDeleted): ?>
                                <button class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                                    <i class="fas fa-plus"></i> Add Team
                                </button>
                                <a href="?deleted=1" class="btn btn-outline-light btn-sm me-2">
                                    <i class="fas fa-trash"></i> View Deleted
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('admin/teams') ?>" class="btn btn-success btn-sm me-2">
                                    <i class="fas fa-arrow-left"></i> Back to Active
                                </a>
                            <?php endif; ?>
                            <div class="btn-group">
                                <a href="<?= base_url('admin/teams/export/pdf') ?>" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <a href="<?= base_url('admin/teams/export/excel') ?>" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                            </div>
                        </div>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($teams)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No teams found for Event ID: <?= $eventId ?></h5>
                            <p class="text-muted">Teams will be filtered by your current event session.</p>
                            <?php if (!$showDeleted): ?>
                                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                                    <i class="fas fa-plus"></i> Add First Team
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Logo</th>
                                        <th>Team Name</th>
                                        <th>Captain</th>
                                        <th>Owner</th>
                                        <th>Auction Players</th>
                                        <th>Total Players</th>
                                        <th>Hold Amount</th>
                                        <th>Available Purse</th>
                                        <th>Max Purse</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teams as $team): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($team['team_logo'])): ?>
                                                    <img src="<?= base_url('uploads/teams/' . $team['team_logo']) ?>"
                                                        alt="<?= esc($team['team_name']) ?>"
                                                        class="rounded-circle"
                                                        style="width: 40px; height: 40px; object-fit: cover;"
                                                        onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'">
                                                <?php else: ?>
                                                    <img src="<?= base_url('uploads/teams/bni_logo.png') ?>"
                                                        alt="Default"
                                                        class="rounded-circle"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= esc($team['team_name']) ?></strong>
                                                <br><small class="text-muted">Team ID: <?= $team['team_id'] ?></small>
                                            </td>
                                            <td>
                                                <?= esc($team['captain_name'] ?? 'Not assigned') ?>
                                                <?php if ($team['captain_name']): ?>
                                                    <br><small class="text-muted">Player ID: <?= $team['captain_player_id'] ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= esc($team['owner_name'] ?? 'Not assigned') ?>
                                                <?php if ($team['owner_name']): ?>
                                                    <br><small class="text-muted">Player ID: <?= $team['owner_player_id'] ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $team['auction_players'] ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= $team['total_players_with_cap_owner'] ?></span>
                                            </td>
                                            <td>
                                                <span class="text-warning fw-bold">₹<span class="indian-price"><?= $team['hold_amount'] ?></span></span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">₹<span class="indian-price"><?= $team['purse'] ?></span></span>
                                            </td>
                                            <td>
                                                <span class="text-primary fw-bold">₹<span class="indian-price"><?= $team['max_purse'] ?></span></span>
                                            </td>
                                            <td>
                                                <?php if (!$showDeleted): ?>
                                                    <button class="btn btn-outline-primary btn-sm me-1"
                                                        onclick="editTeam(<?= $team['team_id'] ?>)"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="<?= base_url('admin/teams/delete/' . $team['team_id']) ?>"
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this team? All associated players will be reset.')"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/teams/restore/' . $team['team_id']) ?>"
                                                        class="btn btn-outline-success btn-sm"
                                                        onclick="return confirm('Are you sure you want to restore this team?')"
                                                        title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Team Summary -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Event Summary</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Total Teams:</strong> <?= count($teams) ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Players:</strong>
                                                <?= array_sum(array_column($teams, 'total_players_with_cap_owner')) ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Auction Players:</strong>
                                                <?= array_sum(array_column($teams, 'auction_players')) ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Available Players:</strong> <?= count($players) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Team Modal -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Team </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/teams/store') ?>" method="post" enctype="multipart/form-data" onsubmit="return validateAddTeamForm()">
                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                <div class="modal-body">
                    <!-- Event ID Display -->
                    <!-- <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This team will be created for <strong>Event ID: <?= $eventId ?></strong>
                    </div> -->

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="team_name" class="form-label">Team Name *</label>
                                <input type="text" class="form-control" id="team_name" name="team_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="team_logo" class="form-label">Team Logo</label>
                                <input type="file" class="form-control" id="team_logo" name="team_logo" accept="image/*">
                                <small class="text-muted">Recommended: 200x200px, max 2MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="captain_player_id" class="form-label">Captain *</label>
                                <select class="form-select" id="captain_player_id" name="captain_player_id" required>
                                    <option value="">Select Captain</option>
                                    <?php foreach ($players as $player): ?>
                                        <option value="<?= $player['player_id'] ?>" title="Player ID: <?= $player['player_id'] ?>">
                                            <?= esc($player['player_name']) ?> (ID: <?= $player['player_id'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- <small class="text-muted">Only players from Event <?= $eventId ?> are shown</small> -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="captain_price" class="form-label">Captain Price (₹) *</label>
                                <input type="number" class="form-control" id="captain_price" name="captain_price"
                                    min="0" step="1000" placeholder="0" required>
                                <small class="text-muted">Price for captain (not included in hold amount)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="owner_player_id" class="form-label">Owner *</label>
                                <select class="form-select" id="owner_player_id" name="owner_player_id" required>
                                    <option value="">Select Owner</option>
                                    <?php foreach ($players as $player): ?>
                                        <option value="<?= $player['player_id'] ?>" title="Player ID: <?= $player['player_id'] ?>">
                                            <?= esc($player['player_name']) ?> (ID: <?= $player['player_id'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- <small class="text-muted">Only players from Event <?= $eventId ?> are shown</small> -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="owner_price" class="form-label">Owner Price (₹) *</label>
                                <input type="number" class="form-control" id="owner_price" name="owner_price"
                                    min="0" step="1000" placeholder="0" required>
                                <small class="text-muted">Price for owner (not included in hold amount)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Preview -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Budget Preview</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted">Max Purse</small><br>
                                            <strong>₹<span class="indian-price"><?= $settings['max_purse'] ?></span></strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Hold Amount</small><br>
                                            <strong class="text-warning">₹<span class="indian-price"><?= $settings['total_players_per_team'] * $settings['base_price'] ?></span></strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Captain + Owner</small><br>
                                            <strong class="text-info" id="preview_cap_owner">₹<span class="indian-price">0</span></strong>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">Available Purse</small><br>
                                            <strong class="text-success" id="preview_available">₹<span class="indian-price"><?= $settings['max_purse'] - ($settings['total_players_per_team'] * $settings['base_price']) ?></span></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Team <small class="text-muted">(Event: <?= $eventId ?>)</small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTeamForm" method="post" enctype="multipart/form-data" onsubmit="return validateEditTeamForm()">
                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                <div class="modal-body">
                    <!-- Event ID Display -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Editing team for <strong>Event ID: <?= $eventId ?></strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_team_name" class="form-label">Team Name *</label>
                                <input type="text" class="form-control" id="edit_team_name" name="team_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_team_logo" class="form-label">Team Logo</label>
                                <input type="file" class="form-control" id="edit_team_logo" name="team_logo" accept="image/*">
                                <small class="text-muted">Leave empty to keep current logo</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_captain_player_id" class="form-label">Captain *</label>
                                <select class="form-select" id="edit_captain_player_id" name="captain_player_id" required>
                                    <option value="">Select Captain</option>
                                </select>
                                <!-- <small class="text-muted">Only players from Event <?= $eventId ?> are shown</small> -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_captain_price" class="form-label">Captain Price (₹) *</label>
                                <input type="number" class="form-control" id="edit_captain_price" name="captain_price"
                                    min="0" step="1000" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_owner_player_id" class="form-label">Owner *</label>
                                <select class="form-select" id="edit_owner_player_id" name="owner_player_id" required>
                                    <option value="">Select Owner</option>
                                </select>
                                <!-- <small class="text-muted">Only players from Event <?= $eventId ?> are shown</small> -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_owner_price" class="form-label">Owner Price (₹) *</label>
                                <input type="number" class="form-control" id="edit_owner_price" name="owner_price"
                                    min="0" step="1000" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Budget preview calculation with Indian rupee formatting
    function formatIndianCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    function updateBudgetPreview() {
        const captainPrice = parseFloat(document.getElementById('captain_price').value) || 0;
        const ownerPrice = parseFloat(document.getElementById('owner_price').value) || 0;
        const maxPurse = <?= $settings['max_purse'] ?>;
        const holdAmount = <?= $settings['total_players_per_team'] * $settings['base_price'] ?>;

        const totalCapOwner = captainPrice + ownerPrice;
        const availablePurse = maxPurse - holdAmount - totalCapOwner;

        document.getElementById('preview_cap_owner').textContent = formatIndianCurrency(totalCapOwner);
        document.getElementById('preview_available').textContent = formatIndianCurrency(availablePurse);

        // Change color based on available purse
        const availableElement = document.getElementById('preview_available');
        if (availablePurse < 0) {
            availableElement.className = 'text-danger';
        } else {
            availableElement.className = 'text-success';
        }
    }

    // Add event listeners for budget preview
    document.getElementById('captain_price').addEventListener('input', updateBudgetPreview);
    document.getElementById('owner_price').addEventListener('input', updateBudgetPreview);

    // Prevent captain and owner from being the same
    document.getElementById('captain_player_id').addEventListener('change', function() {
        const captainId = this.value;
        const ownerSelect = document.getElementById('owner_player_id');

        Array.from(ownerSelect.options).forEach(option => {
            if (option.value === captainId && captainId !== '') {
                option.disabled = true;
                if (ownerSelect.value === captainId) {
                    ownerSelect.value = '';
                }
            } else {
                option.disabled = false;
            }
        });
    });

    document.getElementById('owner_player_id').addEventListener('change', function() {
        const ownerId = this.value;
        const captainSelect = document.getElementById('captain_player_id');

        Array.from(captainSelect.options).forEach(option => {
            if (option.value === ownerId && ownerId !== '') {
                option.disabled = true;
                if (captainSelect.value === ownerId) {
                    captainSelect.value = '';
                }
            } else {
                option.disabled = false;
            }
        });
    });

    // Form validation functions
    function validateAddTeamForm() {
        const captainId = document.getElementById('captain_player_id').value;
        const ownerId = document.getElementById('owner_player_id').value;
        const captainPrice = document.getElementById('captain_price').value;
        const ownerPrice = document.getElementById('owner_price').value;

        if (!captainId) {
            alert('Please select a captain');
            return false;
        }

        if (!ownerId) {
            alert('Please select an owner');
            return false;
        }

        if (!captainPrice || captainPrice < 0) {
            alert('Please enter a valid captain price');
            return false;
        }

        if (!ownerPrice || ownerPrice < 0) {
            alert('Please enter a valid owner price');
            return false;
        }

        if (captainId === ownerId) {
            alert('Captain and owner cannot be the same player');
            return false;
        }

        return true;
    }

    function validateEditTeamForm() {
        const captainId = document.getElementById('edit_captain_player_id').value;
        const ownerId = document.getElementById('edit_owner_player_id').value;
        const captainPrice = document.getElementById('edit_captain_price').value;
        const ownerPrice = document.getElementById('edit_owner_price').value;

        if (!captainId) {
            alert('Please select a captain');
            return false;
        }

        if (!ownerId) {
            alert('Please select an owner');
            return false;
        }

        if (!captainPrice || captainPrice < 0) {
            alert('Please enter a valid captain price');
            return false;
        }

        if (!ownerPrice || ownerPrice < 0) {
            alert('Please enter a valid owner price');
            return false;
        }

        if (captainId === ownerId) {
            alert('Captain and owner cannot be the same player');
            return false;
        }

        return true;
    }

    // Edit team function with improved player dropdown population and event filtering
    function editTeam(teamId) {
        console.log('Editing team:', teamId, 'for event:', <?= $eventId ?>);

        fetch(`<?= base_url('admin/teams/edit') ?>/${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }

                console.log('Team data loaded:', data);

                // Get available players and current team players
                fetch(`<?= base_url('admin/teams/getAvailablePlayers') ?>/${teamId}`)
                    .then(response => response.json())
                    .then(playersData => {
                        console.log('Available players loaded:', playersData);

                        // Populate captain dropdown
                        const captainSelect = document.getElementById('edit_captain_player_id');
                        captainSelect.innerHTML = '<option value="">Select Captain</option>';
                        playersData.availablePlayers.forEach(player => {
                            const option = document.createElement('option');
                            option.value = player.player_id;
                            option.textContent = `${player.player_name} (ID: ${player.player_id})`;
                            option.title = `Player ID: ${player.player_id}`;
                            captainSelect.appendChild(option);
                        });

                        // Populate owner dropdown
                        const ownerSelect = document.getElementById('edit_owner_player_id');
                        ownerSelect.innerHTML = '<option value="">Select Owner</option>';
                        playersData.availablePlayers.forEach(player => {
                            const option = document.createElement('option');
                            option.value = player.player_id;
                            option.textContent = `${player.player_name} (ID: ${player.player_id})`;
                            option.title = `Player ID: ${player.player_id}`;
                            ownerSelect.appendChild(option);
                        });

                        // Populate form with existing data
                        document.getElementById('edit_team_name').value = data.team_name;
                        document.getElementById('edit_captain_player_id').value = data.captain_player_id || '';
                        document.getElementById('edit_owner_player_id').value = data.owner_player_id || '';
                        document.getElementById('edit_captain_price').value = data.captain_price || 0;
                        document.getElementById('edit_owner_price').value = data.owner_price || 0;

                        // Set form action
                        document.getElementById('editTeamForm').action = `<?= base_url('admin/teams/update') ?>/${teamId}`;

                        // Show modal
                        new bootstrap.Modal(document.getElementById('editTeamModal')).show();
                    })
                    .catch(error => {
                        console.error('Error loading players:', error);
                        alert('Failed to load player data for this event');
                    });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load team data');
            });
    }

    // Format price as Indian Rupees
    function formatPrice(price) {
        if (!price) return '0';
        return parseInt(price).toLocaleString('en-IN');
    }

    // Format all elements with .indian-price class on page load
    document.addEventListener("DOMContentLoaded", function() {
        const priceElements = document.querySelectorAll('.indian-price');
        priceElements.forEach(el => {
            const price = el.textContent;
            el.textContent = formatPrice(price);
        });

        // Add event notification
        console.log('Teams page loaded for Event ID:', <?= $eventId ?>);
    });

    // Add event ID validation
    window.addEventListener('beforeunload', function(e) {
        const currentEventId = <?= $eventId ?>;
        if (currentEventId === null || currentEventId === undefined) {
            e.preventDefault();
            e.returnValue = 'Event session may have expired. Please refresh the page.';
        }
    });
</script>

<?= $this->endSection() ?>