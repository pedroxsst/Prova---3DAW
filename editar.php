

<?php
// Função para limpar e tratar os dados do formulário
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$perguntaErr = "";
$pergunta = "";
$successMsg = "";

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seubanco"; // Altere para o nome do seu banco de dados

// Conexão com o banco
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Listar perguntas e respostas (assumindo tabela respostas com id, id_pergunta, resposta)
echo '<h2>Perguntas e Respostas</h2>';
echo '<table border="1" cellpadding="5"><tr><th>ID</th><th>Pergunta</th><th>Respostas</th><th>Ações</th></tr>';
$sql = "SELECT * FROM perguntas ORDER BY id ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pid = $row['id'];
        $perg = htmlspecialchars($row['pergunta']);
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
        echo "<td>" . (count($respostas) ? implode('<hr>', $respostas) : '<i>Sem resposta</i>') . "</td>";
        echo '<td><a href="editar.php?id=' . $pid . '">Editar</a></td>';
        echo "</tr>";
    }
} else {
    echo '<tr><td colspan="4">Nenhuma pergunta cadastrada.</td></tr>';
}
echo '</table><br><br>';

// Buscar dados da pergunta para edição
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM perguntas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pergunta = $row['pergunta'];
    } else {
        $successMsg = "Pergunta não encontrada.";
    }
    $stmt->close();
}

// Atualizar pergunta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar'])) {
    $id = intval($_POST['id']);
    $valid = true;
    if (empty($_POST["pergunta"])) {
        $perguntaErr = "Pergunta requerida";
        $valid = false;
    } else {
        $pergunta = test_input($_POST["pergunta"]);
    }

    if ($valid) {
        $sql = "UPDATE perguntas SET pergunta=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $pergunta, $id);
        if ($stmt->execute()) {
            $successMsg = "Pergunta atualizada com sucesso!";
        } else {
            $successMsg = "Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Deletar pergunta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletar'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM perguntas WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $successMsg = "Pergunta deletada com sucesso!";
        $pergunta = "";
    } else {
        $successMsg = "Erro ao deletar: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!-- Formulário de edição de pergunta -->
<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? intval($_GET['id']) : ''; ?>">
    <p>
        <label>Pergunta:</label><br>
        <textarea name="pergunta" rows="4" cols="50"><?php echo htmlspecialchars($pergunta); ?></textarea>
        <span style="color:red;"> <?php echo $perguntaErr; ?> </span>
    </p>
    <button type="submit" name="atualizar">Atualizar</button>
    <button type="submit" name="deletar" onclick="return confirm('Tem certeza que deseja deletar esta pergunta?');">Deletar</button>
</form>
<p style="color:green;"> <?php echo $successMsg; ?> </p>