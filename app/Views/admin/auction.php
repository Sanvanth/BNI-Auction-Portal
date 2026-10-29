<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Auction Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/* -----------------------------------------------------------
   Helper to format numbers in Indian currency style
----------------------------------------------------------- */
function format_inr($amount)
{
    $amount = (string) round($amount);
    $len = strlen($amount);
    if ($len <= 3)
        return $amount;

    $last3 = substr($amount, -3);
    $rest = substr($amount, 0, -3);
    $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);

    return $rest . "," . $last3;
}

// Get current user's event_id from session
$session = session();
$currentEventId = $session->get('event_id');
?>
<style>
    .team-logo-wrapper.selected {
        border: 5px solid green;
        border-radius: 50%;
    }

    .team-logo-wrapper.disabled {
        opacity: 0.4;
        pointer-events: none;
        filter: grayscale(100%);
    }

    #auction-status {
        border: 1px solid #ccc;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .team-logo-wrapper.selected {
        border: 2px solid green;
    }

    .close-player-btn,
    .close-player-btn:hover {
        position: absolute;
        top: 3px;
        right: 15px;
        z-index: 10;
        background: #ff0e0e;
        color: #fff;
        border: 3px solid #fff;
    }
</style>

<!-- ===== MAIN CONTAINER ===== -->
<div class="container-fluid">
    <div class="row">

        <!-- ===================================
             LEFT COLUMN : SEARCH / PLAYER / BID
        =================================== -->
        <div class="col-md-8">
            <div class="card shadow-lg border-0">

                <!-- header -->
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Player Auction</h3>

                        <button id="undo-button" class="btn btn-warning btn-sm" onclick="undoLastAction()">
                            Undo Last Action
                        </button>
                    </div>
                </div>

                <!-- body -->
                <div class="card-body">

                    <!-- ---------- SEARCH BOX ---------- -->
                    <div class="mb-4">
                        <label class="form-label fw-bold" for="player-search">Search Player (ID or Name)</label>
                        <div class="position-relative">
                            <input id="player-search" type="text" class="form-control form-control-lg shadow-sm"
                                placeholder="Enter player ID or name..." autocomplete="off"
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <div id="search-results" class="search-dropdown"></div>
                        </div>
                    </div>

                    <!-- ---------- PLAYER DETAILS CARD ---------- -->
                    <div id="player-details-card" class="card border-0 shadow-sm"
                        style="display:none;position:relative;">

                        <!-- close -->
                        <button class="btn btn-outline-secondary btn-sm close-player-btn"
                            onclick="closePlayerDetails()">
                            <i class="fas fa-times"></i>
                        </button>

                        <!-- header -->
                        <div class="card-header bg-gradient-secondary text-white">
                            <h5 class="mb-0">Player Details</h5>
                        </div>

                        <!-- body -->
                        <div class="card-body">
                            <div class="row">

                                <!-- photo / name -->
                                <div class="col-md-3 text-center">
                                    <div class="player-image-container mb-3">
                                        <img id="player-image" alt="Player"
                                            class="img-fluid rounded shadow-lg player-image">
                                    </div>
                                    <h3 id="player-name" class="text-primary fw-bold mb-3"></h3>
                                </div>

                                <!-- attributes -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="player-detail-item">
                                                <span class="detail-label">Role:</span>
                                                <span id="player-role" class="detail-value"></span>
                                            </div>
                                            <div class="player-detail-item">
                                                <span class="detail-label">Base Price:</span>
                                                <span class="detail-value text-success fw-bold">₹<span
                                                        id="base-price"></span></span>
                                            </div>
                                            <div class="player-detail-item">
                                                <span class="detail-label">Company:</span>
                                                <span id="company-name" class="detail-value"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="player-detail-item">
                                                <span class="detail-label">Chapter:</span>
                                                <span id="chapter-name" class="detail-value"></span>
                                            </div>
                                            <div class="player-detail-item">
                                                <span class="detail-label">Batting Style:</span>
                                                <span id="batting-type" class="detail-value"></span>
                                            </div>
                                            <div class="player-detail-item">
                                                <span class="detail-label">Bowling Style:</span>
                                                <span id="bowling-type" class="detail-value"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /row -->

                            <!-- ---------- FINAL ACTIONS ---------- -->
                            <div class="my-4">
                                <h5 class="text-info mb-3">Final Actions</h5>

                                <!-- teams -->
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <h6 class="text-info mb-3">Teams:</h6>
                                        <div id="team-logo-container" class="d-flex flex-wrap gap-3">
                                            <?php foreach ($teams as $team): ?>
                                                <div class="team-logo-wrapper" data-team-id="<?= $team['team_id'] ?>"
                                                    data-purse="<?= $team['purse'] ?>"
                                                    title="<?= esc($team['team_name']) ?> (₹<?= format_inr($team['purse']) ?>)">
                                                    <img src="<?= base_url('uploads/teams/' . $team['team_logo']) ?>"
                                                        class="team-logo img-thumbnail"
                                                        style="height:100px;width:100px;cursor:pointer;"
                                                        alt="<?= esc($team['team_name']) ?>">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <input type="hidden" id="final-team" name="final_team_id">
                                </div>

                                <!-- buttons -->
                                <div class="row">
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <button class="btn btn-success btn-lg w-100" onclick="markAsSold()">
                                            Mark as Sold
                                        </button>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12">
                                        <button class="btn btn-danger btn-lg w-100" onclick="markAsUnsold()">
                                            Mark as Unsold
                                        </button>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12 text-center">
                                        <button id="undobutton" class="btn btn-warning btn-lg"
                                            onclick="undoAuctionLastAction()">
                                            Undo Last Action
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ---------- LIVE STATUS ---------- -->
                            <div id="auction-status" class="card shadow-sm border-info border-2 p-3 mb-4">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-info fw-bold mb-3">Live Auction Status</h5>
                                    <div class="d-flex justify-content-around flex-wrap">
                                        <div class="auction-box text-center mb-2">
                                            <p class="mb-1 text-muted">Current Team</p>
                                            <h4 id="current-team-name" class="text-primary fw-semibold">None</h4>
                                        </div>
                                        <div class="auction-box text-center mb-2">
                                            <p class="mb-1 text-muted">Current Bid</p>
                                            <h4 class="text-success fw-semibold">
                                                ₹<span id="current-bid-amount" class="indian-price">0</span>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /card-body -->
                    </div><!-- /player-details-card -->

                </div><!-- /card-body -->
            </div><!-- /card -->
        </div><!-- /col-8 -->

        <!-- ======================================
             RIGHT COLUMN : TEAM PURSE + HISTORY
        ====================================== -->
        <div class="col-md-4">

            <!-- team status -->
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-info text-white">
                    <h3 class="card-title mb-0">Teams Status</h3>
                </div>
                <div class="card-body p-2">
                    <div id="teams-list">
                        <?php foreach ($teams as $team): ?>
                            <div class="team-card mb-2 p-3 border rounded shadow-sm" data-team-id="<?= $team['team_id'] ?>">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($team['team_logo'])): ?>
                                        <img src="<?= base_url('uploads/teams/' . $team['team_logo']) ?>"
                                            onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'"
                                            class="rounded-circle me-3 team-logo" alt="<?= esc($team['team_name']) ?>">
                                    <?php else: ?>
                                        <img src="<?= base_url('uploads/teams/bni_logo.png') ?>"
                                            class="rounded-circle me-3 team-logo" alt="Team">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1"><?= esc($team['team_name']) ?></h6>
                                        <p class="mb-0">
                                            Purse:
                                            <span class="text-success fw-bold">
                                                ₹<span class="indian-price"><?= $team['purse'] ?></span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- history -->
            <div class="card mt-4 shadow-lg border-0">
                <div class="card-header bg-gradient-warning text-dark">
                    <h3 class="card-title mb-0">History</h3>
                </div>
                <div class="card-body p-2">
                    <div id="auction-history" class="auction-history-container">
                        <p class="text-muted text-center">No history yet</p>
                    </div>
                </div>
            </div>

        </div><!-- /col-4 -->
    </div><!-- /row -->
</div><!-- /container -->

<!-- ===== ALERT CONTAINER ===== -->
<div id="alert-container" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>

<!-- ===== JS LIBRARIES ===== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"
    crossorigin="anonymous"></script>

<!-- =====================================================
     AUCTION LOGIC : dynamic settings, bidding, history
===================================================== -->
<script>
    /* ---------- GLOBAL VARIABLES ---------- */
    let currentPlayerId = null;
    let searchTimeout = null;
    let selectedTeamIds = new Set();
    let currentBidPrice = 0;
    let lastSelectedTeamId = null;

    let basePrice = 500000;  // overwritten by settings
    let bidIncrementRules = [];      // [{min_amount,max_amount,increment_amount},...]

    /* ---------- INITIALISATION ---------- */
    $(document).ready(() => {

        loadAuctionSettings();  // dynamic settings
        loadAuctionHistory();   // initial history
        setInterval(() => { loadAuctionHistory(); refreshTeamData(); }, 30000);

        /* search handler */
        $('#player-search').on('input', () => {
            const q = $('#player-search').val().trim();
            clearTimeout(searchTimeout);

            if (!q.length) { $('#search-results').empty().hide(); return; }

            searchTimeout = setTimeout(() => searchPlayers(q), 300);
        });

        /* preload from query param */
        if ($('#player-search').val().trim()) $('#player-search').trigger('input');

        /* hide dropdown on outside click */
        $(document).on('click', e => {
            if (!$(e.target).closest('#player-search, #search-results').length) $('#search-results').hide();
        });

        /* ESC clears */
        $('#player-search').on('keydown', e => {
            if (e.key === 'Escape') {
                $('#player-search').val('');
                $('#search-results').empty().hide();
            }
        });
    });

    /* ---------- LOAD SETTINGS ---------- */
    function loadAuctionSettings() {
        $.get('<?= base_url('admin/get-auction-settings') ?>')
            .done(s => {
                basePrice = parseFloat(s.base_price) || 500000;
                try { bidIncrementRules = JSON.parse(s.bid_increment_rules || '[]'); } catch { bidIncrementRules = []; }
            })
            .fail(() => console.warn('Failed to load settings – using defaults'));
    }

    /* next increment according to rules */
    function calculateNextIncrement(amount) {
        if (bidIncrementRules.length) {
            for (const r of bidIncrementRules) {
                const min = parseFloat(r.min_amount);
                const max = r.max_amount !== null ? parseFloat(r.max_amount) : null;
                if (amount >= min && (max === null || amount <= max)) return parseFloat(r.increment_amount);
            }
        }
        return amount < 10000000 ? 500000 : 1000000;
    }

    /* ---------- SEARCH PLAYERS ---------- */
    function searchPlayers(query) {
        $('#search-results').html('<div class="search-item loading">Searching...</div>').show();

        $.get('<?= base_url('admin/auction/searchPlayers') ?>', { q: query })
            .done(updateSearchResults)
            .fail(() => $('#search-results').html('<div class="search-item error">Search error</div>').show());
    }

    function updateSearchResults(list) {
        if (!list.length) {
            $('#search-results').html('<div class="search-item no-results">No available players found</div>').show();
            return;
        }
        let html = '';
        list.forEach(p => {
            const img = p.profile_pic ? '<?= base_url('uploads/players/') ?>' + p.profile_pic
                : '<?= base_url('uploads/teams/bni_logo.png') ?>';
            html += `
            <div class="search-item" onclick="selectPlayer('${p.player_id}')">
                <div class="d-flex align-items-center">
                    <img src="${img}" class="me-3" style="width:45px;height:45px;border-radius:50%;"
                         onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'" alt="P">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">${p.player_id} - ${p.player_name}</h6>
                        <span class="badge bg-primary me-1">${p.player_role}</span>
                        <span class="text-success fw-bold">₹${parseInt(p.base_price).toLocaleString('en-IN')}</span><br>
                        <small class="text-muted">${p.company_name || 'N/A'} | ${p.chapter_name || 'N/A'}</small>
                    </div>
                </div>
            </div>`;
        });
        $('#search-results').html(html).show();
    }

    /* ---------- SELECT & LOAD PLAYER ---------- */
    function selectPlayer(id) {
        $.post('<?= base_url('admin/auction/selectPlayer') ?>', { player_id: id })
            .always(() => loadPlayerDetails(id));  // whether success or not, load details locally
    }

    function loadPlayerDetails(id) {
        currentPlayerId = id;
        $('#search-results').hide();
        $('#player-search').val('Loading...');

        $.get('<?= base_url('admin/auction/getPlayerDetails') ?>/' + id)
            .done(d => {
                if (d.error) return showAlert('error', d.error);

                // populate
                $('#player-search').val(d.player_name);
                $('#player-name').text(d.player_name || 'N/A');
                $('#player-role').text(d.player_role || 'N/A');
                $('#base-price').text(parseInt(d.base_price).toLocaleString('en-IN'));
                $('#company-name').text(d.company_name || 'N/A');
                $('#chapter-name').text(d.chapter_name || 'N/A');
                $('#batting-type').text(d.batting_type || 'N/A');
                $('#bowling-type').text(d.bowling_type || 'N/A');

                const img = d.profile_pic ? '<?= base_url('uploads/players/') ?>' + d.profile_pic
                    : '<?= base_url('uploads/teams/bni_logo.png') ?>';
                $('#player-image').attr('src', img);

                currentBidPrice = 0;
                $('#current-bid-amount').text(parseInt(d.base_price).toLocaleString('en-IN'));

                selectedTeamIds.clear();
                lastSelectedTeamId = null;
                $('#current-team-name').text('None');
                $('#final-team').val('');

                updateTeamAvailability();
                $('#player-details-card').slideDown(300);
            })
            .fail(() => showAlert('error', 'Failed to load player details'));
    }

    function closePlayerDetails() {
        $('#player-details-card').slideUp(300);
        resetForm();
    }

    /* ---------- TEAM LOGO CLICK ---------- */
    $('#team-logo-container').on('click', '.team-logo-wrapper:not(.disabled)', function () {
        const teamId = $(this).data('team-id');
        const teamName = $(this).attr('title').split(' (')[0];
        const teamPurse = parseInt($(this).data('purse'));

        const inc = calculateNextIncrement(currentBidPrice);
        const nextBid = currentBidPrice + inc;

        if ((teamPurse + basePrice) < nextBid) {
            return showAlert('error', `${teamName} has insufficient funds`);
        }

        $('#final-team').val(teamId);
        lastSelectedTeamId = teamId;
        selectedTeamIds.add(teamId);
        currentBidPrice = nextBid;

        $('#current-team-name').text(teamName);
        $('#current-bid-amount').text(currentBidPrice.toLocaleString('en-IN'));

        $('.team-logo-wrapper').removeClass('selected');
        $(this).addClass('selected');

        updateTeamAvailability();
        pushLiveBid(teamId, currentBidPrice);
        showAlert('success', `${teamName} bid ₹${currentBidPrice.toLocaleString('en-IN')}`);
    });

    /* send bid to server for live display */
    function pushLiveBid(teamId, amount) {
        $.ajax({
            url: '<?= base_url('admin/players/updateCurrentBidTeam') ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ player_id: currentPlayerId, team_id: teamId, final_price: amount })
        });
    }

    /* ---------- TEAM AVAILABILITY ---------- */
    function updateTeamAvailability() {
        $('.team-logo-wrapper').each(function () {
            const purse = parseInt($(this).data('purse'));
            const nextBid = currentBidPrice + calculateNextIncrement(currentBidPrice);
            const disable = (purse + basePrice) < nextBid;

            $(this).toggleClass('disabled', disable);
            const title = $(this).attr('title').replace(' - Insufficient Funds', '');
            $(this).attr('title', disable ? title + ' - Insufficient Funds' : title);
        });
    }

    /* ---------- SOLD ---------- */
    function markAsSold() {
        if (!currentPlayerId) return showAlert('error', 'Select a player first');
        const teamId = $('#final-team').val();
        if (!teamId) return showAlert('error', 'Select a team');
        const price = currentBidPrice;

        const $btn = $('button[onclick="markAsSold()"]').prop('disabled', true).text('Processing...');
        $.post('<?= base_url('admin/auction/validateTeamPurse') ?>', { team_id: teamId, final_price: price })
            .done(v => {
                if (!v.valid) return showAlert('error', v.message);

                $.post('<?= base_url('admin/auction/markAsSold') ?>',
                    { player_id: currentPlayerId, team_id: teamId, final_price: price })
                    .done(r => {
                        if (r.success) {
                            showAlert('success', r.message + ' (Close card to continue)');
                            updateTeamPurse(teamId, r.new_purse);
                            resetForm();
                            loadAuctionHistory();
                        } else showAlert('error', r.message);
                    })
                    .fail(() => showAlert('error', 'Server error'));
            })
            .always(() => $btn.prop('disabled', false).text('Mark as Sold'));
    }

    /* ---------- UNSOLD ---------- */
    function markAsUnsold() {
        if (!currentPlayerId) return showAlert('error', 'Select a player first');
        if (!confirm('Mark this player as unsold?')) return;

        const $btn = $('button[onclick="markAsUnsold()"]').prop('disabled', true).text('Processing...');
        $.post('<?= base_url('admin/auction/markAsUnsold') ?>', { player_id: currentPlayerId })
            .done(r => {
                if (r.success) {
                    showAlert('success', r.message + ' (Close card to continue)');
                    resetForm();
                    loadAuctionHistory();
                } else showAlert('error', r.message);
            })
            .fail(() => showAlert('error', 'Server error'))
            .always(() => $btn.prop('disabled', false).text('Mark as Unsold'));
    }

    /* ---------- UNDO ---------- */
    function undoLastAction() {
        if (!confirm('Undo last bid/sale?')) return;
        const $b = $('#undo-button').prop('disabled', true).text('Undoing...');
        $.post('<?= base_url('admin/auction/undoLastAction') ?>')
            .done(r => {
                showAlert(r.success ? 'success' : 'error', r.message);
                refreshTeamData(); loadAuctionHistory();
            })
            .always(() => $b.prop('disabled', false).text('Undo Last Action'));
    }

    function undoAuctionLastAction() {
        if (!confirm('Undo last auction log?')) return;
        const $b = $('#undobutton').prop('disabled', true).text('Undoing...');
        $.post('<?= base_url('admin/auction/undoAuctionLastAction') ?>')
            .done(r => {
                if (r.success) { refreshTeamData(); loadAuctionHistory(); reloadCurrentPlayerStatus(); }
                else showAlert('error', r.message);
            })
            .always(() => $b.prop('disabled', false).text('Undo Last Action'));
    }

    function reloadCurrentPlayerStatus() {
        if (!currentPlayerId) return;
        $.get('<?= base_url('admin/auction/getPlayerDetails') ?>/' + currentPlayerId)
            .done(p => {
                currentBidPrice = parseInt(p.current_price || 0);
                $('#current-bid-amount').text(currentBidPrice.toLocaleString('en-IN'));
                $('#current-team-name').text(p.current_team_name || 'None');
                $('#final-team').val(p.current_team || '');
                $('.team-logo-wrapper').removeClass('selected');
                if (p.current_team) $(`.team-logo-wrapper[data-team-id="${p.current_team}"]`).addClass('selected');
                updateTeamAvailability();
            });
    }

    /* ---------- TEAM PURSE UPDATE LOCALLY ---------- */
    function updateTeamPurse(teamId, purse) {
        $(`.team-card[data-team-id="${teamId}"] .indian-price`).text(formatPrice(purse));
        $(`.team-logo-wrapper[data-team-id="${teamId}"]`).attr('data-purse', purse);
        updateTeamAvailability();
    }

    /* ---------- RESET FORM ---------- */
    function resetForm() {
        $('#player-search').val('');
        $('#final-team').val('');
        currentPlayerId = null;
        currentBidPrice = 0;
        lastSelectedTeamId = null;
        selectedTeamIds.clear();
        $('#current-team-name').text('None');
        $('#current-bid-amount').text('0');
        $('.team-logo-wrapper').removeClass('selected disabled');
    }

    /* ---------- HISTORY ---------- */
    function loadAuctionHistory() {
        $.get('<?= base_url('admin/auction/getHistory') ?>')
            .done(list => {
                if (!list.length) return $('#auction-history').html('<p class="text-muted text-center">No auction history</p>');
                let html = '';
                list.forEach(a => {
                    const txt = a.action_type === 'sold'
                        ? `${a.player_name} sold to ${a.team_name} for ₹${formatPrice(a.bid_amount)}`
                        : `${a.player_name} marked as unsold`;
                    const lbl = a.action_type === 'sold' ? 'SOLD' : 'UNSOLD';
                    html += `
                    <div class="auction-history-item mb-2 p-2 border-bottom">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-bold">${txt}</div>
                                <small class="text-muted">${a.formatted_time}</small>
                            </div>
                            <span class="badge ${a.action_type === 'sold' ? 'bg-success' : 'bg-danger'}">${lbl}</span>
                        </div>
                    </div>`;
                });
                $('#auction-history').html(html);
            })
            .fail(() => $('#auction-history').html('<p class="text-danger text-center">Error loading history</p>'));
    }

    /* ---------- TEAM DATA REFRESH ---------- */
    function refreshTeamData() {
        $.get('<?= base_url('admin/auction/refreshTeamData') ?>')
            .done(teams => {
                let html = '';
                teams.forEach(t => {
                    const logo = t.team_logo ? '<?= base_url('uploads/teams/') ?>' + t.team_logo
                        : '<?= base_url('uploads/teams/bni_logo.png') ?>';
                    html += `
                    <div class="team-card mb-2 p-3 border rounded shadow-sm" data-team-id="${t.team_id}">
                        <div class="d-flex align-items-center">
                            <img src="${logo}" class="rounded-circle me-3 team-logo"
                                 onerror="this.src='<?= base_url('uploads/teams/bni_logo.png') ?>'"
                                 alt="${t.team_name}">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">${t.team_name}</h6>
                                <p class="mb-0">
                                    Purse: <span class="text-success fw-bold">₹${formatPrice(t.purse)}</span>
                                </p>
                            </div>
                        </div>
                    </div>`;
                    $(`.team-logo-wrapper[data-team-id="${t.team_id}"]`).attr('data-purse', t.purse);
                });
                $('#teams-list').html(html);
                if (currentPlayerId) updateTeamAvailability();
            });
    }

    /* ---------- UTILITIES ---------- */
    function showAlert(type, msg) {
        const cls = type === 'success' ? 'alert-success' : type === 'info' ? 'alert-info' : 'alert-danger';
        const id = 'alert-' + Date.now();
        $('#alert-container').append(`
        <div id="${id}" class="alert ${cls} alert-dismissible fade show shadow-lg" role="alert">
            ${msg}
            <button type="button" class="btn-close" aria-label="Close"
                    onclick="document.getElementById('${id}').remove()"></button>
        </div>`);
        setTimeout(() => { $('#' + id).fadeOut(500, () => $('#' + id).remove()); }, 5000);
    }
    function formatPrice(n) { return parseInt(n || 0).toLocaleString('en-IN'); }
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.indian-price')
            .forEach(e => e.textContent = formatPrice(e.textContent));
    });
</script>
<style>
    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .search-item {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .search-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .search-item.loading,
    .search-item.error,
    .search-item.no-results {
        cursor: default;
    }

    .search-item.loading {
        color: #6c757d;
        font-style: italic;
        text-align: center;
    }

    .search-item.error {
        color: #dc3545;
        text-align: center;
    }

    .search-item.no-results {
        color: #6c757d;
        font-style: italic;
        text-align: center;
    }

    .search-player-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 50%;
    }

    .player-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 2px solid #ddd;
    }

    .player-detail-item {
        margin-bottom: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .detail-label {
        font-weight: bold;
        color: #495057;
        margin-right: 8px;
    }

    .detail-value {
        color: #212529;
    }

    .team-logo {
        width: 50px;
        height: 50px;
        object-fit: cover;
    }

    .auction-history-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .auction-history-item {
        transition: background-color 0.2s;
    }

    .auction-history-item:hover {
        background-color: #f8f9fa;
    }

    .bg-gradient-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
    }

    .bg-gradient-info {
        background: linear-gradient(45deg, #17a2b8, #117a8b);
    }

    .bg-gradient-success {
        background: linear-gradient(45deg, #28a745, #1e7e34);
    }

    .bg-gradient-warning {
        background: linear-gradient(45deg, #ffc107, #e0a800);
    }

    .bg-gradient-dark {
        background: linear-gradient(45deg, #343a40, #23272b);
    }

    .bg-gradient-secondary {
        background: linear-gradient(45deg, #6c757d, #545b62);
    }

    .team-logo-wrapper {
        position: relative;
        transition: all 0.3s ease;
        border-radius: 50%;
        overflow: hidden;
    }

    .team-logo-wrapper:hover:not(.disabled) {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .team-logo-wrapper.disabled {
        opacity: 0.3;
        pointer-events: none;
        filter: grayscale(100%);
        position: relative;
    }

    .team-logo-wrapper.disabled::after {
        content: 'ðŸ’¸';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 24px;
        z-index: 2;
    }

    .team-logo-wrapper.selected {
        border: 3px solid #28a745;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }

        50% {
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.8);
        }

        100% {
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
        }
    }

    .close-player-btn {
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .close-player-btn:hover {
        background-color: #dc3545;
        color: white;
        border-color: #fff;
    }

    #auction-status .auction-box {
        padding: 10px;
        margin: 5px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .player-image {
            width: 80px;
            height: 80px;
        }

        .team-logo {
            width: 40px;
            height: 40px;
        }

        .search-player-image {
            width: 40px;
            height: 40px;
        }

        .team-logo-wrapper img {
            height: 70px !important;
            width: 70px !important;
        }

        .close-player-btn {
            top: 5px;
            right: 10px;
            width: 30px;
            height: 30px;
            font-size: 12px;
        }
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .alert {
        border-radius: 8px;
        border: none;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
    }

    .alert-info {
        background: linear-gradient(135deg, #cce8f4, #b8daff);
        color: #004085;
    }
</style>

<?= $this->endSection() ?>