<?php
// includes/connect.php
$servername = 'localhost';
$username = 'root';
$password = '';
$database = 'webpc'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$database;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Lỗi kết nối CSDL: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

function generate_new_id(PDO $pdoConnection, string $tableName, string $idColumnName, string $prefix, int $numberPartLength = 3): string
{
    try {
        $sql = "SELECT MAX(CAST(SUBSTRING(LOWER({$idColumnName}), LENGTH(:prefix) + 1) AS UNSIGNED)) AS max_numeric_part
                FROM {$tableName}
                WHERE LOWER({$idColumnName}) LIKE LOWER(:prefix_like)";

        $stmt = $pdoConnection->prepare($sql);
        $prefixForLength = strtolower($prefix); // Dùng prefix thường cho LENGTH
        $prefixLike = strtolower($prefix) . '%';
        
        $stmt->bindParam(':prefix', $prefixForLength, PDO::PARAM_STR);
        $stmt->bindParam(':prefix_like', $prefixLike, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNumericPart = 1;
        if ($result && $result['max_numeric_part'] !== null) {
            $nextNumericPart = (int)$result['max_numeric_part'] + 1;
        }

        $formattedNumericPart = str_pad((string)$nextNumericPart, $numberPartLength, '0', STR_PAD_LEFT);
        return strtoupper($prefix) . $formattedNumericPart;

    } catch (PDOException $e) {
        error_log("Lỗi khi tạo ID mới cho bảng {$tableName}, cột {$idColumnName}: " . $e->getMessage());
        throw $e;
    }
}
?>