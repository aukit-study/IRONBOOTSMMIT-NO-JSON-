<?php include 'header.php'; ?>

<h1>Size Chart</h1>

<table class="size-chart-table">
    <thead>
        <tr>
            <?php foreach (array_keys($size_chart[0]) as $header): ?>
                <th><?php echo $header; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($size_chart as $row): ?>
            <tr>
                <?php foreach ($row as $size): ?>
                    <td><?php echo $size; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
.size-chart-table { width: 80%; margin: 20px auto; border-collapse: collapse; text-align: center; }
.size-chart-table th, .size-chart-table td { border: 1px solid #ccc; padding: 12px; }
.size-chart-table thead { background-color: #f2f2f2; }
</style>

<?php include 'footer.php'; ?>