<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BidLog extends Controller
{
    protected $db;
    protected $eventId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        
        // Get event_id from session
        $session = session();
        $this->eventId = $session->get('event_id');
        
        // Check if user is logged in and has event_id
        if (!$session->get('logged_in') || !$this->eventId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied or invalid session');
        }
    }

    public function index()
    {
        // Get bid logs with comprehensive data filtered by event_id
        $bidLogs = $this->getBidLogs();
        
        // Get teams for filter dropdown filtered by event_id
        $teams = $this->getTeams();
        
        // Get statistics filtered by event_id
        $statistics = $this->getBidStatistics();

        return view('admin/bid_logs', [
            'bid_logs' => $bidLogs,
            'teams' => $teams,
            'statistics' => $statistics,
            'activeMenu' => 'bid_logs'
        ]);
    }

    private function getBidLogs()
    {
        $sql = "
            SELECT 
                b.bid_id,
                b.player_id,
                b.team_id,
                b.bid_amount,
                b.created_at,
                p.player_name,
                p.player_role,
                p.company_name,
                p.chapter_name,
                p.auction_status,
                p.is_unsold,
                t.team_name,
                t.team_logo,
                CASE 
                    WHEN p.auction_status = 'sold' THEN 1
                    ELSE 0
                END as is_sold
            FROM bids b
            LEFT JOIN players p ON b.player_id = p.player_id
            LEFT JOIN teams t ON b.team_id = t.team_id
            WHERE (b.bid_amount > 0 OR (b.bid_amount = 0 AND b.team_id IS NULL))
            AND b.event_id = ?
            ORDER BY b.created_at DESC
        ";

        return $this->db->query($sql, [$this->eventId])->getResultArray();
    }

    private function getTeams()
    {
        $sql = "SELECT team_id, team_name FROM teams WHERE is_active = 1 AND event_id = ? ORDER BY team_name ASC";
        return $this->db->query($sql, [$this->eventId])->getResultArray();
    }

    private function getBidStatistics()
    {
        // Total bids filtered by event_id
        $totalBidsSql = "SELECT COUNT(*) as total FROM bids WHERE bid_amount > 0 AND event_id = ?";
        $totalBids = $this->db->query($totalBidsSql, [$this->eventId])->getRowArray()['total'];

        // Total bid value filtered by event_id
        $totalValueSql = "SELECT COALESCE(SUM(bid_amount), 0) as total FROM bids WHERE bid_amount > 0 AND event_id = ?";
        $totalBidValue = $this->db->query($totalValueSql, [$this->eventId])->getRowArray()['total'];

        // Average bid filtered by event_id
        $avgBidSql = "SELECT COALESCE(AVG(bid_amount), 0) as average FROM bids WHERE bid_amount > 0 AND event_id = ?";
        $averageBid = $this->db->query($avgBidSql, [$this->eventId])->getRowArray()['average'];

        // Highest bid filtered by event_id
        $highestBidSql = "SELECT COALESCE(MAX(bid_amount), 0) as highest FROM bids WHERE bid_amount > 0 AND event_id = ?";
        $highestBid = $this->db->query($highestBidSql, [$this->eventId])->getRowArray()['highest'];

        // Active teams count filtered by event_id
        $activeTeamsSql = "SELECT COUNT(*) as count FROM teams WHERE is_active = 1 AND event_id = ?";
        $activeTeams = $this->db->query($activeTeamsSql, [$this->eventId])->getRowArray()['count'];

        // Players sold count filtered by event_id
        $soldPlayersSql = "SELECT COUNT(*) as count FROM players WHERE auction_status = 'sold' AND is_active = 1 AND event_id = ?";
        $soldPlayers = $this->db->query($soldPlayersSql, [$this->eventId])->getRowArray()['count'];

        return [
            'total_bids' => $totalBids,
            'total_bid_value' => $totalBidValue,
            'average_bid' => $averageBid,
            'highest_bid' => $highestBid,
            'active_teams' => $activeTeams,
            'sold_players' => $soldPlayers
        ];
    }

    public function export($type = 'pdf')
    {
        $bidLogs = $this->getBidLogs();

        if ($type === 'pdf') {
            $html = '<h3>Bid Logs Report (Event ID: ' . $this->eventId . ')</h3><table border="1" cellspacing="0" cellpadding="5">
                <tr>
                    <th>Player Name</th><th>Role</th><th>Company</th><th>Team</th><th>Bid Amount</th><th>Bid Time</th><th>Status</th>
                </tr>';
            foreach ($bidLogs as $bid) {
                $status = $bid['is_sold'] ? 'Sold' : ($bid['is_unsold'] ? 'Unsold' : 'Bidding');
                $html .= '<tr>
                    <td>' . esc($bid['player_name']) . '</td>
                    <td>' . esc($bid['player_role']) . '</td>
                    <td>' . esc($bid['company_name']) . '</td>
                    <td>' . esc($bid['team_name']) . '</td>
                    <td>₹' . number_format($bid['bid_amount'], 2) . '</td>
                    <td>' . date('M j, Y g:i A', strtotime($bid['created_at'])) . '</td>
                    <td>' . esc($status) . '</td>
                </tr>';
            }
            $html .= '</table>';

            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'bid_logs_event_' . $this->eventId . '_' . date('Ymd_His') . '.pdf';
            $dompdf->stream($filename, ["Attachment" => true]);
            exit;
        } elseif ($type === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Player Name')
                ->setCellValue('B1', 'Role')
                ->setCellValue('C1', 'Company')
                ->setCellValue('D1', 'Team')
                ->setCellValue('E1', 'Bid Amount')
                ->setCellValue('F1', 'Bid Time')
                ->setCellValue('G1', 'Status');

            $row = 2;
            foreach ($bidLogs as $bid) {
                $status = $bid['is_sold'] ? 'Sold' : ($bid['is_unsold'] ? 'Unsold' : 'Bidding');
                $sheet->setCellValue('A' . $row, $bid['player_name'])
                    ->setCellValue('B' . $row, $bid['player_role'])
                    ->setCellValue('C' . $row, $bid['company_name'])
                    ->setCellValue('D' . $row, $bid['team_name'])
                    ->setCellValue('E' . $row, '₹' . number_format($bid['bid_amount'], 2))
                    ->setCellValue('F' . $row, date('M j, Y g:i A', strtotime($bid['created_at'])))
                    ->setCellValue('G' . $row, $status);
                $row++;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="bid_logs_event_' . $this->eventId . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Invalid export type: " . esc($type));
        }
    }

    public function getRealtimeData()
    {
        $bidLogs = $this->getBidLogs();
        $statistics = $this->getBidStatistics();

        return $this->response->setJSON([
            'bid_logs' => $bidLogs,
            'statistics' => $statistics,
            'event_id' => $this->eventId
        ]);
    }
}