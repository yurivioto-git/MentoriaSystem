<h3>Alterar Senha</h3>

<form action="?view=change_password&action=update_password" method="POST" class="card card-body">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <div class="mb-3">
        <label for="current_password" class="form-label">Senha Atual</label>
        <input type="password" name="current_password" id="current_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="new_password" class="form-label">Nova Senha</label>
        <input type="password" name="new_password" id="new_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Alterar Senha</button>
</form>
