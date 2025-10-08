<h3>Gerenciar Coordenadores</h3>

<!-- Botão para abrir modal de novo coordenador -->
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createCoordinatorModal">
    <i class="bi bi-plus-circle"></i> Novo Coordenador
</button>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome</th>
                <th>RM</th>
                <th>Curso</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coordinators as $coordinator): ?>
                <tr>
                    <td><?php echo e($coordinator['nome']); ?></td>
                    <td><?php echo e($coordinator['rm']); ?></td>
                    <td><?php echo e($coordinator['course_name']); ?></td>
                    <td>
                        <!-- Botão para editar -->
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCoordinatorModal-<?php echo $coordinator['id']; ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <!-- Botão para excluir -->
                        <form action="?view=coordinators&action=delete_user" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza?');">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $coordinator['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                <!-- Modal de Edição de Coordenador -->
                <div class="modal fade" id="editCoordinatorModal-<?php echo $coordinator['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Coordenador</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="?view=coordinators&action=update_user" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $coordinator['id']; ?>">
                                    <input type="hidden" name="view" value="coordinators">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nome</label>
                                        <input type="text" name="nome" class="form-control" value="<?php echo e($coordinator['nome']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">RM</label>
                                        <input type="text" name="rm" class="form-control" value="<?php echo e($coordinator['rm']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Curso</label>
                                        <select name="course_id" class="form-select" required>
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?php echo $course['id']; ?>" <?php echo ($coordinator['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                                    <?php echo e($course['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                                        <input type="password" name="senha" class="form-control">
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

<!-- Modal de Criação de Coordenador -->
<div class="modal fade" id="createCoordinatorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Coordenador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="?view=coordinators&action=create_user" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="role" value="coordinator">
                    <input type="hidden" name="view" value="coordinators">

                    <div class="mb-3">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RM</label>
                        <input type="text" name="rm" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Curso</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo e($course['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Coordenador</button>
                </div>
            </form>
        </div>
    </div>
</div>
