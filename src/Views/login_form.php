<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Login</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>
                <?php if (!empty($success) && $success === 'registered'): ?>
                    <div class="alert alert-success">Cadastro realizado com sucesso! Faça o login.</div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label for="rm" class="form-label">RM (Registro de Matrícula)</label>
                        <input type="text" class="form-control" id="rm" name="rm" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p>Não tem uma conta? <a href="register.php">Registre-se aqui</a>.</p>
            </div>
        </div>
    </div>
</div>
