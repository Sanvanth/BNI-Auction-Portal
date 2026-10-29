<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

use App\Models\PlayerModel;
use App\Models\TeamModel;
use App\Models\BidModel;

class MobileApp extends Controller
{
    protected $db;
    private $jwtKey = "renianobsanvantyhoovishnujanahitt";
    protected $basePrice = 500000;
    

    
    

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // --- JWT Encode ---
    private function generateJWT($payload, $key, $algo = 'HS256')
    {
        $header = ['typ' => 'JWT', 'alg' => $algo];

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);
        $signature = $this->sign($signingInput, $key, $algo);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function sign($input, $key, $algo)
    {
        $algorithms = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];
        return hash_hmac($algorithms[$algo], $input, $key, true);
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    // --- JWT Decode ---
    private function decodeJWT($jwt, $key, $algo = 'HS256')
    {
        $tokens = explode('.', $jwt);
        if (count($tokens) !== 3) {
            return false;
        }

        list($headb64, $bodyb64, $cryptob64) = $tokens;
        $sig = $this->base64UrlDecode($cryptob64);
        $validSig = $this->sign("$headb64.$bodyb64", $key, $algo);

        if (!hash_equals($validSig, $sig)) {
            return false;
        }

        $payload = json_decode($this->base64UrlDecode($bodyb64), true);

        if (isset($payload['exp']) && time() >= $payload['exp']) {
            return false; // expired
        }

        return $payload;
    }

    // --- Verify JWT Middleware ---
    private function verifyToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setStatusCode(401)
                                  ->setJSON(['error' => 'Authorization header missing or invalid']);
        }

        $jwt = $matches[1];
        $payload = $this->decodeJWT($jwt, $this->jwtKey);

        if (!$payload) {
            return $this->response->setStatusCode(401)
                                  ->setJSON(['error' => 'Invalid or expired token']);
        }

        return $payload; // return user data
    }

    // --- Controller Methods ---
    public function login()
    {
        $json = $this->request->getJSON(true);
        $username = $json['username'] ?? null;
        $password = $json['password'] ?? null;

        if (!$username || !$password) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(['error' => 'Username and password required']);
        }

        $db = Database::connect();
        $builder = $db->table('users');
        $user = $builder->where('username', $username)
                        ->where('password_hash', $password)
                        ->where('is_active', true)
                        ->get()
                        ->getRowArray();
                        
                        //  if ($user && password_verify($password, $user['password_hash'])) {

         if ($user) {
            $payload = [
                'iss' => "auction",
                'iat' => time(),
                'exp' => time() + 86400,
                'data' => [
                    'userid' => $user['user_id'],
                    'username' => $user['username'],
                    'role'=> $user['role']
                ]
            ];

            $token = $this->generateJWT($payload, $this->jwtKey);

            return $this->response->setJSON([
                'message' => 'Login successful',
                'token'   => $token,
                'userid' => $user['user_id'],
                'username' => $user['username'],
                'role'=> $user['role'],
                'team'=> $user['team_id']
            ]);
        }

        return $this->response->setStatusCode(401)
                              ->setJSON(['error' => 'Invalid credentials']);
    }

    public function logout()
    {
        return $this->response->setStatusCode(200)
                              ->setJSON(['message' => 'Logged out']);
    }

    public function teamsDetails()
    {
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }

        $sql = "
            SELECT 
                t.team_id,
                t.team_name,
                t.team_logo,
                t.is_active,
                t.purse,
                t.hold_amount,
                t.max_purse,
                t.created_at,
                COUNT(p.player_id) AS player_count
            FROM teams t
            LEFT JOIN players p ON p.sold_to = t.team_id AND p.is_active = 1
            WHERE t.is_active = 1
            GROUP BY t.team_id, t.team_name, t.team_logo, t.is_active, t.purse, t.hold_amount, t.max_purse, t.created_at
            ORDER BY t.team_name
        ";

        $teams = $this->db->query($sql)->getResultArray();
        
        $baseUrl = base_url('uploads/teams'); 
            foreach ($teams as &$team) {
                if (!empty($team['team_logo'])) {
                    $team['team_logo'] = $baseUrl . '/' . $team['team_logo'];
                }
            }

        return $this->response->setStatusCode(200)->setJSON(
            $teams
        );
    }
    
    public function getSelectedPlayer()
    {
        
            $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload;
        }
        $sql = "
            SELECT p.player_id, p.player_name, p.player_role, p.batting_type, p.bowling_type,
                   p.base_price, p.profile_pic, p.company_name, p.chapter_name, p.auction_status,
                   p.final_price, p.sold_to, p.phone_number, p.current_price, p.business_category,
                   t.team_name, t.team_logo, current_t.team_logo
            FROM players p
            LEFT JOIN teams t         ON p.sold_to     = t.team_id
            LEFT JOIN teams current_t ON p.current_team = current_t.team_id
            WHERE p.is_selected = 1 AND p.is_active = 1
            LIMIT 1
        ";

        $player = $this->db->query($sql)->getRowArray();
        

        if (!$player) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'No player currently selected']);
        }
        
        $player['profile_pic'] = base_url('uploads/players/' . $player['profile_pic']);
        $player['team_logo'] = $player['team_logo'] != null ? base_url('uploads/teams/' . $player['team_logo']) : null;

        return $this->response->setStatusCode(200)->setJSON(
            $player
        );
    }
    
    public function getCompletedAuctionsCount()
    {   
        
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        
        $result = $this->db->query("
            SELECT COUNT(*) AS completed_count
            FROM players
            WHERE auction_status IN ('sold','unsold') AND is_active = 1 AND is_cap = '0'
        ")->getRow();

        return $this->response->setStatusCode(200)->setJSON([
            'success'         => true,
            'completed_count' => (int) $result->completed_count,
        ]);
    }
    
    private function validateTeamPurse($teamId, $finalPrice)
{
    if (!$teamId || $finalPrice <= 0) {
        return ['valid' => false, 'message' => 'Invalid team or price'];
    }

    $team = $this->db->query("
        SELECT team_name, purse, hold_amount
        FROM teams
        WHERE team_id = ? AND is_active = 1
    ", [$teamId])->getRowArray();

    if (!$team) {
        return ['valid' => false, 'message' => 'Team not found'];
    }

    $availableFromHold = min($this->basePrice, $team['hold_amount']); // dynamic
    $valid             = $finalPrice <= ($team['purse'] + $availableFromHold);

    return [
        'valid'           => $valid,
        'message'         => $valid
            ? 'Valid'
            : 'Insufficient purse. Available: ₹' . number_format($team['purse']),
        'available_purse' => $team['purse'],
        'team_name'       => $team['team_name'],
        'team_data'       => $team,
    ];
}

public function markAsSold()
{
    $payload = $this->verifyToken();
    if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
        return $payload; 
    }

    $json       = $this->request->getJSON(true);
    $playerId   = $json['player_id'] ?? null;
    $teamId     = $json['team_id'] ?? null;
    $finalPrice = (float) ($json['final_price'] ?? 0);

    if (!$playerId || !$teamId || $finalPrice <= 0) {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Invalid data provided'
        ]);
    }

    // ✅ fixed call
    $checkStat = $this->validateTeamPurse($teamId, $finalPrice);
    if (!$checkStat['valid']) {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => $checkStat['message']
        ]);
    }

    $this->db->transBegin();

    try {
        $player = $this->db->query("
            SELECT player_id, player_name, base_price
            FROM players
            WHERE player_id = ? AND is_active = 1
              AND auction_status IN ('available','unsold')
        ", [$playerId])->getRowArray();

        if (!$player) {
            throw new \Exception('Player not found or not available for auction');
        }

        if ($finalPrice < $player['base_price']) {
            throw new \Exception(
                'Final price cannot be less than base price of ₹' . number_format($player['base_price'])
            );
        }

        $team = $checkStat['team_data'];

        // Dynamic hold deduction
        $fromHold  = min($this->basePrice, $team['hold_amount'], $finalPrice);
        $fromPurse = $finalPrice - $fromHold;

        if ($fromPurse > $team['purse']) {
            throw new \Exception(
                'Insufficient team purse. Available: ₹' . number_format($team['purse']) .
                ', Needed: ₹' . number_format($fromPurse)
            );
        }

        $newPurse = $team['purse'] - $fromPurse;
        $newHold  = $team['hold_amount'] - $fromHold;

        // Update player
        $this->db->query("
            UPDATE players
            SET sold_to = ?, final_price = ?, auction_status = 'sold', is_unsold = 0
            WHERE player_id = ?
        ", [$teamId, $finalPrice, $playerId]);

        // Update team
        $this->db->query("
            UPDATE teams
            SET purse = ?, hold_amount = ?
            WHERE team_id = ?
        ", [$newPurse, $newHold, $teamId]);

        // Bid record
        $this->db->query("
            INSERT INTO bids (player_id, team_id, bid_amount, created_at)
            VALUES (?, ?, ?, NOW())
        ", [$playerId, $teamId, $finalPrice]);

        // Logs
        $this->db->table('auction_logs')->where('player_id', $playerId)->update(['is_active' => 0]);
        $this->db->table('auction_logs')->insert([
            'player_id'          => $playerId,
            'team_id'            => $teamId,
            'current_bid_amount' => $finalPrice,
            'is_active'          => 1,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->db->transCommit();

        return $this->response->setStatusCode(200)->setJSON([
            'success'          => true,
            'message'          => $player['player_name'] . ' sold to ' . $team['team_name'] .
                                  ' for ₹' . number_format($finalPrice),
            'player_name'      => $player['player_name'],
            'team_name'        => $team['team_name'],
            'team_id'          => $teamId,
            'final_price'      => $finalPrice,
            'from_hold'        => $fromHold,
            'from_purse'       => $fromPurse,
            'new_purse'        => $newPurse,
            'new_hold_amount'  => $newHold,
        ]);
    } catch (\Exception $e) {
        $this->db->transRollback();
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

    
    public function markAsUnsold()
    {   
        
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }

        $json = $this->request->getJSON(true);
        $playerId = $json['player_id'] ?? null;

        if (!$playerId) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Player ID is required']);
        }

        $this->db->transBegin();

        try {
            $player = $this->db->query("
                SELECT player_id, player_name
                FROM players
                WHERE player_id = ? AND is_active = 1 AND auction_status IN ('available','unsold')
            ", [$playerId])->getRowArray();

            if (!$player) {
                throw new \Exception('Player not found or not available for auction');
            }

            $this->db->query("
                UPDATE players
                SET auction_status = 'unsold', is_unsold = 1, sold_to = NULL,
                    final_price = NULL, current_team = 0, current_price = 0
                WHERE player_id = ?
            ", [$playerId]);

            $this->db->query("
                INSERT INTO bids (player_id, team_id, bid_amount, created_at)
                VALUES (?, NULL, 0, NOW())
            ", [$playerId]);

            $this->db->table('auction_logs')->where('player_id', $playerId)->update(['is_active' => 0]);
            $this->db->table('auction_logs')->insert([
                'player_id'          => $playerId,
                'team_id'            => null,
                'current_bid_amount' => 0,
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

            $this->db->transCommit();

            return $this->response->setStatusCode(200)->setJSON([
                'success'     => true,
                'message'     => $player['player_name'] . ' marked as unsold',
                'player_name' => $player['player_name'],
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    
    public function undoLastAction()
    {
        
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        
        $this->db->transBegin();

        try {
            $lastAction = $this->db->query("
                SELECT b.bid_id, b.player_id, b.team_id, b.bid_amount, b.created_at,
                       p.player_name, t.team_name, t.purse, t.hold_amount
                FROM bids b
                INNER JOIN players p ON b.player_id = p.player_id
                LEFT JOIN teams t   ON b.team_id  = t.team_id
                ORDER BY b.created_at DESC, b.bid_id DESC
                LIMIT 1
            ")->getRowArray();

            if (!$lastAction) {
                throw new \Exception('No auction actions found to undo');
            }

            $message = '';

            if ($lastAction['team_id'] !== null) {
                // Undo sold
                $this->db->query("
                    UPDATE players
                    SET sold_to = NULL, final_price = NULL,
                        auction_status = 'available', is_unsold = 0
                    WHERE player_id = ?
                ", [$lastAction['player_id']]);

                $fromHold  = min($this->basePrice, $lastAction['bid_amount']);       // dynamic
                $fromPurse = $lastAction['bid_amount'] - $fromHold;

                $newPurse = $lastAction['purse']       + $fromPurse;
                $newHold  = $lastAction['hold_amount'] + $fromHold;

                $this->db->query("
                    UPDATE teams
                    SET purse = ?, hold_amount = ?
                    WHERE team_id = ?
                ", [$newPurse, $newHold, $lastAction['team_id']]);

                $message = $lastAction['player_name'] . ' returned from ' . $lastAction['team_name'] .
                           ', ₹' . number_format($fromPurse) . ' restored to purse, ₹' .
                           number_format($fromHold) . ' to hold';
            } else {
                // Undo unsold
                $this->db->query("
                    UPDATE players
                    SET auction_status = 'available', is_unsold = 0,
                        sold_to = NULL, final_price = NULL
                    WHERE player_id = ?
                ", [$lastAction['player_id']]);

                $message = $lastAction['player_name'] . ' made available for auction again';
            }

            // Delete bid record
            $this->db->query("DELETE FROM bids WHERE bid_id = ?", [$lastAction['bid_id']]);

            $this->db->transCommit();

            return $this->response->setStatusCode(200)->setJSON([
                'success'     => true,
                'message'     => 'Action undone: ' . $message,
                'player_name' => $lastAction['player_name'],
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function undoAuctionLastAction()
    {
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        
        
        
        $db       = \Config\Database::connect();
        $builder  = $db->table('auction_logs');
        $lastLog  = $builder->orderBy('auction_log_id', 'DESC')->get(1)->getRow();
        
        
        if (!$lastLog) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No log entry found to delete']);
        }

        $playerId = $lastLog->player_id;
        $builder->where('auction_log_id', $lastLog->auction_log_id)->delete();

        $latestLog = $builder
            ->where('player_id', $playerId)
            ->orderBy('auction_log_id', 'DESC')
            ->get(1)
            ->getRow();

        $playerModel = new \App\Models\PlayerModel();

        if ($latestLog) {
            $playerModel->update($playerId, [
                'current_team'  => $latestLog->team_id,
                'current_price' => $latestLog->current_bid_amount,
            ]);
        } else {
            $playerModel->update($playerId, [
                'current_team'  => null,
                'current_price' => 0,
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON(['success' => true]);
    }
    
    
    public function updateCurrentBidTeam()
    {
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        
        $json = $this->request->getBody();
        $request = json_decode($json, true);
        
        

        
        if (!$request || !isset($request['player_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Missing player ID']);
        }

        if (!is_numeric($request['player_id']) || !is_numeric($request['team_id'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid IDs']);
        }

        $this->db->transBegin();

        try {
            $playerModel = new \App\Models\PlayerModel();

            $data = [
                'current_team' => $request['team_id'],
                'current_price' => $request['final_price'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $playerModel->update($request['player_id'], $data);

            if ($updated) {
                // Deactivate previous auction logs for this player
                $this->db->table('auction_logs')
                    ->where('player_id', $request['player_id'])
                    ->update(['is_active' => 0]);

                // Insert new auction log
                $logData = [
                    'player_id' => $request['player_id'],
                    'team_id' => $request['team_id'],
                    'is_active' => 1,
                    'current_bid_amount' => $request['final_price'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->db->table('auction_logs')->insert($logData);
            }

            $this->db->transCommit();
            return $this->response->setStatusCode(200)->setJSON(['success' => $updated]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function refreshTeamData()
    {
        
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        
        $teams = $this->db->query("
            SELECT team_id, team_name, team_logo, purse
            FROM teams
            WHERE is_active = 1
            ORDER BY team_name ASC
        ")->getResultArray();
        
        $baseUrl = base_url('uploads/teams'); 
            foreach ($teams as &$team) {
                if (!empty($team['team_logo'])) {
                    $team['team_logo'] = $baseUrl . '/' . $team['team_logo'];
                }
            }

        return $this->response->setStatusCode(200)->setJSON($teams);
    }
    
    public function getAuctionSettings()
    {
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        
        $db = \Config\Database::connect();
        $settings = $db->table('auction_settings')->getWhere(['id' => 1])->getRowArray();
        
        if (!$settings) {
            $settings = [
                'max_purse' => 100000000.00,
                'base_price' => 2000000.00,
                'total_players_per_team' => 11,
                'hold_amount' => 22000000.00,
                'number_of_rounds' => 5,
                'players_per_round' => 20,
                'bid_increment_rules' => '[{"min_amount":0,"max_amount":10000000,"increment_amount":500000},{"min_amount":10000001,"max_amount":null,"increment_amount":1000000}]'
            ];
        }
        
        return $this->response->setJSON($settings);
    }
    
   public function getTeamPlayerDetails($teamid)
    {
        $payload = $this->verifyToken();
        if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $payload; 
        }
        

        if($teamid != null || $teamid != "" || $teamid != 0){
            $sql = "
            SELECT B.team_id, B.team_name, B.team_logo, B.purse, B.max_purse,
                   A.chapter_name, A.business_category, A.company_name, A.phone_number,
                   A.player_name, A.batting_type, A.bowling_type, A.player_role, A.player_id,
                   A.final_price, A.profile_pic,B.owner_player_id,B.captain_player_id
            FROM teams AS B
            LEFT JOIN players AS A ON A.sold_to = B.team_id
            WHERE B.is_active = 1 AND B.team_id = ?
            ORDER BY B.team_id, A.player_id
        ";
         $rows = $this->db->query($sql, [$teamid])->getResultArray();
        }else{
            $sql = "
            SELECT B.team_id, B.team_name, B.team_logo, B.purse, B.max_purse,A.chapter_name,A.business_category,A.company_name,A.phone_number,
                  A.player_name, A.batting_type, A.bowling_type, A.player_role, A.player_id,
                   A.final_price, A.profile_pic
            FROM teams AS B
            LEFT JOIN players AS A ON A.sold_to = B.team_id
            WHERE B.is_active = 1
            ORDER BY B.team_id, A.player_id
        ";
          $rows = $this->db->query($sql)->getResultArray();
        }
    
        

    
        // Group by team
        $grouped = [];
        foreach ($rows as $row) {
            $teamId = $row['team_id'];
    
            if (!isset($grouped[$teamId])) {
                $grouped[$teamId] = [
                    'team_id'     => $row['team_id'],
                    'team_name'   => $row['team_name'],
                    'team_logo'   => base_url('uploads/teams/' . $row['team_logo']),
                    'purse'       => $row['purse'],
                    'max_purse'   => $row['max_purse'],
                    'players'     => []
                ];
            }
    
            if (!empty($row['player_id'])) {
                $grouped[$teamId]['players'][] = [
                    'player_id'  => $row['player_id'],
                    'player_name'  => $row['player_name'],
                    'player_role'  => $row['player_role'],
                    'batting_type' => $row['batting_type'],
                    'bowling_type' => $row['bowling_type'],
                    'final_price'  => $row['final_price'],
                    'profile_pic'  => base_url('uploads/players/' . $row['profile_pic']),
                    'chapter_name' => $row['chapter_name'],
                    'business_category' => $row['business_category'],
                    'company_name' => $row['company_name'],
                    'phone_number' => $row['phone_number'],
                    'is_captain'       => ($row['captain_player_id'] == $row['player_id']) ? true : false,
                    'is_owner'         => ($row['owner_player_id'] == $row['player_id']) ? true : false
                    
                ];
            }
        }
    
        return $this->response->setStatusCode(200)->setJSON(array_values($grouped));
    }
    
    
    public function unsoldAvailablePlayers()
{
    $payload = $this->verifyToken();
    if ($payload instanceof \CodeIgniter\HTTP\ResponseInterface) {
        return $payload;
    }

    $json = $this->request->getBody();
    $request = json_decode($json, true);

    $status = $request['status'] ?? null;

    if (!in_array($status, ['unsold', 'available'])) {
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Invalid status provided'
        ]);
    }

    $sql = "SELECT p.player_id, p.player_name, p.player_role, p.batting_type, p.bowling_type,
                   p.base_price, p.profile_pic, p.company_name, p.chapter_name, p.auction_status,
                   p.final_price, p.sold_to, p.phone_number, p.current_price, p.business_category
            FROM players p
            WHERE p.is_active = 1 AND p.auction_status = ?";

    $players = $this->db->query($sql, [$status])->getResultArray();

    if (empty($players)) {
        return $this->response->setStatusCode(404)->setJSON([
            'success' => false,
            'message' => 'No players available'
        ]);
    }

    $baseUrl = base_url('uploads/players');
    foreach ($players as &$p) {
        if (!empty($p['profile_pic'])) {
            $p['profile_pic'] = $baseUrl . '/' . $p['profile_pic'];
        }
    }

    return $this->response->setStatusCode(200)->setJSON([
        'success' => true,
        'players' => $players
    ]);
}


}
