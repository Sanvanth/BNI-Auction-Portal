<?php
namespace App\Controllers;

use App\Models\PlayerModel;
use CodeIgniter\Controller;

class Leaderboard extends Controller
{
    public function index()
    {
        // Check if user is logged in and get event_id from session
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $eventId = session()->get('event_id');
        if (!$eventId) {
            session()->setFlashdata('error', 'Event ID not found in session.');
            return redirect()->to(base_url('dashboard'));
        }

        $playerModel = new PlayerModel();
        
        // Get top 10 sold players with highest final price for the specific event
        $leaderboard = $playerModel->select('
                players.player_id,
                players.player_name,
                players.player_role,
                players.company_name,
                players.final_price,
                players.profile_pic,
                teams.team_id,
                teams.team_name,
                teams.team_logo
            ')
            ->join('teams', 'teams.team_id = players.sold_to', 'left')
            ->where('players.sold_to IS NOT', null)
            ->where('players.final_price IS NOT', null)
            ->where('players.is_unsold', false)
            ->where('players.event_id', $eventId)
            ->orderBy('players.final_price', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Calculate statistics for the specific event
        $statistics = $this->calculateStatistics($playerModel, $eventId);

        return view('admin/leaderboard', [
            'leaderboard' => $leaderboard,
            'statistics' => $statistics,
            'activeMenu' => 'leaderboard',
            'event_id' => $eventId
        ]);
    }

    private function calculateStatistics($playerModel, $eventId)
    {
        // Get all sold players for the specific event using get() instead of findAll()
        $soldPlayers = $playerModel->select('final_price')
            ->where('sold_to IS NOT', null)
            ->where('final_price IS NOT', null)
            ->where('is_unsold', false)
            ->where('event_id', $eventId)
            ->get()
            ->getResultArray();

        if (empty($soldPlayers)) {
            return [
                'total_sold' => 0,
                'total_value' => 0,
                'average_price' => 0,
                'highest_sale' => 0
            ];
        }

        $totalSold = count($soldPlayers);
        $totalValue = array_sum(array_column($soldPlayers, 'final_price'));
        $averagePrice = $totalValue / $totalSold;
        $highestSale = max(array_column($soldPlayers, 'final_price'));

        return [
            'total_sold' => $totalSold,
            'total_value' => $totalValue,
            'average_price' => $averagePrice,
            'highest_sale' => $highestSale
        ];
    }

    public function export($format = 'excel')
    {
        // Check if user is logged in and get event_id from session
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        $eventId = session()->get('event_id');
        if (!$eventId) {
            session()->setFlashdata('error', 'Event ID not found in session.');
            return redirect()->to(base_url('dashboard'));
        }

        $playerModel = new PlayerModel();
        
        // Get all sold players for export for the specific event
        $leaderboard = $playerModel->select('
                players.player_name,
                players.player_role,
                players.company_name,
                players.final_price,
                teams.team_name
            ')
            ->join('teams', 'teams.team_id = players.sold_to AND teams.event_id = players.event_id', 'left')
            ->where('players.sold_to IS NOT', null)
            ->where('players.final_price IS NOT', null)
            ->where('players.is_unsold', false)
            ->where('players.event_id', $eventId)
            ->orderBy('players.final_price', 'DESC')
            ->get()
            ->getResultArray();

        if ($format === 'excel') {
            return $this->exportExcel($leaderboard);
        } elseif ($format === 'pdf') {
            return $this->exportPDF($leaderboard);
        }
    }

    private function exportExcel($data)
    {
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="auction_leaderboard_event_' . session()->get('event_id') . '.xls"');
        header('Cache-Control: max-age=0');

        // Create Excel content
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>Rank</th>";
        echo "<th>Player Name</th>";
        echo "<th>Company</th>";
        echo "<th>Role</th>";
        echo "<th>Final Price</th>";
        echo "<th>Team</th>";
        echo "</tr>";

        foreach ($data as $index => $player) {
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . htmlspecialchars($player['player_name']) . "</td>";
            echo "<td>" . htmlspecialchars($player['company_name']) . "</td>";
            echo "<td>" . htmlspecialchars($player['player_role']) . "</td>";
            echo "<td>₹" . number_format($player['final_price'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($player['team_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }

    private function exportPDF($data)
    {
        // For PDF export, you would need a PDF library like TCPDF or DOMPDF
        // This is a basic implementation
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="auction_leaderboard_event_' . session()->get('event_id') . '.pdf"');
        
        // You would implement PDF generation here
        // For now, just redirect back with a message
        session()->setFlashdata('error', 'PDF export feature is not implemented yet.');
        return redirect()->to(base_url('admin/leaderboard'));
    }
}