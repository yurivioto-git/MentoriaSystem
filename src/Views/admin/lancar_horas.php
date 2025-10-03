<h3>Lançar Horas para um Aluno</h3>

<div class="card">
    <div class="card-body">
        <form action="admin_dashboard.php?action=create_hora" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label for="aluno_id" class="form-label">Aluno</label>
                <select class="form-select" id="aluno_id" name="aluno_id" required>
                    <option value="">Selecione um aluno...</option>
                    <?php foreach ($alunos as $aluno): ?>
                        <option value="<?php echo $aluno['id']; ?>"><?php echo e($aluno['nome']); ?> (RM: <?php echo e($aluno['rm']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo de Atividade</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="">Selecione...</option>
                    <option value="Palestras">Palestras</option>
                    <option value="Visitas Técnicas">Visitas Técnicas</option>
                    <option value="Mentoria">Mentoria</option>
                    <option value="Eventos Científicos">Eventos Científicos</option>
                    <option value="Estágios">Estágios</option>
                    <option value="Outros">Outros (sem comprovante)</option>
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
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Lançar Horas</button>
        </form>
    </div>
</div>
