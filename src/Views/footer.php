</main>

<footer class="bg-dark text-white text-center p-3 mt-5">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Etec Jales. Todos os direitos reservados.</p>
        <p>Desenvolvido por Yuri André Vioto Silva &reg;</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>
</html>
<?php
// Envia o conteúdo do buffer para o navegador
ob_end_flush();
?>