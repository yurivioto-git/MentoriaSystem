<?php
require_once __DIR__ . '../../header.php';
?>

<div class="container">
    <h2>Enviar Apêndice 5</h2>

    <form action="upload_apendice5.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="bimestre">Bimestre de Referência:</label>
            <select class="form-control" id="bimestre" name="bimestre" required>
                <option value="">Selecione o Bimestre</option>
                <?php
                $currentYear = date('Y');
                $bimesters = [
                    1 => "1º Bimestre",
                    2 => "2º Bimestre",
                    3 => "3º Bimestre",
                    4 => "4º Bimestre",
                ];
                foreach ($bimesters as $num => $name) {
                    $value = "{$currentYear}-{$num}";
                    echo "<option value=\"{$value}\">{$name} / {$currentYear}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="apendice5_file">Selecione o arquivo (.doc, .docx):</label>
            <input type="file" class="form-control-file" id="apendice5_file" name="apendice5_file" accept=".doc,.docx" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

<?php
require_once __DIR__ . '../../footer.php';
?>