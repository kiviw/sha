<?php
/**
 * Plugin Name: Manual Deposit and KSH Disbursement
 * Plugin URI: Your plugin website URL
 * Description: A simple plugin for manual deposit confirmation and KSH disbursement.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: Your website URL
 * Text Domain: manual-deposit-disbursement
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Create custom database table on plugin activation
function create_deposit_requests_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'deposit_requests';

    // Check if the table exists, if not, create it
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            deposit_address varchar(255) NOT NULL,
            amount_in_wld float NOT NULL,
            phone varchar(255) NOT NULL,
            mpesa_name varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'Unconfirmed',
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'create_deposit_requests_table');

// Register shortcode to display the deposit form
function manual_deposit_form_shortcode() {
    ob_start();
    $deposit_address = '0xbe5d9b4f0b61ed76bbfa821ea465e0c4179f0684'; // Replace this with the actual deposit address

    ?>
    <div class="manual-deposit-form">
        <p>Copy the WLD deposit address below and use it to send your WLD from an external wallet:</p>
        <div class="deposit-address"><?php echo $deposit_address; ?></div>

        <form method="post">
            <label for="amount_in_wld">Amount in WLD Sent:</label>
            <input type="number" id="amount_in_wld" name="amount_in_wld" min="0" step="1" required />

            <label for="phone">Your Phone Number (MPESA):</label>
            <input type="text" id="phone" name="phone" required />

            <label for="mpesa_name">Your MPESA Name:</label>
            <input type="text" id="mpesa_name" name="mpesa_name" required />

            <input type="submit" value="Submit Deposit Request" name="submit_deposit" />
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('manual_deposit_form', 'manual_deposit_form_shortcode');

// Process deposit form submission
function process_deposit_submission() {
    if (isset($_POST['submit_deposit'])) {
        $deposit_address = '0xbe5d9b4f0b61ed76bbfa821ea465e0c4179f0684'; // Replace this with the actual deposit address
        $amount_in_wld = isset($_POST['amount_in_wld']) ? floatval($_POST['amount_in_wld']) : 0;
        $phone_number = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $mpesa_name = isset($_POST['mpesa_name']) ? sanitize_text_field($_POST['mpesa_name']) : '';

        if ($amount_in_wld <= 0 || empty($phone_number) || empty($mpesa_name)) {
            echo '<p>Invalid input. Please enter valid WLD amount, phone number, and MPESA name.</p>';
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'deposit_requests';

        $data = array(
            'deposit_address' => $deposit_address,
            'amount_in_wld' => $amount_in_wld,
            'phone' => $phone_number,
            'mpesa_name' => $mpesa_name,
        );

        $wpdb->insert($table_name, $data);

        // For this example, we'll just show a success message
        echo '<p>Your deposit request has been submitted. Please wait for confirmation.</p>';
    }
}
add_action('init', 'process_deposit_submission');

// Admin page to manage deposit requests
function manual_deposit_admin_page() {
    ?>
    <div class="wrap">
        <h1>Deposit Requests</h1>
        <?php
        // Add your custom code here to display and manage deposit requests
        // You can display a table of pending requests, manually confirm deposits, and disburse KSH
        // Update the database accordingly once you confirm and disburse KSH
        ?>
    </div>
    <?php
}

// Hook the admin page function to an action
add_action('admin_menu', 'register_manual_deposit_admin_page');

// Register the admin page
function register_manual_deposit_admin_page() {
    add_menu_page(
        'Deposit Requests',
        'Deposit Requests',
        'manage_options',
        'manual_deposit_admin',
        'manual_deposit_admin_page',
        'dashicons-money',
        30
    );
}

// Register shortcode to display the deposit transactions table for admin
function manual_deposit_transactions_shortcode() {
    ob_start();
    global $wpdb;
    $table_name = $wpdb->prefix . 'deposit_requests';
    $transactions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if ($transactions) {
        echo '<table class="manual-deposit-transactions">';
        echo '<thead><tr><th>Transaction ID</th><th>Deposit Address</th><th>Amount in WLD</th><th>Phone Number</th><th>MPESA Name</th><th>Status</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        foreach ($transactions as $transaction) {
            echo '<tr>';
            echo '<td>' . $transaction->id . '</td>';
            echo '<td>' . $transaction->deposit_address . '</td>';
            echo '<td>' . $transaction->amount_in_wld . '</td>';
            echo '<td>' . $transaction->phone . '</td>';
            echo '<td>' . $transaction->mpesa_name . '</td>';
            echo '<td>' . $transaction->status . '</td>';
            echo '<td><a href="' . admin_url('admin.php?page=manual_deposit_admin&action=confirm&id=' . $transaction->id) . '">Confirm</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No deposit transactions found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('manual_deposit_transactions', 'manual_deposit_transactions_shortcode');

// Admin page actions
add_action('admin_init', 'manual_deposit_admin_actions');
function manual_deposit_admin_actions() {
    if (isset($_GET['page']) && $_GET['page'] === 'manual_deposit_admin') {
        if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $transaction_id = intval($_GET['id']);
                confirm_transaction($transaction_id);
            }
        }
    }
}

// Confirm transaction
function confirm_transaction($transaction_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'deposit_requests';

    $wpdb->update(
        $table_name,
        array('status' => 'Confirmed'),
        array('id' => $transaction_id),
        array('%s'),
        array('%d')
    );

    // Redirect to admin page after confirming the transaction
    wp_redirect(admin_url('admin.php?page=manual_deposit_admin'));
    exit;
}

// Register shortcode to display the deposit transactions table for public users
function manual_deposit_transactions_user_shortcode() {
    ob_start();
    global $wpdb;
    $table_name = $wpdb->prefix . 'deposit_requests';

    $transactions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if ($transactions) {
        echo '<style>.manual-deposit-transactions th:nth-child(4), .manual-deposit-transactions td:nth-child(4), .manual-deposit-transactions th:nth-child(5), .manual-deposit-transactions td:nth-child(5) { display: none; }</style>';
        echo '<table class="manual-deposit-transactions">';
        echo '<thead><tr><th>Transaction ID</th><th>Amount in WLD</th><th>MPESA Name</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        foreach ($transactions as $transaction) {
            echo '<tr>';
            echo '<td>' . $transaction->id . '</td>';
            echo '<td>' . $transaction->amount_in_wld . '</td>';
            echo '<td>' . $transaction->mpesa_name . '</td>';
            echo '<td>' . $transaction->status . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No deposit transactions found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('manual_deposit_transactions_user', 'manual_deposit_transactions_user_shortcode');
