<?php
// Função para limpar e tratar os dados do formulário
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$perguntaErr = "";
$pergunta = $tipo = $alt_a = $alt_b = $alt_c = $alt_d = $correta = "";
$successMsg = "";

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seubanco";

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
        $tipo = isset($row['tipo']) ? $row['tipo'] : 'discursiva';
        if ($tipo == 'multipla') {
            $alt_a = $row['alt_a'];
            $alt_b = $row['alt_b'];
            $alt_c = $row['alt_c'];
            $alt_d = $row['alt_d'];
            $correta = $row['correta'];
        }
    } else {
        $successMsg = "Pergunta não encontrada.";
    }
    $stmt->close();
}

// Atualizar pergunta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar'])) {
    $id = intval($_POST['id']);
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'discursiva';
    $valid = true;
    if ($tipo == 'multipla') {
        $pergunta = test_input($_POST["pergunta"]);
        $alt_a = test_input($_POST["alt_a"]);
        $alt_b = test_input($_POST["alt_b"]);
        $alt_c = test_input($_POST["alt_c"]);
        $alt_d = test_input($_POST["alt_d"]);
        $correta = isset($_POST["correta"]) ? $_POST["correta"] : "";
        if (empty($pergunta) || empty($alt_a) || empty($alt_b) || empty($alt_c) || empty($alt_d) || empty($correta)) {
            $perguntaErr = "Todos os campos são obrigatórios para múltipla escolha.";
            $valid = false;
        }
    } else {
        if (empty($_POST["pergunta"])) {
            $perguntaErr = "Pergunta requerida";
            $valid = false;
        } else {
            $pergunta = test_input($_POST["pergunta"]);
        }
    }

    if ($valid) {
        if ($tipo == 'multipla') {
            $sql = "UPDATE perguntas SET pergunta=?, alt_a=?, alt_b=?, alt_c=?, alt_d=?, correta=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $pergunta, $alt_a, $alt_b, $alt_c, $alt_d, $correta, $id);
        } else {
            $sql = "UPDATE perguntas SET pergunta=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $pergunta, $id);
        }
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
    // Excluir respostas primeiro
    $sql = "DELETE FROM respostas WHERE id_pergunta=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Excluir a pergunta
    $sql = "DELETE FROM perguntas WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $successMsg = "Pergunta e respostas deletadas com sucesso!";
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
    <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
    <?php if ($tipo == 'multipla'): ?>
        <p>
            <label>Pergunta:</label><br>
            <textarea name="pergunta" rows="4" cols="50"><?php echo htmlspecialchars($pergunta); ?></textarea>
        </p>
        <p>
            <label>Alternativa A:</label>
            <input type="text" name="alt_a" value="<?php echo htmlspecialchars($alt_a); ?>">
        </p>
        <p>
            <label>Alternativa B:</label>
            <input type="text" name="alt_b" value="<?php echo htmlspecialchars($alt_b); ?>">
        </p>
        <p>
            <label>Alternativa C:</label>
            <input type="text" name="alt_c" value="<?php echo htmlspecialchars($alt_c); ?>">
        </p>
        <p>
            <label>Alternativa D:</label>
            <input type="text" name="alt_d" value="<?php echo htmlspecialchars($alt_d); ?>">
        </p>
        <p>
            <label>Correta:</label>
            <select name="correta">
                <option value="A" <?php if($correta=='A') echo 'selected'; ?>>A</option>
                <option value="B" <?php if($correta=='B') echo 'selected'; ?>>B</option>
                <option value="C" <?php if($correta=='C') echo 'selected'; ?>>C</option>
                <option value="D" <?php if($correta=='D') echo 'selected'; ?>>D</option>
            </select>
        </p>
    <?php else: ?>
        <p>
            <label>Pergunta:</label><br>
            <textarea name="pergunta" rows="4" cols="50"><?php echo htmlspecialchars($pergunta); ?></textarea>
        </p>
    <?php endif; ?>
    <span style="color:red;"> <?php echo $perguntaErr; ?> </span>
    <button type="submit" name="atualizar">Atualizar</button>
    <button type="submit" name="deletar" onclick="return confirm('Tem certeza que deseja deletar esta pergunta?');">Deletar</button>
</form>
<p style="color:green;"> <?php echo $successMsg; ?> </p>
