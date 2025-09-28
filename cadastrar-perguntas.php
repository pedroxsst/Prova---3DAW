<!DOCTYPE html>
<html>
<head>
<title>Admin</title>
<script>
function toggleTipoPergunta() {
    var tipo = document.getElementById('tipo').value;
    document.getElementById('discursiva').style.display = tipo === 'discursiva' ? 'block' : 'none';
    document.getElementById('multipla').style.display = tipo === 'multipla' ? 'block' : 'none';
}
</script>
</head>
<body>

<h1>Cadastrar Pergunta</h1>

<form action="" method="POST">
    <p>
        <label>Tipo de Pergunta:</label>
        <select name="tipo" id="tipo" onchange="toggleTipoPergunta()" required>
            <option value="discursiva" <?php if(isset($_POST['tipo']) && $_POST['tipo']=='discursiva') echo 'selected'; ?>>Discursiva</option>
            <option value="multipla" <?php if(isset($_POST['tipo']) && $_POST['tipo']=='multipla') echo 'selected'; ?>>Múltipla Escolha</option>
        </select>
    </p>
    <div id="discursiva" style="display:<?php echo (!isset($_POST['tipo']) || $_POST['tipo']=='discursiva') ? 'block':'none'; ?>">
        <p>
            <label>Pergunta:</label><br>
            <textarea name="pergunta" rows="4" cols="50"><?php echo isset($pergunta) ? htmlspecialchars($pergunta) : ''; ?></textarea>
        </p>
    </div>
    <div id="multipla" style="display:<?php echo (isset($_POST['tipo']) && $_POST['tipo']=='multipla') ? 'block':'none'; ?>">
        <p>
            <label>Pergunta:</label><br>
            <textarea name="pergunta_multipla" rows="4" cols="50"><?php echo isset($pergunta_multipla) ? htmlspecialchars($pergunta_multipla) : ''; ?></textarea>
        </p>
        <p>
            <label>Alternativa A:</label>
            <input type="text" name="alt_a" value="<?php echo isset($alt_a) ? htmlspecialchars($alt_a) : ''; ?>">
        </p>
        <p>
            <label>Alternativa B:</label>
            <input type="text" name="alt_b" value="<?php echo isset($alt_b) ? htmlspecialchars($alt_b) : ''; ?>">
        </p>
        <p>
            <label>Alternativa C:</label>
            <input type="text" name="alt_c" value="<?php echo isset($alt_c) ? htmlspecialchars($alt_c) : ''; ?>">
        </p>
        <p>
            <label>Alternativa D:</label>
            <input type="text" name="alt_d" value="<?php echo isset($alt_d) ? htmlspecialchars($alt_d) : ''; ?>">
        </p>
        <p>
            <label>Correta:</label>
            <select name="correta">
                <option value="A" <?php if(isset($correta) && $correta=='A') echo 'selected'; ?>>A</option>
                <option value="B" <?php if(isset($correta) && $correta=='B') echo 'selected'; ?>>B</option>
                <option value="C" <?php if(isset($correta) && $correta=='C') echo 'selected'; ?>>C</option>
                <option value="D" <?php if(isset($correta) && $correta=='D') echo 'selected'; ?>>D</option>
            </select>
        </p>
    </div>
    <p>
        <button type="submit">Salvar</button>
    </p>
</form>

</body>
</html>
<?php
// Função para limpar e tratar os dados do formulário
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$pergunta = $pergunta_multipla = $alt_a = $alt_b = $alt_c = $alt_d = $correta = "";
$successMsg = "";

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seubanco";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = isset($_POST["tipo"]) ? $_POST["tipo"] : "discursiva";
    if ($tipo == "discursiva") {
        if (empty($_POST["pergunta"])) {
            $successMsg = "Pergunta é obrigatória.";
        } else {
            $pergunta = test_input($_POST["pergunta"]);

            // Cria conexão
            $conn = new mysqli($servername, $username, $password, $dbname);
            // Checa conexão
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO perguntas (pergunta, tipo) VALUES (?, 'discursiva')");
            $stmt->bind_param("s", $pergunta);

            if ($stmt->execute()) {
                $successMsg = "Pergunta discursiva cadastrada com sucesso!";
                $pergunta = "";
            } else {
                $successMsg = "Erro ao cadastrar: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    } else if ($tipo == "multipla") {
        $pergunta_multipla = test_input($_POST["pergunta_multipla"]);
        $alt_a = test_input($_POST["alt_a"]);
        $alt_b = test_input($_POST["alt_b"]);
        $alt_c = test_input($_POST["alt_c"]);
        $alt_d = test_input($_POST["alt_d"]);
        $correta = isset($_POST["correta"]) ? $_POST["correta"] : "";
        if (empty($pergunta_multipla) || empty($alt_a) || empty($alt_b) || empty($alt_c) || empty($alt_d) || empty($correta)) {
            $successMsg = "Todos os campos da pergunta de múltipla escolha são obrigatórios.";
        } else {
            // Cria conexão
            $conn = new mysqli($servername, $username, $password, $dbname);
            // Checa conexão
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO perguntas (pergunta, tipo, alt_a, alt_b, alt_c, alt_d, correta) VALUES (?, 'multipla', ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $pergunta_multipla, $alt_a, $alt_b, $alt_c, $alt_d, $correta);

            if ($stmt->execute()) {
                $successMsg = "Pergunta de múltipla escolha cadastrada com sucesso!";
                $pergunta_multipla = $alt_a = $alt_b = $alt_c = $alt_d = $correta = "";
            } else {
                $successMsg = "Erro ao cadastrar: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>
<p style="color:green;"> <?php echo $successMsg; ?> </p>
