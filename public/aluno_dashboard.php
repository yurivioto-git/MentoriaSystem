<?php
require_once __DIR__ . '/../src/bootstrap.php';

require_auth();
if (is_admin()) {
    header('Location: admin_dashboard.php'); // Redireciona admin para o dashboard correto
    exit();
}

use App\Models\Hora;
use App\Models\Apendice5;
use App\Controllers\HorasController;

$horaModel = new Hora();
$horasController = new HorasController();

// Lógica para POST requests (criar ou deletar horas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    if ($action === 'create_hora') {
        $horasController->create();
    } elseif ($action === 'delete_hora') {
        $horasController->delete((int)$_POST['hora_id']);
    }
    exit();
}

$pageTitle = 'Dashboard do Aluno';
include_once PROJECT_ROOT . '/src/Views/header.php';

if (isset($_GET['error'])) {
    echo '<div class="container mt-3"><div class="alert alert-danger">' . htmlspecialchars(urldecode($_GET['error'])) . '</div></div>';
}
if (isset($_GET['success'])) {
    echo '<div class="container mt-3"><div class="alert alert-success">' . htmlspecialchars(urldecode($_GET['success'])) . '</div></div>';
}

$userId = $_SESSION['user_id'];

// Filtros
$filter_bimestre = isset($_GET['bimestre']) ? (int)$_GET['bimestre'] : null;
$filter_ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

$filters = [];
if ($filter_bimestre) $filters['bimestre'] = $filter_bimestre;
if ($filter_ano) $filters['ano'] = $filter_ano;

$minhasHoras = $horaModel->findByUserId($userId, $filters);
$totalHoras = array_reduce($minhasHoras, fn($sum, $h) => $sum + $h['quantidade_horas'], 0);

$tiposPermitidos = ['Palestras', 'Visitas Técnicas', 'Mentoria', 'Eventos Científicos', 'Outros'];

?>

<div class="row">
    <div class="col-md-12">
        <h3>Apêndice 5</h3>
        <a href="enviar_apendice5.php" class="btn btn-primary mb-3">Enviar Apêndice 5</a>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Bimestre</th>
                        <th>Data de Envio</th>
                        <th>Status</th>
                        <th>Observações do Admin</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $apendice5Model = new Apendice5();
                    $apendice5Submissions = $apendice5Model->findByUserId($userId);
                    if (empty($apendice5Submissions)):
                    ?>
                        <tr><td colspan="5" class="text-center">Nenhum envio de Apêndice 5 encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($apendice5Submissions as $submission): ?>
                            <tr>
                                <td><?php echo e($submission['bimestre_ref']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($submission['submission_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo strtolower($submission['status']) === 'aprovado' ? 'success' : (strtolower($submission['status']) === 'rejeitado' ? 'danger' : 'warning'); ?>">
                                        <?php echo e($submission['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo e($submission['admin_notes'] ?? ''); ?></td>
                                <td>
                                    <a href="download_apendice5.php?id=<?php echo $submission['id']; ?>" class="btn btn-info btn-sm">Baixar</a>
                                    <form action="delete_apendice5.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este envio?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <!-- Coluna para Lançar Horas -->
    <div class="col-md-4">
        <h3>Lançar Nova Atividade</h3>
        <div class="card">
            <div class="card-body">
                <form action="aluno_dashboard.php?action=create_hora" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Atividade</label>
                        <select class="form-select" id="tipo" name="tipo" required onchange="toggleComprovante(this.value)">
                            <option value="">Selecione...</option>
                            <?php foreach ($tiposPermitidos as $tipo): ?>
                                <option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control" id="data" name="data" required>
                    </div>

                    <div class="mb-3">
                        <label for="quantidade_horas" class="form-label">Horas</label>
                        <input type="number" step="0.01" class="form-control" id="quantidade_horas" name="quantidade_horas" required>
                    </div>

                    <div class="mb-3">
                        <label for="bimestre" class="form-label">Bimestre</label>
                        <select class="form-select" id="bimestre" name="bimestre" required>
                            <option value="1">1º Bimestre</option>
                            <option value="2">2º Bimestre</option>
                            <option value="3">3º Bimestre</option>
                            <option value="4">4º Bimestre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição (Opcional)</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                    </div>

                    <div class="mb-3" id="campo-comprovante" style="display: none;">
                        <label for="comprovante" class="form-label">Comprovante (Obrigatório para 'Outros')</label>
                        <input class="form-control" type="file" id="comprovante" name="comprovante">
                        <div class="form-text">Permitido: JPG, PNG, PDF (Máx: <?php echo $_ENV['MAX_UPLOAD_MB']; ?>MB)</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Lançar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Coluna para Listar Horas -->
    <div class="col-md-8">
        <h3>Minhas Horas Lançadas</h3>
        <p><strong>Total de horas: <?php echo number_format($totalHoras, 2, ',', '.'); ?></strong></p>

        <!-- Formulário de Filtro -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <select name="bimestre" class="form-select">
                    <option value="">Todos os Bimestres</option>
                    <option value="1" <?php echo ($filter_bimestre == 1) ? 'selected' : ''; ?>>1º Bimestre</option>
                    <option value="2" <?php echo ($filter_bimestre == 2) ? 'selected' : ''; ?>>2º Bimestre</option>
                    <option value="3" <?php echo ($filter_bimestre == 3) ? 'selected' : ''; ?>>3º Bimestre</option>
                    <option value="4" <?php echo ($filter_bimestre == 4) ? 'selected' : ''; ?>>4º Bimestre</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="ano" class="form-control" placeholder="Ano" value="<?php echo e($filter_ano); ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary">Filtrar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Data</th>
                        <th>Horas</th>
                        <th>Bimestre</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($minhasHoras)): ?>
                        <tr><td colspan="6" class="text-center">Nenhum lançamento encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($minhasHoras as $hora): ?>
                            <tr>
                                <td>
                                    <?php echo e($hora['tipo']); ?>
                                    <?php if ($hora['tipo'] === 'Outros' && !empty($hora['comprovante_path'])): ?>
                                        <a href="download.php?id=<?php echo $hora['id']; ?>" title="Baixar Comprovante"><i class="bi bi-paperclip"></i></a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($hora['data'])); ?></td>
                                <td><?php echo number_format((float)$hora['quantidade_horas'], 2, ',', '.'); ?></td>
                                <td><?php echo e($hora['bimestre']); ?>º</td>
                                <td>
                                    <span class="badge bg-<?php echo strtolower($hora['status']) === 'aprovado' ? 'success' : (strtolower($hora['status']) === 'rejeitado' ? 'danger' : 'warning'); ?>">
                                        <?php echo e($hora['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="aluno_dashboard.php?action=delete_hora" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este lançamento?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="hora_id" value="<?php echo $hora['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleComprovante(tipo) {
    const campo = document.getElementById('campo-comprovante');
    const input = document.getElementById('comprovante');
    if (tipo === 'Outros') {
        campo.style.display = 'block';
        input.required = true;
    } else {
        campo.style.display = 'none';
        input.required = false;
    }
}
</script>

<?php
include_once PROJECT_ROOT . '/src/Views/footer.php';
?>