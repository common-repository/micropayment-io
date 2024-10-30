<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<div class="billingfox-help-shortcode">';
echo '<h2>'.__('Transactions', BILLING_FOX_TRANSLATE).'</h2>';
echo '<table>';
foreach ($list as $row) {
    echo '<tr>';
    echo '<td>'.date('Y-m-d H:i', strtotime($row['spent_at'])).'</td>';
    echo '<td>'.$row['description'].'</td>';
    echo '<td>'.number_format($row['amount'], 2).'</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';