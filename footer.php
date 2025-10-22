<?php
// footer.php
?>
<link rel="stylesheet" href="styles.css">

<footer class="footer">
    <button class="to-top" aria-label="Back to top" title="Back to top">▲</button>
    <span class="copyright">© COMPANY Licensing Authority - <span id="year"></span></span>
</footer>

<script>
// Dynamically set the year
document.getElementById('year').textContent = new Date().getFullYear();

// Back-to-top button functionality
document.querySelector('.to-top').addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

