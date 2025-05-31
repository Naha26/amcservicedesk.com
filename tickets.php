<?php
// Database configuration
$dbFile = 'tickets.db';

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get filter from query string
    $filter = $_GET['filter'] ?? 'all';
    
    // Prepare query based on filter
    if ($filter === 'open') {
        $stmt = $db->prepare("SELECT * FROM tickets WHERE status = 'Open' ORDER BY created_at DESC");
    } elseif ($filter === 'unresolved') {
        $stmt = $db->prepare("SELECT * FROM tickets WHERE status != 'Resolved' ORDER BY created_at DESC");
    } elseif ($filter === 'closed') {
        $stmt = $db->prepare("SELECT * FROM tickets WHERE status = 'Resolved' ORDER BY created_at DESC");
    } else {
        $stmt = $db->prepare("SELECT * FROM tickets ORDER BY created_at DESC");
    }
    
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $tickets = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMC SERVICE DESK - Tickets</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>AMC SERVICE DESK</h1>
            <nav>
                <ul>
                    <li><a href="index.html" id="log-ticket-tab">Log Ticket</a></li>
                    <li><a href="#" class="active" id="view-tickets-tab">View Tickets</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Service Desk Tickets</h2>
            
            <div class="filter-section">
                <a href="tickets.php?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All Tickets</a>
                <a href="tickets.php?filter=open" class="filter-btn <?= $filter === 'open' ? 'active' : '' ?>">Open</a>
                <a href="tickets.php?filter=unresolved" class="filter-btn <?= $filter === 'unresolved' ? 'active' : '' ?>">Unresolved</a>
                <a href="tickets.php?filter=closed" class="filter-btn <?= $filter === 'closed' ? 'active' : '' ?>">Closed</a>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (empty($tickets)): ?>
                <p>No tickets found.</p>
            <?php else: ?>
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?= htmlspecialchars($ticket['ticket_id']) ?></td>
                                <td><?= htmlspecialchars($ticket['subject']) ?></td>
                                <td><?= htmlspecialchars($ticket['department']) ?></td>
                                <td class="<?= strtolower($ticket['priority']) === 'high' || strtolower($ticket['priority']) === 'critical' ? 'priority-' . strtolower($ticket['priority']) : '' ?>">
                                    <?= htmlspecialchars($ticket['priority']) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($ticket['status']) ?>">
                                        <?= htmlspecialchars($ticket['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y H:i', strtotime($ticket['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2023 African Mining and Crushing - Service Desk</p>
        </footer>
    </div>
</body>
</html>