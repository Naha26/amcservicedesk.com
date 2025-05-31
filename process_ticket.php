<?php
header('Content-Type: application/json');

// Database configuration (using SQLite for simplicity - in production, use MySQL or similar)
$dbFile = 'tickets.db';

try {
    // Create database and table if they don't exist
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $db->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ticket_id TEXT NOT NULL,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        department TEXT NOT NULL,
        priority TEXT NOT NULL,
        subject TEXT NOT NULL,
        description TEXT NOT NULL,
        status TEXT DEFAULT 'Open',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Generate a unique ticket ID
    $ticketId = 'AMC-' . strtoupper(uniqid());
    
    // Prepare data for insertion
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($department) || empty($priority) || empty($subject) || empty($description)) {
        throw new Exception('All fields are required.');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address.');
    }
    
    // Insert ticket into database
    $stmt = $db->prepare("INSERT INTO tickets (ticket_id, name, email, department, priority, subject, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$ticketId, $name, $email, $department, $priority, $subject, $description]);
    
    // Send email notification
    $to = "Naha.Innocent@outlook.com";
    $subject = "New Service Desk Ticket: $ticketId - $subject";
    
    $message = "
    <html>
    <head>
        <title>New Service Desk Ticket: $ticketId</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .ticket-info { background-color: #f5f5f5; padding: 15px; border-radius: 5px; }
            .label { font-weight: bold; color: #0056b3; }
        </style>
    </head>
    <body>
        <h2 style='color: #0056b3;'>New Service Desk Ticket</h2>
        <div class='ticket-info'>
            <p><span class='label'>Ticket ID:</span> $ticketId</p>
            <p><span class='label'>Name:</span> $name</p>
            <p><span class='label'>Email:</span> $email</p>
            <p><span class='label'>Department:</span> $department</p>
            <p><span class='label'>Priority:</span> $priority</p>
            <p><span class='label'>Subject:</span> $subject</p>
            <p><span class='label'>Description:</span><br>".nl2br($description)."</p>
        </div>
        <p>Please log in to the AMC Service Desk to view and manage this ticket.</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: AMC Service Desk <amc.servicedesk@outlook.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // SMTP configuration for Microsoft/Outlook
    ini_set("SMTP", "smtp.office365.com");
    ini_set("smtp_port", 587);
    ini_set("sendmail_from", "amc.servicedesk@outlook.com");
    
    // In a production environment, you would use PHPMailer or similar library for better handling
    $mailSent = mail($to, $subject, $message, $headers);
    
    if (!$mailSent) {
        // Log the error but don't fail the ticket submission
        error_log("Failed to send email notification for ticket $ticketId");
    }
    
    echo json_encode([
        'success' => true,
        'ticketId' => $ticketId,
        'message' => 'Ticket created successfully.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>