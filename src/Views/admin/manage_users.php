<h3>Gerenciar Alunos</h3>

<!-- Botão para abrir modal de novo aluno -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createUserModal">
    <i class="bi bi-plus-circle"></i> Novo Aluno
</button>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome</th>
                <th>RM</th>
                <th>Série</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunos as $aluno): ?>
                <tr>
                    <td><?php echo e($aluno['nome']); ?></td>
                    <td><?php echo e($aluno['rm']); ?></td>
                    <td><?php echo e($aluno['serie']); ?>ª</td>
                    <td>
                        <span class="badge bg-<?php echo $aluno['active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $aluno['active'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </td>
                    <td>
                        <!-- Botão para editar -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal-<?php echo $aluno['id']; ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <!-- Botão para excluir -->
                        <form action="?view=alunos&action=delete_user" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza? Todos os lançamentos deste aluno também serão excluídos.');">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $aluno['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                <!-- Modal de Edição de Aluno -->
                <div class="modal fade" id="editUserModal-<?php echo $aluno['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Aluno</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="?view=alunos&action=update_user" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $aluno['id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" name="nome" class="form-control" value="<?php echo e($aluno['nome']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">RM</label>
                                        <input type="text" name="rm" class="form-control" value="<?php echo e($aluno['rm']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Série</label>
                                        <select name="serie" class="form-select" required>
                                            <option value="1" <?php echo ($aluno['serie'] == 1) ? 'selected' : ''; ?>>1ª</option>
                                            <option value="2" <?php echo ($aluno['serie'] == 2) ? 'selected' : ''; ?>>2ª</option>
                                            <option value="3" <?php echo ($aluno['serie'] == 3) ? 'selected' : ''; ?>>3ª</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                                        <input type="password" name="senha" class="form-control">
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="active" id="active-<?php echo $aluno['id']; ?>" <?php echo $aluno['active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="active-<?php echo $aluno['id']; ?>">Ativo</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Criação de Aluno -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Aluno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="?view=alunos&action=create_user" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="role" value="aluno">

                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RM</label>
                        <input type="text" name="rm" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Série</label>
                        <select name="serie" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="1">1ª</option>
                            <option value="2">2ª</option>
                            <option value="3">3ª</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Aluno</button>
                </div>
            </form>
        </div>
    </div>
</div>
