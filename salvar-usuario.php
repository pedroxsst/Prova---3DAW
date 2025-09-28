<!DOCTYPE html>
<html>
<head>
<title>Admin - Cadastro e Login</title>
<style>
    .tab-btn { margin-right: 10px; padding: 5px 15px; cursor: pointer; }
    .active { background: #007bff; color: #fff; border: none; }
    .hidden { display: none; }
</style>
<script>
function showForm(form) {
    document.getElementById('cadastro').classList.add('hidden');
    document.getElementById('login').classList.add('hidden');
    document.getElementById(form).classList.remove('hidden');
    document.getElementById('btn-cadastro').classList.remove('active');
    document.getElementById('btn-login').classList.remove('active');
    document.getElementById('btn-' + form).classList.add('active');
}
</script>
</head>
<body>

<h1>Cadastro e Login de Usuário</h1>
<button id="btn-cadastro" class="tab-btn active" onclick="showForm('cadastro')">Cadastrar</button>
<button id="btn-login" class="tab-btn" onclick="showForm('login')">Login</button>

<div id="cadastro">
    <h2>Cadastro</h2>
    <form action="salvar-usuario.php" method="POST">
        <input type="hidden" name="acao" value="cadastrar">
        <p>
            <label>Nome:</label>
            <input type="text" name="name" required>
        </p>
        <p>
            <label>E-mail:</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label>Senha:</label>
            <input type="password" name="senha" required>
        </p>
        <p>
            <button type="submit">Cadastrar</button>
        </p>
    </form>
</div>

<div id="login" class="hidden">
    <h2>Login</h2>
    <form action="salvar-usuario.php" method="POST">
        <input type="hidden" name="acao" value="login">
        <p>
            <label>E-mail:</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label>Senha:</label>
            <input type="password" name="senha" required>
        </p>
        <p>
            <button type="submit">Entrar</button>
        </p>
    </form>
</div>

</body>
</html>
<?php

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


$nameErr = $emailErr = $senhaErr = "";
$name = $email = $senha = "";
$successMsg = "";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usuarios";


if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['acao'])) {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : (isset($_GET['acao']) ? $_GET['acao'] : '');
    // Cadastro
    if ($acao === 'cadastrar') {
        $valid = true;
        if (empty($_POST["name"])) {
            $nameErr = "Nome requerido";
            $valid = false;
        } else {
            $name = test_input($_POST["name"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
                $nameErr = "Apenas letras e espaço";
                $valid = false;
            }
        }

        if (empty($_POST["email"])) {
            $emailErr = "Email requerido";
            $valid = false;
        } else {
            $email = test_input($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Email invalido!";
                $valid = false;
            }
        }

        if (empty($_POST["senha"])) {
            $senhaErr = "Senha requerida";
            $valid = false;
        } else {
            $senha = password_hash(test_input($_POST["senha"]), PASSWORD_DEFAULT);
        }

        if ($valid) {
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }
            // Verifica se email já existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $successMsg = "E-mail já cadastrado.";
            } else {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $senha);
                if ($stmt->execute()) {
                    $successMsg = "Usuário cadastrado com sucesso!";
                } else {
                    $successMsg = "Erro ao cadastrar: " . $stmt->error;
                }
            }
            $stmt->close();
            $conn->close();
        }
    }
    // Login
    if ($acao === 'login') {
        if (empty($_POST["email"])) {
            $emailErr = "Email requerido";
        } else {
            $email = test_input($_POST["email"]);
        }
        if (empty($_POST["senha"])) {
            $senhaErr = "Senha requerida";
        } else {
            $senha = test_input($_POST["senha"]);
        }
        if ($email && $senha) {
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Falha na conexão: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $nome, $senha_hash);
                $stmt->fetch();
                if (password_verify($senha, $senha_hash)) {
                    header('Location: pagina-usuario.php');
                    exit();
                } else {
                    $successMsg = "Senha incorreta.";
                }
            } else {
                $successMsg = "Usuário não encontrado.";
            }
            $stmt->close();
            $conn->close();
        }
    }
    // Exclusão
    if ($acao === 'excluir' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $successMsg = "Erro ao excluir: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    }
}
?>
