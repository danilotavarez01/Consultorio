<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos
if (!hasPermission('manage_prescriptions') && !hasPermission('view_prescriptions')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

// Procesar formulario de nueva receta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'crear') {
    // Verificar permiso específico para crear recetas
    if (!hasPermission('manage_prescriptions')) {
        header("location: unauthorized.php");
        exit;
    }
    
    $sql = "INSERT INTO recetas (paciente_id, fecha_receta, medicamentos, indicaciones, doctor_id, tamano_receta) VALUES (?, CURDATE(), ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bindParam(1, $_POST['paciente_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $_POST['medicamentos'], PDO::PARAM_STR);
        $stmt->bindParam(3, $_POST['indicaciones'], PDO::PARAM_STR);
        $stmt->bindParam(4, $_SESSION['id'], PDO::PARAM_INT);
        $stmt->bindParam(5, $_POST['tamano_receta'], PDO::PARAM_STR);
        $stmt->execute();
        $receta_id = $conn->lastInsertId();
        $stmt = null;
        
        // Redirigir a la vista de impresión
        header("location: imprimir_receta.php?id=" . $receta_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Recetas - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <h2>Gestión de Recetas</h2>
                <hr>

                <!-- Botón para nueva receta -->
                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#nuevaRecetaModal">
                    <i class="fas fa-prescription"></i> Nueva Receta
                </button>

                <!-- Campo de búsqueda -->
                <div class="form-group mb-3">
                    <input type="text" id="searchReceta" class="form-control" placeholder="Buscar receta...">
                </div>

                <!-- Tabla de recetas -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>DNI</th>
                                <th>Doctor</th>
                                <th>Medicamentos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT r.*, p.nombre as paciente_nombre, p.apellido as paciente_apellido, 
                                   p.dni, u.nombre as doctor_nombre
                                   FROM recetas r 
                                   JOIN pacientes p ON r.paciente_id = p.id 
                                   JOIN usuarios u ON r.doctor_id = u.id 
                                   ORDER BY r.fecha_receta DESC";
                            $stmt = $conn->query($sql);
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>".date('d/m/Y', strtotime($row['fecha_receta']))."</td>";
                                echo "<td>".$row['paciente_nombre']." ".$row['paciente_apellido']."</td>";
                                echo "<td>".$row['dni']."</td>";
                                echo "<td>".$row['doctor_nombre']."</td>";
                                echo "<td>".substr($row['medicamentos'], 0, 50)."...</td>";
                                echo "<td>
                                    <a href='imprimir_receta.php?id=".$row['id']."' class='btn btn-info btn-sm' target='_blank'>
                                        <i class='fas fa-print'></i>
                                    </a>
                                    </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Receta -->
    <div class="modal fade" id="nuevaRecetaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Receta</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-group">
                            <label>Paciente</label>
                            <select name="paciente_id" class="form-control" required>
                                <?php
                                $sql = "SELECT id, nombre, apellido, dni FROM pacientes ORDER BY apellido, nombre";
                                $stmt = $conn->query($sql);
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='".$row['id']."'>".$row['apellido'].", ".$row['nombre']." - DNI: ".$row['dni']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Medicamentos</label>
                            <textarea name="medicamentos" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Indicaciones</label>
                            <textarea name="indicaciones" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Tamaño de Receta</label>
                            <select name="tamano_receta" class="form-control" required>
                                <option value="completa">Página Completa</option>
                                <option value="media">Media Página</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar e Imprimir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    <script>
        // Función de búsqueda
        $(document).ready(function() {
            $("#searchReceta").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>
