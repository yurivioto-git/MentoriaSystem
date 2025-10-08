<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_admin();

use App\Controllers\AdminController;
use App\Controllers\HorasController;
use App\Models\User;
use App\Models\Hora;
use App\Models\Apendice5;
use App\Models\Course;

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
$courseModel = new Course();

// Roteamento de ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    $user_actions = ['create_user', 'update_user', 'delete_user', 'update_hora_status'];

    if (in_array($action, $user_actions)) {
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
        }
    }
}

// Roteamento de views GET
$view = $_GET['view'] ?? 'alunos'; // Visão padrão: gerenciar alunos
$pageTitle = 'Dashboard do Administrador';
include_once PROJECT_ROOT . '/src/Views/header.php';

?>

<div class="d-flex align-items-start">
    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <a class="nav-link <?php echo ($view === 'alunos') ? 'active' : ''; ?>" href="?view=alunos">Gerenciar Alunos</a>
        <?php if (is_super_admin()): ?>
        <a class="nav-link <?php echo ($view === 'coordinators') ? 'active' : ''; ?>" href="?view=coordinators">Gerenciar Coordenadores</a>
        <?php endif; ?>
        <a class="nav-link <?php echo ($view === 'horas') ? 'active' : ''; ?>" href="?view=horas">Gerenciar Horas</a>
        <a class="nav-link <?php echo ($view === 'lancar') ? 'active' : ''; ?>" href="?view=lancar">Lançar Horas</a>
        <a class="nav-link <?php echo ($view === 'relatorios') ? 'active' : ''; ?>" href="?view=relatorios">Relatórios</a>
        <a class="nav-link <?php echo ($view === 'apendice5') ? 'active' : ''; ?>" href="?view=apendice5">Gerenciar Apêndice 5</a>
    </div>

    <div class="tab-content flex-grow-1" id="v-pills-tabContent">
        <!-- Visão de Gerenciar Alunos -->
        <div class="tab-pane fade <?php echo ($view === 'alunos') ? 'show active' : ''; ?>">
            <?php 
            $courses = $courseModel->getAll();
            $user = new \App\Models\User();
            $current_user = $user->findById($_SESSION['user_id']);
            $selected_course = $_GET['course_id'] ?? null;

            if (is_super_admin()) {
                $alunos = $userModel->getAll('student', null, $selected_course);
            } else {
                $alunos = $userModel->getAll('student', null, $current_user['course_id']);
            }
            
            include PROJECT_ROOT . '/src/Views/admin/manage_users.php'; 
            ?>
        </div>

        <!-- Visão de Gerenciar Coordenadores -->
        <?php if (is_super_admin()): ?>
        <div class="tab-pane fade <?php echo ($view === 'coordinators') ? 'show active' : ''; ?>">
            <?php 
            $coordinators = $userModel->getAll('coordinator');
            $courses = $courseModel->getAll();
            include PROJECT_ROOT . '/src/Views/admin/manage_coordinators.php'; 
            ?>
        </div>
        <?php endif; ?>

        <!-- Visão de Gerenciar Horas -->
        <div class="tab-pane fade <?php echo ($view === 'horas') ? 'show active' : ''; ?>">
            <?php
            $user = new \App\Models\User();
            $current_user = $user->findById($_SESSION['user_id']);

            if (is_super_admin()) {
                $alunos = $userModel->getAll('student');
            } else {
                $alunos = $userModel->getAll('student', null, $current_user['course_id']);
            }

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
            $user = new \App\Models\User();
            $current_user = $user->findById($_SESSION['user_id']);

            if (is_super_admin()) {
                $alunos = $userModel->getAll('student', true);
            } else {
                $alunos = $userModel->getAll('student', true, $current_user['course_id']);
            }
            
            include PROJECT_ROOT . '/src/Views/admin/lancar_horas.php'; 
            ?>
        </div>

        <!-- Visão de Relatórios -->
        <div class="tab-pane fade <?php echo ($view === 'relatorios') ? 'show active' : ''; ?>">
            <?php include PROJECT_ROOT . '/src/Views/admin/reports.php'; ?>
        </div>

        <!-- Visão de Gerenciar Apêndice 5 -->
        <div class="tab-pane fade <?php echo ($view === 'apendice5') ? 'show active' : ''; ?>">
            <?php
            $filter_bimestre_ap5 = $_GET['bimestre_ap5'] ?? null;
            $filter_status_ap5 = $_GET['status_ap5'] ?? null;
            $apendice5Model = new Apendice5();
            $submissions = $apendice5Model->getSubmissions($filter_bimestre_ap5, $filter_status_ap5);
            $distinctBimestres = $apendice5Model->getDistinctBimestres();
            include PROJECT_ROOT . '/src/Views/admin/manage_apendice5.php';
            ?>
        </div>
    </div>
</div>

<?php
include_once PROJECT_ROOT . '/src/Views/footer.php';
?>
