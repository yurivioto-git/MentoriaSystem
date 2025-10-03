<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_admin();

use App\Controllers\AdminController;
use App\Controllers\HorasController;
use App\Models\User;
use App\Models\Hora;

// Primeiro, verificar se é uma ação de geração de relatório
if (isset($_GET['action']) && $_GET['action'] === 'generate_report') {
    $filters = [
        'aluno_id' => $_GET['aluno_id'] ?? null,
        'tipo' => $_GET['tipo'] ?? null,
        'data_inicio' => $_GET['data_inicio'] ?? null,
        'data_fim' => $_GET['data_fim'] ?? null,
        'serie' => $_GET['serie'] ?? null,
    ];
    $filters = array_filter($filters);
    (new AdminController())->generateReport($filters);
    exit(); // Interrompe a execução para não renderizar o HTML
}

$adminController = new AdminController();
$horasController = new HorasController();
$userModel = new User();
$horaModel = new Hora();

// Roteamento de ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    if (str_starts_with($action, 'user')) {
        $adminController->handleAction();
    } else {
        // Ações de horas (criar, editar, deletar)
        switch($action) {
            case 'create_hora':
                $horasController->create();
                break;
            case 'update_hora':
                $horasController->update((int)$_POST['hora_id']);
                break;
            case 'delete_hora':
                $horasController->delete((int)$_POST['hora_id']);
                break;
            case 'update_hora_status':
                $adminController->handleAction();
                break;
        }
    }
    exit();
}

// Roteamento de views GET
$view = $_GET['view'] ?? 'alunos'; // Visão padrão: gerenciar alunos
$pageTitle = 'Dashboard do Administrador';
include_once PROJECT_ROOT . '/src/Views/header.php';

?>

<div class="d-flex align-items-start">
    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <a class="nav-link <?php echo ($view === 'alunos') ? 'active' : ''; ?>" href="?view=alunos">Gerenciar Alunos</a>
        <a class="nav-link <?php echo ($view === 'horas') ? 'active' : ''; ?>" href="?view=horas">Gerenciar Horas</a>
        <a class="nav-link <?php echo ($view === 'lancar') ? 'active' : ''; ?>" href="?view=lancar">Lançar Horas</a>
        <a class="nav-link <?php echo ($view === 'relatorios') ? 'active' : ''; ?>" href="?view=relatorios">Relatórios</a>
    </div>

    <div class="tab-content flex-grow-1" id="v-pills-tabContent">
        <!-- Visão de Gerenciar Alunos -->
        <div class="tab-pane fade <?php echo ($view === 'alunos') ? 'show active' : ''; ?>">
            <?php 
            $alunos = $userModel->getAll('aluno');
            include PROJECT_ROOT . '/src/Views/admin/manage_users.php'; 
            ?>
        </div>

        <!-- Visão de Gerenciar Horas -->
        <div class="tab-pane fade <?php echo ($view === 'horas') ? 'show active' : ''; ?>">
            <?php
            $alunos = $userModel->getAll('aluno');
            $filters = [
                'aluno_id' => $_GET['aluno_id'] ?? null,
                'tipo' => $_GET['tipo'] ?? null,
                'data_inicio' => $_GET['data_inicio'] ?? null,
                'data_fim' => $_GET['data_fim'] ?? null,
                'serie' => $_GET['serie'] ?? null,
                'status' => $_GET['status'] ?? null,
            ];
            // Remove filtros vazios
            $filters = array_filter($filters);
            $horas = $horaModel->findAllWithDetails($filters);
            include PROJECT_ROOT . '/src/Views/admin/manage_horas.php';
            ?>
        </div>

        <!-- Visão de Lançar Horas (para um aluno) -->
        <div class="tab-pane fade <?php echo ($view === 'lancar') ? 'show active' : ''; ?>">
            <?php 
            $alunos = $userModel->getAll('aluno', true);
            include PROJECT_ROOT . '/src/Views/admin/lancar_horas.php'; 
            ?>
        </div>

        <!-- Visão de Relatórios -->
        <div class="tab-pane fade <?php echo ($view === 'relatorios') ? 'show active' : ''; ?>">
            <?php include PROJECT_ROOT . '/src/Views/admin/reports.php'; ?>
        </div>
    </div>
</div>

<?php
include_once PROJECT_ROOT . '/src/Views/footer.php';
?>
