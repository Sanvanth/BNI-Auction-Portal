<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-cogs me-2"></i>Auction & Team Settings 
            <small class="text-muted">(Event ID: <?= session()->get('event_id') ?>)</small>
        </h5>
    </div>
    <div class="card-body">
        <?php if (!session()->get('event_id')): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No event selected. Please select an event to manage settings.
            </div>
        <?php else: ?>
            <form action="<?= base_url('/admin/save-auction-settings') ?>" method="post" class="row g-3" id="auction-settings-form">
                <div class="col-md-4">
                    <label for="max_purse" class="form-label">Maximum Purse Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="max_purse" name="max_purse" value="<?= esc($settings['max_purse'] ?? '') ?>" placeholder="Enter max purse" required>
                </div>
                <div class="col-md-4">
                    <label for="base_price" class="form-label">Base Price per Player (₹)</label>
                    <input type="number" min="0" class="form-control" id="base_price" name="base_price" value="<?= esc($settings['base_price'] ?? '') ?>" placeholder="Enter base price" required>
                </div>
                <div class="col-md-4">
                    <label for="total_players_per_team" class="form-label">Total Players per Team</label>
                    <input type="number" min="0" class="form-control" id="total_players_per_team" name="total_players_per_team" value="<?= esc($settings['total_players_per_team'] ?? '') ?>" placeholder="Enter total players per team" required>
                </div>
                <div class="col-md-4">
                    <label for="hold_amount" class="form-label">Hold Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="hold_amount" name="hold_amount" value="<?= esc($settings['hold_amount'] ?? '') ?>" placeholder="Auto-calculated" readonly>
                </div>
                <div class="col-md-4">
                    <label for="number_of_rounds" class="form-label">Number of Rounds</label>
                    <input type="number" min="0" class="form-control" id="number_of_rounds" name="number_of_rounds" value="<?= esc($settings['number_of_rounds'] ?? '') ?>" placeholder="Enter number of rounds" required>
                </div>
                
                <!-- Rounds Rules Section -->
                <div class="col-12">
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="fas fa-sort-numeric-down me-2"></i>Rounds Configuration</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRoundRule()">
                            <i class="fas fa-plus me-1"></i>Add Round
                        </button>
                    </div>
                    
                    <div id="rounds-rules-container">
                        <?php 
                        $roundsRules = [];
                        if (!empty($settings['rounds_rules'])) {
                            $roundsRules = is_string($settings['rounds_rules']) ? 
                                          json_decode($settings['rounds_rules'], true) : 
                                          $settings['rounds_rules'];
                        }
                        
                        if (empty($roundsRules)) {
                            // Default rounds rules
                            $roundsRules = [
                                ['round' => 1, 'players_count' => 20],
                                ['round' => 2, 'players_count' => 15],
                                ['round' => 3, 'players_count' => 10],
                                ['round' => 4, 'players_count' => 8],
                                ['round' => 5, 'players_count' => 5]
                            ];
                        }
                        ?>
                        
                        <?php foreach ($roundsRules as $index => $rule): ?>
                            <div class="round-rule-row row g-3 mb-3 border p-3 rounded bg-light" data-round-index="<?= $index ?>">
                                <div class="col-md-5">
                                    <label class="form-label">Round Number</label>
                                    <input type="number" min="1" class="form-control" 
                                           name="round_rules[<?= $index ?>][round]" 
                                           value="<?= esc($rule['round'] ?? '') ?>" 
                                           placeholder="e.g., 1" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Players Count</label>
                                    <input type="number" min="1" class="form-control" 
                                           name="round_rules[<?= $index ?>][players_count]" 
                                           value="<?= esc($rule['players_count'] ?? '') ?>" 
                                           placeholder="e.g., 20" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRoundRule(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Hidden input to store JSON -->
                    <input type="hidden" id="rounds_rules" name="rounds_rules" value='<?= esc(json_encode($roundsRules)) ?>'>
                </div>
                
                <!-- Rounds Preview -->
                <div class="col-12">
                    <div class="alert alert-primary">
                        <h6 class="alert-heading"><i class="fas fa-eye me-2"></i>Rounds Preview:</h6>
                        <div id="rounds-preview">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        <hr>
                        <small class="mb-0">
                            <strong>Note:</strong> Ensure round numbers are sequential (1, 2, 3...) and match the total number of rounds.
                        </small>
                    </div>
                </div>
                
                <!-- Bid Increment Rules Section -->
                <div class="col-12">
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Bid Increment Rules</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addIncrementRule()">
                            <i class="fas fa-plus me-1"></i>Add Rule
                        </button>
                    </div>
                    
                    <div id="increment-rules-container">
                        <?php 
                        $bidRules = [];
                        if (!empty($settings['bid_increment_rules'])) {
                            $bidRules = is_string($settings['bid_increment_rules']) ? 
                                       json_decode($settings['bid_increment_rules'], true) : 
                                       $settings['bid_increment_rules'];
                        }
                        
                        if (empty($bidRules)) {
                            // Default rules
                            $bidRules = [
                                ['min_amount' => 0, 'max_amount' => 10000000, 'increment_amount' => 500000],
                                ['min_amount' => 10000001, 'max_amount' => null, 'increment_amount' => 1000000]
                            ];
                        }
                        ?>
                        
                        <?php foreach ($bidRules as $index => $rule): ?>
                            <div class="increment-rule-row row g-3 mb-3 border p-3 rounded bg-light" data-rule-index="<?= $index ?>">
                                <div class="col-md-4">
                                    <label class="form-label">Min Amount (₹)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" 
                                           name="bid_rules[<?= $index ?>][min_amount]" 
                                           value="<?= esc($rule['min_amount'] ?? '') ?>" 
                                           placeholder="e.g., 0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Max Amount (₹)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" 
                                           name="bid_rules[<?= $index ?>][max_amount]" 
                                           value="<?= esc($rule['max_amount'] ?? '') ?>" 
                                           placeholder="Leave empty for unlimited">
                                    <small class="text-muted">Leave empty for no upper limit</small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Increment Amount (₹)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" 
                                           name="bid_rules[<?= $index ?>][increment_amount]" 
                                           value="<?= esc($rule['increment_amount'] ?? '') ?>" 
                                           placeholder="e.g., 500000" required>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeIncrementRule(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Hidden input to store JSON -->
                    <input type="hidden" id="bid_increment_rules" name="bid_increment_rules" value='<?= esc(json_encode($bidRules)) ?>'>
                </div>
                
                <!-- Rules Preview -->
                <div class="col-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Bid Rules Preview:</h6>
                        <div id="rules-preview">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        <hr>
                        <small class="mb-0">
                            <strong>Note:</strong> Rules are applied in sequential order. Make sure there are no gaps between ranges.
                        </small>
                    </div>
                </div>
                
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Settings for Current Event
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Only initialize if event is selected
        if (<?= session()->get('event_id') ? 'true' : 'false' ?>) {
            const basePriceInput = document.getElementById('base_price');
            const totalPlayersInput = document.getElementById('total_players_per_team');
            const holdAmountInput = document.getElementById('hold_amount');
            
            function updateHoldAmount() {
                let bp = parseFloat(basePriceInput.value) || 0;
                let tp = parseInt(totalPlayersInput.value) || 0;
                holdAmountInput.value = (bp * tp).toFixed(2);
            }
            
            basePriceInput.addEventListener('input', updateHoldAmount);
            totalPlayersInput.addEventListener('input', updateHoldAmount);
            
            // Initialize
            updateHoldAmount();
            updateRoundsPreview();
            updateRulesPreview();
            updateHiddenRoundsInput();
            updateHiddenRulesInput();
        }
    });

    let ruleIndex = <?= isset($bidRules) ? count($bidRules) : 0 ?>;
    let roundIndex = <?= isset($roundsRules) ? count($roundsRules) : 0 ?>;

    // Rounds Rules Functions
    function addRoundRule() {
        const container = document.getElementById('rounds-rules-container');
        const newRoundHtml = `
            <div class="round-rule-row row g-3 mb-3 border p-3 rounded bg-light" data-round-index="${roundIndex}">
                <div class="col-md-5">
                    <label class="form-label">Round Number</label>
                    <input type="number" min="1" class="form-control" 
                           name="round_rules[${roundIndex}][round]" placeholder="e.g., 1" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Players Count</label>
                    <input type="number" min="1" class="form-control" 
                           name="round_rules[${roundIndex}][players_count]" placeholder="e.g., 20" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRoundRule(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newRoundHtml);
        roundIndex++;
        
        // Add event listeners to new inputs
        const newRule = container.lastElementChild;
        const inputs = newRule.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                updateRoundsPreview();
                updateHiddenRoundsInput();
            });
        });
        
        updateRoundsPreview();
        updateHiddenRoundsInput();
    }

    function removeRoundRule(button) {
        const ruleRow = button.closest('.round-rule-row');
        const container = document.getElementById('rounds-rules-container');
        
        // Don't allow removing if it's the last rule
        if (container.children.length > 1) {
            ruleRow.remove();
            updateRoundsPreview();
            updateHiddenRoundsInput();
        } else {
            alert('At least one round configuration is required.');
        }
    }

    function updateRoundsPreview() {
        const rounds = collectRoundsData();
        const preview = document.getElementById('rounds-preview');
        let previewHtml = '';
        
        rounds.forEach((round, index) => {
            const roundNumber = parseInt(round.round) || 0;
            const playersCount = parseInt(round.players_count) || 0;
            
            previewHtml += `<small><strong>Round ${roundNumber}:</strong> ${playersCount} players<br></small>`;
        });
        
        preview.innerHTML = previewHtml || '<small><em>No rounds configured</em></small>';
    }

    function collectRoundsData() {
        const rounds = [];
        const roundRows = document.querySelectorAll('.round-rule-row');
        
        roundRows.forEach(row => {
            const roundNumber = row.querySelector('input[name*="[round]"]').value;
            const playersCount = row.querySelector('input[name*="[players_count]"]').value;
            
            if (roundNumber && playersCount) {
                rounds.push({
                    round: parseInt(roundNumber),
                    players_count: parseInt(playersCount)
                });
            }
        });
        
        // Sort by round number
        rounds.sort((a, b) => a.round - b.round);
        
        return rounds;
    }

    function updateHiddenRoundsInput() {
        const rounds = collectRoundsData();
        document.getElementById('rounds_rules').value = JSON.stringify(rounds);
    }

    // Bid Increment Rules Functions
    function addIncrementRule() {
        const container = document.getElementById('increment-rules-container');
        const newRuleHtml = `
            <div class="increment-rule-row row g-3 mb-3 border p-3 rounded bg-light" data-rule-index="${ruleIndex}">
                <div class="col-md-4">
                    <label class="form-label">Min Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" 
                           name="bid_rules[${ruleIndex}][min_amount]" placeholder="e.g., 0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" 
                           name="bid_rules[${ruleIndex}][max_amount]" placeholder="Leave empty for unlimited">
                    <small class="text-muted">Leave empty for no upper limit</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Increment Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control" 
                           name="bid_rules[${ruleIndex}][increment_amount]" placeholder="e.g., 500000" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeIncrementRule(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newRuleHtml);
        ruleIndex++;
        
        // Add event listeners to new inputs
        const newRule = container.lastElementChild;
        const inputs = newRule.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                updateRulesPreview();
                updateHiddenRulesInput();
            });
        });
        
        updateRulesPreview();
        updateHiddenRulesInput();
    }

    function removeIncrementRule(button) {
        const ruleRow = button.closest('.increment-rule-row');
        const container = document.getElementById('increment-rules-container');
        
        // Don't allow removing if it's the last rule
        if (container.children.length > 1) {
            ruleRow.remove();
            updateRulesPreview();
            updateHiddenRulesInput();
        } else {
            alert('At least one bid increment rule is required.');
        }
    }

    function updateRulesPreview() {
        const rules = collectRulesData();
        const preview = document.getElementById('rules-preview');
        let previewHtml = '';
        
        rules.forEach((rule, index) => {
            const minAmount = parseFloat(rule.min_amount) || 0;
            const maxAmount = rule.max_amount ? parseFloat(rule.max_amount) : null;
            const incrementAmount = parseFloat(rule.increment_amount) || 0;
            
            const minFormatted = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 0
            }).format(minAmount);
            
            const maxFormatted = maxAmount ? 
                new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'INR',
                    maximumFractionDigits: 0
                }).format(maxAmount) : 'No Limit';
            
            const incrementFormatted = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 0
            }).format(incrementAmount);
            
            previewHtml += `<small><strong>Rule ${index + 1}:</strong> ${minFormatted} - ${maxFormatted}: Increment by ${incrementFormatted}<br></small>`;
        });
        
        preview.innerHTML = previewHtml || '<small><em>No rules defined</em></small>';
    }

    function collectRulesData() {
        const rules = [];
        const ruleRows = document.querySelectorAll('.increment-rule-row');
        
        ruleRows.forEach(row => {
            const minAmount = row.querySelector('input[name*="[min_amount]"]').value;
            const maxAmount = row.querySelector('input[name*="[max_amount]"]').value;
            const incrementAmount = row.querySelector('input[name*="[increment_amount]"]').value;
            
            if (minAmount && incrementAmount) {
                rules.push({
                    min_amount: parseFloat(minAmount),
                    max_amount: maxAmount ? parseFloat(maxAmount) : null,
                    increment_amount: parseFloat(incrementAmount)
                });
            }
        });
        
        return rules;
    }

    function updateHiddenRulesInput() {
        const rules = collectRulesData();
        document.getElementById('bid_increment_rules').value = JSON.stringify(rules);
    }

    // Add event listeners to existing inputs
    document.addEventListener('DOMContentLoaded', function() {
        if (<?= session()->get('event_id') ? 'true' : 'false' ?>) {
            // Bid increment rules inputs
            const ruleInputs = document.querySelectorAll('.increment-rule-row input');
            ruleInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateRulesPreview();
                    updateHiddenRulesInput();
                });
            });

            // Round rules inputs
            const roundInputs = document.querySelectorAll('.round-rule-row input');
            roundInputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateRoundsPreview();
                    updateHiddenRoundsInput();
                });
            });
        }
    });

    // Form submission handler
    document.getElementById('auction-settings-form')?.addEventListener('submit', function(e) {
        updateHiddenRoundsInput();
        updateHiddenRulesInput();
    });
</script>
<?= $this->endSection() ?>