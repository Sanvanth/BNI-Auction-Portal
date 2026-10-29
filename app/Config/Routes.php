<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ========== AUTHENTICATION ROUTES ==========
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');

// ========== PUBLIC ROUTES (NO AUTHENTICATION REQUIRED) ==========
// Public teams details with encoded event ID
$routes->get('/public/teams/(:any)', 'Team::publicTeamsDetails/$1');
$routes->get('/teams/(:any)', 'Team::publicTeamsDetails/$1'); // Alternative shorter route

// ========== PUBLIC AUCTION DISPLAY ==========
// User-facing auction display (no auth filter)
$routes->get('/auction', 'Auction::userView');
$routes->get('/auction/user', 'Auction::userView');
$routes->get('/auction/display', 'Auction::userView');
$routes->get('/auction/teams-details', 'Team::teamsDetails');

// Public API endpoints for user display
$routes->get('/auction/getSelectedPlayer', 'Auction::getSelectedPlayer');
$routes->get('/auction/getCompletedAuctionsCount', 'Auction::getCompletedAuctionsCount');

// ========== TEAM ROUTES (Team Authentication Required) ==========
$routes->group('team', ['filter' => 'auth'], function ($routes) {
    // Team dashboard/account page
    $routes->get('account', 'Team::account');
    $routes->get('dashboard', 'Team::account'); // Alternative route
    $routes->get('/', 'Team::account'); // Default team route
    
    // Team API endpoints - FIXED: Removed extra slashes
    $routes->get('getTeamData', 'Team::getTeamData');
    $routes->get('getTeamPlayers', 'Team::getTeamPlayers');
    $routes->get('getAvailablePlayersForTeam', 'Team::getAvailablePlayersForTeam');
    $routes->get('getUnsoldPlayersForTeam', 'Team::getUnsoldPlayersForTeam');
    
    // Debugging route (temporary)
    $routes->get('debug', 'Team::debugSession');
});

// ========== SUPERADMIN ROUTES ==========
$routes->group('superadmin', ['filter' => 'auth'], function ($routes) {
    // ========== MAIN DASHBOARD ==========
    $routes->get('dashboard', 'Superadmin::dashboard');
    $routes->get('/', 'Superadmin::dashboard');
    
    // Dashboard API endpoints
    $routes->get('api/quick-stats', 'Superadmin::getQuickStats');
    $routes->get('api/recent-activity', 'Superadmin::getRecentActivity');
    $routes->get('api/system-health', 'Superadmin::getSystemHealth');

    // ========== EVENT MANAGEMENT (Event Controller) ==========
    $routes->get('events', 'Event::index');
    $routes->get('create-event', 'Event::create');
    $routes->post('store-event', 'Event::store');
    $routes->get('edit-event/(:num)', 'Event::edit/$1');
    $routes->post('update-event/(:num)', 'Event::update/$1');
    $routes->delete('delete-event/(:num)', 'Event::delete/$1');
    $routes->get('events/list', 'Event::getList');  // For AJAX DataTable
    $routes->get('get-event-data/(:num)', 'Event::getEventData/$1');  // For edit modal
    $routes->get('get-event/(:num)', 'Event::get/$1');

    // ========== ADMIN MANAGEMENT (SAdmin Controller) ==========
    $routes->get('admins', 'SAdmin::index');
    $routes->get('api/admins-list', 'SAdmin::getList');

    // Admin CRUD
    $routes->get('add-admin', 'SAdmin::create');
    $routes->post('store-admin', 'SAdmin::store');
    $routes->get('create-admin', 'SAdmin::create'); // Alternative route
    $routes->post('create-admin', 'SAdmin::store'); // Alternative route
    $routes->get('edit-admin/(:num)', 'SAdmin::get/$1');
    $routes->post('update-admin/(:num)', 'SAdmin::update/$1');
    $routes->get('get-admin/(:num)', 'SAdmin::get/$1');
    $routes->delete('delete-admin/(:num)', 'SAdmin::delete/$1');
    $routes->post('toggle-admin-status/(:num)', 'SAdmin::toggleStatus/$1');
    $routes->post('reset-admin-password/(:num)', 'SAdmin::resetPassword/$1');

    // ========== SUPERADMIN MANAGEMENT (SsuperAdmin Controller) ==========
    $routes->get('superadmins', 'SsuperAdmin::index');
    $routes->get('api/superadmins-list', 'SsuperAdmin::getList');

    // Superadmin CRUD
    $routes->get('add-superadmin', 'SsuperAdmin::create');
    $routes->post('store-superadmin', 'SsuperAdmin::store');
    $routes->get('create-superadmin', 'SsuperAdmin::create'); // Alternative route
    $routes->post('create-superadmin', 'SsuperAdmin::store'); // Alternative route
    $routes->get('get-superadmin/(:num)', 'SsuperAdmin::get/$1');
    $routes->post('update-superadmin/(:num)', 'SsuperAdmin::update/$1');
    $routes->delete('delete-superadmin/(:num)', 'SsuperAdmin::delete/$1');
    $routes->post('toggle-superadmin-status/(:num)', 'SsuperAdmin::toggleStatus/$1');
    $routes->post('reset-superadmin-password/(:num)', 'SsuperAdmin::resetPassword/$1');
    $routes->get('/superadmin/superadmins/inactive', 'SsuperAdmin::inactive');
    $routes->post('/superadmin/activate-superadmin/(:num)', 'SsuperAdmin::activate/$1');
});

// ========== ADMIN DASHBOARD ==========
$routes->get('/dashboard', 'Admin::index', ['filter' => 'auth']);
$routes->get('/admin/export-team-composition/(:segment)', 'Admin::exportTeamComposition/$1', ['filter' => 'auth']);

// ========== ADMIN SETTINGS (Outside of grouped routes) ==========
$routes->get('/admin/settings', 'Admin::settings', ['filter' => 'auth']);
$routes->post('/admin/save-auction-settings', 'Admin::saveAuctionSettings', ['filter' => 'auth']);
$routes->get('/admin/get-auction-settings', 'Admin::getAuctionSettings', ['filter' => 'auth']);
$routes->get('/admin/get-bid-increment', 'Admin::getBidIncrement', ['filter' => 'auth']);

// ========== ADMIN ROUTES (Grouped) ==========
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    // ==== Players ====
    $routes->get('players', 'Player::index');
    $routes->post('players/store', 'Player::store');
    $routes->get('players/edit/(:num)', 'Player::edit/$1');
    $routes->post('players/update/(:num)', 'Player::update/$1');
    $routes->get('players/delete/(:num)', 'Player::delete/$1');
    $routes->get('players/restore/(:num)', 'Player::restore/$1');
    $routes->get('players/export/(:alpha)', 'Player::export/$1');
    $routes->post('players/updateCurrentBidTeam', 'Player::updateCurrentBidTeam');

    // ==== Teams ====
    $routes->get('teams', 'Team::index');
    $routes->post('teams/store', 'Team::store');
    $routes->get('teams/edit/(:num)', 'Team::edit/$1');
    $routes->post('teams/update/(:num)', 'Team::update/$1');
    $routes->get('teams/delete/(:num)', 'Team::delete/$1');
    $routes->get('teams/restore/(:num)', 'Team::restore/$1');
    $routes->get('teams/export/(:alpha)', 'Team::export/$1');
    $routes->get('teams/getAvailablePlayers/(:num)', 'Team::getAvailablePlayers/$1');
    $routes->post('teams/recalculateTeamFinances', 'Team::recalculateTeamFinances');

    // ==== Leaderboard ====
    $routes->get('leaderboard', 'Leaderboard::index');
    $routes->get('leaderboard/export/(:alpha)', 'Leaderboard::export/$1');

    // ==== Bid Logs ====
    $routes->get('bid_logs', 'BidLog::index');
    $routes->get('bid_logs/export/(:alpha)', 'BidLog::export/$1');
    $routes->get('bid_logs/realtime-data', 'BidLog::getRealtimeData');
    $routes->get('bid_logs/filter', 'BidLog::filterBids');

    // ==== Team Composition ====
    $routes->get('team_composition', 'TeamComposition::index');
    $routes->get('team_composition/export/(:alpha)', 'TeamComposition::export/$1');

    // ==== Live Auction Admin Page ====
    $routes->get('auction', 'Auction::index');

    // Player Search and Details
    $routes->get('auction/searchPlayers', 'Auction::searchPlayers');
    $routes->get('auction/getPlayerDetails/(:num)', 'Auction::getPlayerDetails/$1');

    // Player Selection for Live Display
    $routes->post('auction/selectPlayer', 'Auction::selectPlayer');

    // Auction Actions
    $routes->post('auction/markAsSold', 'Auction::markAsSold');
    $routes->post('auction/markAsUnsold', 'Auction::markAsUnsold');
    $routes->post('auction/undoLastAction', 'Auction::undoLastAction');
    $routes->post('auction/undoAuctionLastAction', 'Auction::undoAuctionLastAction');

    // Validation and Data
    $routes->post('auction/validateTeamPurse', 'Auction::validateTeamPurse');
    $routes->get('auction/refreshTeamData', 'Auction::refreshTeamData');

    // Statistics and History
    $routes->get('auction/getAuctionStats', 'Auction::getAuctionStats');
    $routes->get('auction/getHistory', 'Auction::getHistory');

    // Debug/Helper Routes
    $routes->get('auction/checkPlayerUpdate/(:num)', 'Auction::checkPlayerUpdate/$1');
    $routes->get('auction/checkBidRecords/(:num)', 'Auction::checkBidRecords/$1');
    $routes->get('auction/checkBidRecords', 'Auction::checkBidRecords');

    // ==== Settings API Routes (Within Admin Group) ====
    $routes->get('settings/auction-settings', 'Admin::getAuctionSettings');
    $routes->get('settings/bid-increment', 'Admin::getBidIncrement');
});

// ========== PUBLIC API ROUTES FOR AUCTION SETTINGS ==========
// Public routes for auction settings (if needed by frontend without auth)
$routes->get('api/auction/settings', 'Admin::getAuctionSettings');
$routes->get('api/auction/get-bid-increment', 'Admin::getBidIncrement');

// ========== MOBILE APP API's ==========
$routes->post('mobileapp/login', 'MobileApp::login');
$routes->post('mobileapp/logout', 'MobileApp::logout');
$routes->get('mobileapp/teamsdetails', 'MobileApp::teamsDetails');
$routes->get('mobileapp/auctionplayerdetails', 'MobileApp::teamsDetails');
$routes->get('mobileapp/getSelectedPlayer', 'MobileApp::getSelectedPlayer');
$routes->get('mobileapp/getCompletedAuctionsCount', 'MobileApp::getCompletedAuctionsCount');
$routes->post('mobileapp/markAsSold', 'MobileApp::markAsSold');
$routes->post('mobileapp/markAsUnsold', 'MobileApp::markAsUnsold');
$routes->get('mobileapp/undoLastAction', 'MobileApp::undoLastAction');
$routes->get('mobileapp/undoAuctionLastAction', 'MobileApp::undoAuctionLastAction');
$routes->post('mobileapp/updateCurrentBidTeam', 'MobileApp::updateCurrentBidTeam');
$routes->get('mobileapp/getAuctionSettings', 'MobileApp::getAuctionSettings');
$routes->post('mobileapp/validateTeamPurse', 'MobileApp::validateTeamPurse');
$routes->get('mobileapp/getTeamPlayerDetails/(:num)', 'MobileApp::getTeamPlayerDetails/$1');
$routes->post('mobileapp/unsoldAvailablePlayers', 'MobileApp::unsoldAvailablePlayers');