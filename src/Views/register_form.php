<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Cadastro de Aluno</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label for="rm" class="form-label">RM (Será seu login)</label>
                        <input type="text" class="form-control" id="rm" name="rm" required>
                    </div>

                    <div class="mb-3">
                        <label for="serie" class="form-label">Série Atual</label>
                        <select class="form-select" id="serie" name="serie" required>
                            <option value="">Selecione sua série</option>
                            <option value="1">1º Ano</option>
                            <option value="2">2º Ano</option>
                            <option value="3">3º Ano</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>

                    <div class="mb-3">
                        <label for="senha_confirm" class="form-label">Confirmar Senha</label>
                        <input type="password" class="form-control" id="senha_confirm" name="senha_confirm" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p>Já tem uma conta? <a href="login.php">Faça o login</a>.</p>
            </div>
        </div>
    </div>
</div>
