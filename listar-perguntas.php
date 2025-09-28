<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seubanco";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// 1. Listar todas as perguntas e respostas
echo "<h2>Todas as Perguntas e Respostas</h2>";
$sql = "SELECT * FROM perguntas ORDER BY id ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo '<table border="1" cellpadding="5"><tr><th>ID</th><th>Pergunta</th><th>Tipo</th><th>Respostas</th></tr>';
    while ($row = $result->fetch_assoc()) {
        $pid = $row['id'];
        $perg = htmlspecialchars($row['pergunta']);
        $tipo = isset($row['tipo']) ? $row['tipo'] : 'discursiva';
        // Buscar respostas
        $respostas = [];
        $sqlr = "SELECT resposta FROM respostas WHERE id_pergunta = $pid";
        $res = $conn->query($sqlr);
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $respostas[] = htmlspecialchars($r['resposta']);
            }
        }
        echo "<tr>";
        echo "<td>$pid</td>";
        echo "<td>$perg</td>";
        echo "<td>$tipo</td>";
        echo "<td>" . (count($respostas) ? implode('<hr>', $respostas) : '<i>Sem resposta</i>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Nenhuma pergunta cadastrada.";
}

// 2. Listar uma pergunta específica (por id)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM perguntas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo "<h2>Pergunta ID {$row['id']}</h2>";
        echo "<b>Pergunta:</b> " . htmlspecialchars($row['pergunta']) . "<br>";
        echo "<b>Tipo:</b> " . htmlspecialchars($row['tipo']) . "<br>";
        if ($row['tipo'] == 'multipla') {
            echo "<b>Alternativas:</b><br>";
            echo "A) " . htmlspecialchars($row['alt_a']) . "<br>";
            echo "B) " . htmlspecialchars($row['alt_b']) . "<br>";
            echo "C) " . htmlspecialchars($row['alt_c']) . "<br>";
            echo "D) " . htmlspecialchars($row['alt_d']) . "<br>";
            echo "<b>Correta:</b> " . htmlspecialchars($row['correta']) . "<br>";
        }
        // Listar respostas para esta pergunta
        $sqlr = "SELECT resposta FROM respostas WHERE id_pergunta = ?";
        $stmtr = $conn->prepare($sqlr);
        $stmtr->bind_param("i", $id);
        $stmtr->execute();
        $resr = $stmtr->get_result();
        echo "<b>Respostas:</b><br>";
        if ($resr && $resr->num_rows > 0) {
            while ($r = $resr->fetch_assoc()) {
                echo htmlspecialchars($r['resposta']) . "<hr>";
            }
        } else {
            echo "<i>Sem resposta</i>";
        }
        $stmtr->close();
    } else {
        echo "<h2>Pergunta não encontrada.</h2>";
    }
    $stmt->close();
}

$conn->close();
?>
