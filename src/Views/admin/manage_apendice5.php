<h2>Gerenciar Apêndice 5</h2>

<form action="apendice5_actions.php" method="GET" class="row g-3 mb-4">
    <input type="hidden" name="view" value="apendice5">
    <div class="col-md-4">
        <select name="bimestre_ap5" class="form-select">
            <option value="">Todos os Bimestres</option>
            <?php foreach ($distinctBimestres as $bimestre): ?>
                <?php
                $parts = explode('-', $bimestre);
                $year = $parts[0];
                $bimester_num = $parts[1];
                $displayText = "{$bimester_num}º Bimestre / {$year}";
                ?>
                <option value="<?php echo e($bimestre); ?>" <?php echo ($filter_bimestre_ap5 === $bimestre) ? 'selected' : ''; ?>>
                    <?php echo e($displayText); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="status_ap5" value="pendente" id="statusPendente" <?php echo ($filter_status_ap5 === 'pendente') ? 'checked' : ''; ?>>
            <label class="form-check-label" for="statusPendente">
                Mostrar somente pendentes
            </label>
        </div>
    </div>
    <div class="col-md-8">
        <button type="submit" class="btn btn-secondary" name="action" value="filter">Filtrar</button>
        <button type="submit" class="btn btn-primary" name="action" value="download_all">Baixar Todos</button>
    </div>
</form>

    <table class="table">
        <thead>
            <tr>
                <th>Aluno</th>
                <th>Bimestre</th>
                <th>Data de Envio</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $submission) : ?>
                <tr>
                    <td><?= htmlspecialchars($submission['user_name']) ?></td>
                    <td><?= htmlspecialchars($submission['bimestre_ref']) ?></td>
                    <td><?= htmlspecialchars($submission['submission_date']) ?></td>
                    <td><?= htmlspecialchars($submission['status']) ?></td>
                    <td>
                        <a href="download_apendice5.php?id=<?= $submission['id'] ?>" class="btn btn-info btn-sm">Baixar</a>
                        
                        <!-- Botão de Aprovação Rápida -->
                        <form action="validate_apendice5.php" method="post" style="display: inline-block; margin-right: 5px;">
                            <input type="hidden" name="id" value="<?= $submission['id'] ?>">
                            <input type="hidden" name="status" value="aprovado">
                            <button type="submit" class="btn btn-success btn-sm">Aprovar</button>
                        </form>

                        <form action="validate_apendice5.php" method="post" style="display: inline-block;">
                            <input type="hidden" name="id" value="<?= $submission['id'] ?>">
                            <select name="status" class="form-control-sm">
                                <option value="aprovado">Aprovar</option>
                                <option value="rejeitado">Rejeitar</option>
                            </select>
                            <input type="text" name="admin_notes" placeholder="Observações" class="form-control-sm">
                            <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>