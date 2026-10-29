<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            <?= $showDeleted ? 'Deleted Players' : 'Players Management' ?>
            <?php if (session()->get('event_id')): ?>
                <?php endif; ?>
        </h5>
        <div>
            <?php if ($showDeleted): ?>
                <a href="<?= base_url('/admin/players') ?>" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-eye me-1"></i>View Active
                </a>
            <?php else: ?>
                <button class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                    <i class="fas fa-plus me-1"></i>Add Player
                </button>
                <a href="<?= base_url('/admin/players?deleted=1') ?>" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-trash me-1"></i>
                </a>
            <?php endif; ?>
            <div class="btn-group">
                <a href="<?= base_url('/admin/players/export/pdf') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>PDF
                </a>
                <a href="<?= base_url('/admin/players/export/excel') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-file-excel me-1"></i>Excel
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (!$showDeleted): ?>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <div class="row text-center">
                                <div class="col-3">
                                    <strong class="text-primary"><?= count(array_filter($players, function($p) { return $p['auction_status'] === 'available' || empty($p['auction_status']); })) ?></strong>
                                    <br><small>Available</small>
                                </div>
                                <div class="col-3">
                                    <strong class="text-success"><?= count(array_filter($players, function($p) { return $p['auction_status'] === 'sold'; })) ?></strong>
                                    <br><small>Sold</small>
                                </div>
                                <div class="col-3">
                                    <strong class="text-secondary"><?= count(array_filter($players, function($p) { return $p['auction_status'] === 'unsold' || $p['is_unsold']; })) ?></strong>
                                    <br><small>Unsold</small>
                                </div>
                                <div class="col-3">
                                    <strong class="text-dark"><?= count($players) ?></strong>
                                    <br><small>Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>Player ID</th>
                        <th>Player Name</th>
                        <th>Role</th>
                        <th>Category</th> <th>Chapter</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>Final Price</th>
                        <th>Sold To</th>
                        <th>Status</th>
                        <?php if (!$showDeleted): ?>
                            <th>Actions</th>
                        <?php else: ?>
                            <th>Restore</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $sno = 1; ?>
                    <?php foreach ($players as $player): ?>
                        <tr data-event-id="<?= $player['event_id'] ?? 'N/A' ?>">
                            <td>
                                <?= $player['player_id'] ?>
                                </td>
                            <td>
                                <?php if ($player['profile_pic']): ?>
                                    <img src="<?= base_url('uploads/players/' . $player['profile_pic']) ?>" width="30"
                                        height="30" class="rounded-circle me-2">
                                <?php endif; ?>
                                <a href="<?= base_url('admin/auction?player_id=' . $player['player_id'] . '&search=' . urlencode($player['player_name'])) ?>"
                                    class="player-name clickable"
                                    data-player-name="<?= htmlspecialchars($player['player_name']) ?>" 
                                    style="text-decoration:none; color:#000 !important;">
                                    <?= $player['player_name'] ?>
                                </a>
                                <?php if (!empty($player['cricheroes_url'])): ?>
                                    <br><a href="<?= $player['cricheroes_url'] ?>" target="_blank" class="badge bg-info text-decoration-none" title="View CricHeroes Profile">
                                        <i class="fas fa-external-link-alt"></i> CricHeroes
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $player['player_role'] === 'batsman' ? 'primary' :
                                    ($player['player_role'] === 'bowler' ? 'success' :
                                        ($player['player_role'] === 'allrounder' ? 'warning' : 'info')) ?>">
                                    <?= ucfirst($player['player_role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $player['category'] ? ucfirst(str_replace('_', ' ', $player['category'])) : '-' ?>
                                </span>
                            </td> <td><?= $player['chapter_name'] ?></td>
                            <td><?= $player['company_name'] ?></td>
                            <td><?= $player['phone_number'] ?></td>
                            <td>
                                <?php if ($player['final_price']): ?>
                                    ₹<span class="indian-price"><?= $player['final_price'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($player['sold_to_name'])): ?>
                                    <span class="badge bg-success"><?= $player['sold_to_name'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($player['is_unsold'] || $player['auction_status'] === 'unsold'): ?>
                                    <span class="badge bg-secondary">Unsold</span>
                                <?php elseif ($player['sold_to'] || $player['auction_status'] === 'sold'): ?>
                                    <span class="badge bg-success">Sold</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($showDeleted): ?>
                                    <a href="<?= base_url('/admin/players/restore/' . $player['player_id']) ?>"
                                        class="btn btn-outline-success btn-sm" 
                                        onclick="return confirm('Are you sure you want to restore this player?')">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-outline-primary btn-sm edit-player"
                                        data-id="<?= $player['player_id'] ?>"
                                        title="Edit Player">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?= base_url('/admin/players/delete/' . $player['player_id']) ?>"
                                        class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this player? This action will also refund any auction amount.')"
                                        title="Delete Player">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($players)): ?>
                        <tr>
                            <td colspan="13" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No players found for this event.</p>
                                <?php if (!$showDeleted): ?>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                                        <i class="fas fa-plus me-1"></i>Add Your First Player
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Add New Player 
                    <?php if (session()->get('event_id')): ?>
                        <small class="text-muted">(Event ID: <?= session()->get('event_id') ?>)</small>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('/admin/players/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        This player will be added to Event ID: <?= session()->get('event_id') ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="player_name" class="form-label">Player Name *</label>
                                <input type="text" class="form-control" id="player_name" name="player_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="player_role" class="form-label">Role *</label>
                                <select class="form-select" id="player_role" name="player_role" required>
                                    <option value="">Select Role</option>
                                    <option value="batsman">Batsman</option>
                                    <option value="bowler">Bowler</option>
                                    <option value="allrounder">Allrounder</option>
                                    <option value="wicketkeeper">Wicketkeeper</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label> <select class="form-select" id="category" name="category">
                                    <option value="">Select Category</option>
                                    <option value="under 35">Under 35</option>
                                    <option value="36 to 49">36 to 49</option>
                                    <option value="above 50">Above 50</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="batting_type" class="form-label">Batting Type</label>
                                <input type="text" class="form-control" id="batting_type" name="batting_type"
                                    placeholder="e.g., Right-handed">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bowling_type" class="form-label">Bowling Type</label>
                                <input type="text" class="form-control" id="bowling_type" name="bowling_type"
                                    placeholder="e.g., Right-arm medium">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="chapter_name" class="form-label">Chapter</label>
                                <input type="text" class="form-control" id="chapter_name" name="chapter_name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="business_category" class="form-label">Business Category</label>
                                <input type="text" class="form-control" id="business_category" name="business_category">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company</label>
                                <input type="text" class="form-control" id="company_name" name="company_name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cricheroes_url" class="form-label">CricHeroes Profile URL</label>
                                <input type="url" class="form-control" id="cricheroes_url" name="cricheroes_url"
                                    placeholder="https://cricheroes.com/player-profile/...">
                                <small class="form-text text-muted">Optional: Link to player's CricHeroes profile</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="profile_pic" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="background-color: black;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Add Player</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPlayerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Edit Player
                    <?php if (session()->get('event_id')): ?>
                        <small class="text-muted">(Event ID: <?= session()->get('event_id') ?>)</small>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPlayerForm" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Editing player from Event ID: <?= session()->get('event_id') ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_player_name" class="form-label">Player Name *</label>
                                <input type="text" class="form-control" id="edit_player_name" name="player_name"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_player_role" class="form-label">Role *</label>
                                <select class="form-select" id="edit_player_role" name="player_role" required>
                                    <option value="">Select Role</option>
                                    <option value="batsman">Batsman</option>
                                    <option value="bowler">Bowler</option>
                                    <option value="allrounder">Allrounder</option>
                                    <option value="wicketkeeper">Wicketkeeper</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_category" class="form-label">Category</label> <select class="form-select" id="edit_category" name="category">
                                    <option value="">Select Category</option>
                                    <option value="under 35">Under 35</option>
                                    <option value="36 to 49">36 to 49</option>
                                    <option value="above 50">Above 50</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_batting_type" class="form-label">Batting Type</label>
                                <input type="text" class="form-control" id="edit_batting_type" name="batting_type">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_bowling_type" class="form-label">Bowling Type</label>
                                <input type="text" class="form-control" id="edit_bowling_type" name="bowling_type">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_chapter_name" class="form-label">Chapter</label>
                                <input type="text" class="form-control" id="edit_chapter_name" name="chapter_name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_business_category" class="form-label">Business Category</label>
                                <input type="text" class="form-control" id="edit_business_category"
                                    name="business_category">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_company_name" class="form-label">Company</label>
                                <input type="text" class="form-control" id="edit_company_name" name="company_name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="edit_phone_number" name="phone_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_cricheroes_url" class="form-label">CricHeroes Profile URL</label>
                                <input type="url" class="form-control" id="edit_cricheroes_url" name="cricheroes_url"
                                    placeholder="https://cricheroes.com/player-profile/...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_profile_pic" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" id="edit_profile_pic" name="profile_pic"
                                    accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" style="background-color: black;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Player</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const activeMenu = 'players';
    const currentEventId = '<?= session()->get("event_id") ?>';

    // Handle edit player
    $('.edit-player').click(function () {
        const playerId = $(this).data('id');

        $.get('<?= base_url('/admin/players/edit/') ?>' + playerId, function (data) {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            // Populate form fields
            $('#edit_player_name').val(data.player_name);
            $('#edit_player_role').val(data.player_role);
            $('#edit_category').val(data.category); // UPDATED: Set category value
            $('#edit_batting_type').val(data.batting_type);
            $('#edit_bowling_type').val(data.bowling_type);
            $('#edit_chapter_name').val(data.chapter_name);
            $('#edit_business_category').val(data.business_category);
            $('#edit_company_name').val(data.company_name);
            $('#edit_phone_number').val(data.phone_number);
            $('#edit_cricheroes_url').val(data.cricheroes_url || '');

            $('#editPlayerForm').attr('action', '<?= base_url('/admin/players/update/') ?>' + playerId);
            $('#editPlayerModal').modal('show');
        }).fail(function(xhr, status, error) {
            alert('Failed to load player data. Please try again.');
            console.error('AJAX Error:', error);
        });
    });

    // When player name in table is clicked
    $(document).on('click', '.player-name', function (e) {
        e.preventDefault(); // stop normal link navigation

        const playerName = $(this).data('player-name');
        const href = $(this).attr('href');

        $('#player-search')
            .val(playerName)
            .trigger('input');

        // Optional: give a tiny delay to let user *see* the search trigger
        setTimeout(function () {
            window.location.href = href;
        }, 300); // or 0 if you're heartless
    });


    // Price formatting function
    function formatPrice(price) {
        if (!price) return '0';
        return parseInt(price).toLocaleString('en-IN');
    }

    // Format prices on page load
    document.addEventListener("DOMContentLoaded", function() {
        const priceElements = document.querySelectorAll('.indian-price');
        priceElements.forEach(el => {
            const price = el.textContent;
            el.textContent = formatPrice(price);
        });

        // Show event info in console for debugging
        if (currentEventId) {
            console.log('Current Event ID:', currentEventId);
        }
    });

    // Validation for forms
    $('#addPlayerModal form, #editPlayerModal form').on('submit', function(e) {
        const playerName = $(this).find('input[name="player_name"]').val().trim();
        const playerRole = $(this).find('select[name="player_role"]').val();

        if (!playerName || !playerRole) {
            e.preventDefault();
            alert('Player name and role are required fields.');
            return false;
        }
    });
</script>

<?= $this->endSection() ?>