<?php
// Database setup script
echo "Setting up Velvet Vogue database...\n";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "velvet_vogue";

try {
    // Create connection
    $pdo = new PDO("mysql:host=$servername", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✓ Database '$dbname' created or already exists\n";
    
    // Use the database
    $pdo->exec("USE $dbname");
    
    // Read and execute the SQL file
    $sqlFile = 'database_setup.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split by semicolon and execute each statement
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (Exception $e) {
                    // Ignore errors for statements that might already exist
                    echo "Info: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "✓ Database setup completed successfully!\n";
        
        // Check if products exist
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Products table has {$result['count']} products\n";
        
    } else {
        echo "✗ Error: database_setup.sql file not found\n";
    }
    
} catch(PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>