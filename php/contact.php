<?php
require_once 'config.php';

// Handle contact form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    try {
        $pdo = getConnection();
        
        // Validate and sanitize input
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email']);
        $subject = trim($_POST['subject']);
        $message = trim($_POST['message']);
        $phone = trim($_POST['phone'] ?? '');
        $order_number = trim($_POST['order_number'] ?? '');
        $inquiry_type = trim($_POST['subject'] ?? ''); // Map subject to inquiry_type
        
        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($subject) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }
        
        // Insert contact message into database
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (first_name, last_name, email, subject, message, phone, order_number, inquiry_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $subject,
            $message,
            $phone,
            $order_number,
            $inquiry_type
        ]);
        
        if ($result) {
            // If newsletter subscription is requested
            if (isset($_POST['newsletter']) && $_POST['newsletter'] == '1') {
                // Add to newsletter (you could have a separate newsletter table)
                // For now, we'll just note it in the message
            }
            
            // Send email notification (in a real application)
            // sendEmailNotification($name, $email, $subject, $message);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Thank you for your message! We will get back to you within 24 hours.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
        }
        
    } catch (Exception $e) {
        error_log('Contact form error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Function to send email notification (placeholder)
function sendEmailNotification($first_name, $last_name, $email, $subject, $message) {
    // In a real application, you would use a service like PHPMailer, SendGrid, etc.
    // This is just a placeholder function
    
    $to = 'info@velvetvogue.com';
    $email_subject = 'New Contact Form Submission: ' . $subject;
    $email_body = "
        New contact form submission received:
        
        Name: $first_name $last_name
        Email: $email
        Subject: $subject
        
        Message:
        $message
        
        ---
        This message was sent from the Velvet Vogue contact form.
    ";
    
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Uncomment the following line to actually send emails
    // mail($to, $email_subject, $email_body, $headers);
}
?>