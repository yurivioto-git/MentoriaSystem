<h3>Gerenciar Lançamentos de Horas</h3>

<!-- Formulário de Filtro -->
<form method="GET" class="card card-body mb-4">
    <input type="hidden" name="view" value="horas">
    <div class="row g-3">
        <div class="col-md-3">
            <label for="filtro_aluno" class="form-label">Aluno</label>
            <select name="aluno_id" id="filtro_aluno" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo $aluno['id']; ?>" <?php echo (($_GET['aluno_id'] ?? '') == $aluno['id']) ? 'selected' : ''; ?>>
                        <?php echo e($aluno['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="filtro_tipo" class="form-label">Tipo</label>
            <select name="tipo" id="filtro_tipo" class="form-select">
                <option value="">Todos</option>
                <option value="Palestras">Palestras</option>
                <option value="Visitas Técnicas">Visitas Técnicas</option>
                <option value="Mentoria">Mentoria</option>
                <option value="Eventos Científicos">Eventos Científicos</option>
                <option value="Estágios">Estágios</option>
                <option value="Outros">Outros</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="filtro_serie" class="form-label">Série</label>
            <select name="serie" id="filtro_serie" class="form-select">
                <option value="">Todas</option>
                <option value="1">1ª</option>
                <option value="2">2ª</option>
                <option value="3">3ª</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="filtro_status" class="form-label">Status</label>
            <select name="status" id="filtro_status" class="form-select">
                <option value="">Todos</option>
                <option value="Pendente" <?php echo (($_GET['status'] ?? '') == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                <option value="Aprovado" <?php echo (($_GET['status'] ?? '') == 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                <option value="Rejeitado" <?php echo (($_GET['status'] ?? '') == 'Rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="data_inicio" class="form-label">De</label>
            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?php echo e($_GET['data_inicio'] ?? ''); ?>">
        </div>
        <div class="col-md-2">
            <label for="data_fim" class="form-label">Até</label>
            <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?php echo e($_GET['data_fim'] ?? ''); ?>">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Aluno</th>
                <th>Tipo</th>
                <th>Data</th>
                <th>Horas</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($horas)): ?>
                <tr><td colspan="6" class="text-center">Nenhum lançamento encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($horas as $hora): ?>
                    <tr>
                        <td><?php echo e($hora['aluno_nome']); ?></td>
                        <td>
                            <?php echo e($hora['tipo']); ?>
                            <?php if ($hora['tipo'] === 'Outros' && !empty($hora['comprovante_path'])): ?>
                                <a href="download.php?id=<?php echo $hora['id']; ?>" title="Baixar Comprovante"><i class="bi bi-paperclip"></i></a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($hora['data'])); ?></td>
                        <td><?php echo number_format((float)$hora['quantidade_horas'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo strtolower($hora['status']) === 'aprovado' ? 'success' : (strtolower($hora['status']) === 'rejeitado' ? 'danger' : 'warning'); ?>">
                                <?php echo e($hora['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewHoraModal-<?php echo $hora['id']; ?>">
                                <i class="bi bi-eye"></i>
                            </button>

                            <?php if ($hora['status'] === 'Pendente'): ?>
                                <form action="?view=horas&action=update_hora_status" method="POST" class="d-inline" onsubmit="return confirm('Aprovar este lançamento?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="hora_id" value="<?php echo $hora['id']; ?>">
                                    <input type="hidden" name="status" value="Aprovado">
                                    <button type="submit" class="btn btn-success btn-sm" title="Aprovar">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form action="?view=horas&action=delete_hora" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="hora_id" value="<?php echo $hora['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal de Visualização/Aprovação de Hora -->
                    <div class="modal fade" id="viewHoraModal-<?php echo $hora['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalhes do Lançamento</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="?view=horas&action=update_hora_status" method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="hora_id" value="<?php echo $hora['id']; ?>">

                                        <p><strong>Aluno:</strong> <?php echo e($hora['aluno_nome']); ?></p>
                                        <p><strong>Tipo:</strong> <?php echo e($hora['tipo']); ?></p>
                                        <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($hora['data'])); ?></p>
                                        <p><strong>Horas:</strong> <?php echo number_format((float)$hora['quantidade_horas'], 2, ',', '.'); ?></p>
                                        <p><strong>Descrição:</strong> <?php echo e($hora['descricao'] ?: 'N/A'); ?></p>
                                        
                                        <?php if (!empty($hora['comprovante_path'])): ?>
                                            <p><strong>Comprovante:</strong> <a href="download.php?id=<?php echo $hora['id']; ?>" target="_blank">Ver Arquivo</a></p>
                                        <?php endif; ?>

                                        <hr>

                                        <div class="mb-3">
                                            <label class="form-label"><strong>Aprovação</strong></label>
                                            <select name="status" class="form-select">
                                                <option value="Pendente" <?php echo ($hora['status'] === 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                                                <option value="Aprovado" <?php echo ($hora['status'] === 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                                                <option value="Rejeitado" <?php echo ($hora['status'] === 'Rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Feedback (se rejeitado)</label>
                                            <textarea name="feedback_admin" class="form-control"><?php echo e($hora['feedback_admin'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        <button type="submit" class="btn btn-primary">Salvar Status</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
