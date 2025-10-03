<h3>Gerar Relatórios</h3>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Exportar Lançamentos em CSV</h5>
        <p class="card-text">Selecione os filtros desejados para gerar o relatório. Se nenhum filtro for selecionado, todos os registros serão exportados.</p>
        
        <form action="admin_dashboard.php" method="GET" target="_blank">
            <input type="hidden" name="view" value="relatorios">
            <input type="hidden" name="action" value="generate_report">

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="report_aluno_id" class="form-label">Aluno</label>
                    <select name="aluno_id" id="report_aluno_id" class="form-select">
                        <option value="">Todos</option>
                        <?php 
                        // Assumindo que $userModel foi instanciado no dashboard principal
                        $allAlunos = (new \App\Models\User())->getAll('aluno');
                        foreach ($allAlunos as $aluno): ?>
                            <option value="<?php echo $aluno['id']; ?>">
                                <?php echo e($aluno['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="report_serie" class="form-label">Série</label>
                    <select name="serie" id="report_serie" class="form-select">
                        <option value="">Todas</option>
                        <option value="1">1ª Série</option>
                        <option value="2">2ª Série</option>
                        <option value="3">3ª Série</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="report_tipo" class="form-label">Tipo de Atividade</label>
                    <select name="tipo" id="report_tipo" class="form-select">
                        <option value="">Todos</option>
                        <option value="Palestras">Palestras</option>
                        <option value="Visitas Técnicas">Visitas Técnicas</option>
                        <option value="Mentoria">Mentoria</option>
                        <option value="Eventos Científicos">Eventos Científicos</option>
                        <option value="Estágios">Estágios</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="report_data_inicio" class="form-label">Data de Início</label>
                    <input type="date" name="data_inicio" id="report_data_inicio" class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="report_data_fim" class="form-label">Data Final</label>
                    <input type="date" name="data_fim" id="report_data_fim" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-3">
                <i class="bi bi-download"></i> Gerar e Baixar CSV
            </button>
        </form>
    </div>
</div>