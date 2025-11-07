<?php
if (!isset($current_page)) $current_page = 1;
if (!isset($total_pages)) $total_pages = 1;
if (!isset($base_url)) $base_url = '';
?>

<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Previous Page -->
        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $current_page > 1 ? $base_url . '?page=' . ($current_page - 1) : '#'; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
        
        <!-- Page Numbers -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)): ?>
                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $base_url . '?page=' . $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php elseif ($i == $current_page - 3 || $i == $current_page + 3): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php endif; ?>
        <?php endfor; ?>
        
        <!-- Next Page -->
        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo $current_page < $total_pages ? $base_url . '?page=' . ($current_page + 1) : '#'; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>