<?php
/*
Plugin Name: Gravity Forms PDF Integration
Description: Generate PDF from Gravity Form submissions, save to database, and display in admin menu.
Version: 1.0
Author: Your Name
*/

// Enqueue PDF generation library (TCPDF or FPDF)

// Add a menu item to the admin menu
function gf_pdf_integration_menu() {
    add_menu_page('PDF Submissions', 'PDF Submissions', 'manage_options', 'pdf-submissions', 'gf_pdf_submission_page');
}

add_action('admin_menu', 'gf_pdf_integration_menu');

// Display PDF submissions in the admin menu
function gf_pdf_submission_page() {
    // Retrieve PDF submissions from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_submissions';
    $pdf_submissions = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h2>PDF Submissions</h2>';
    echo '<table class="wp-list-table widefat fixed">';
    echo '<thead><tr><th>ID</th><th>Submission Date</th><th>PDF Link</th></tr></thead>';
    echo '<tbody>';

    foreach ($pdf_submissions as $submission) {
        echo '<tr>';
        echo '<td>' . $submission->id . '</td>';
        echo '<td>' . $submission->submission_date . '</td>';
        echo '<td><a href="' . $submission->pdf_url . '" target="_blank">View PDF</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

// Create a Gravity Forms submission hook
add_action('gform_after_submission', 'generate_pdf_from_submission', 10, 2);

function generate_pdf_from_submission($entry, $form) {
    // Generate PDF using your chosen library (TCPDF or FPDF)

    // Get the Gravity Forms entry ID
    $entry_id = $entry['id'];

    // Set the PDF file name with entry ID
    $pdf_file_name = 'submission_' . $entry_id . '.pdf';

    // Set the path where you want to save the PDFs
    $pdf_file_path = plugin_dir_path(__FILE__) . 'pdfs/' . $pdf_file_name;

    // Save PDF to the specified path
    // Replace this with your PDF generation code
    // Example using TCPDF:
    // $pdf = new TCPDF();
    // ... PDF generation code ...
    // $pdf->Output($pdf_file_path, 'F');

    // Save PDF to the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_submissions';
    $wpdb->insert(
        $table_name,
        array(
            'submission_date' => current_time('mysql'),
            'pdf_url' => $pdf_file_path,
        )
    );
}

// Activate and deactivate plugin

function plugin_activation() {
    // Create database table for PDF submissions
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_submissions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        submission_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        pdf_url text NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function plugin_deactivation() {
    // Remove the database table on deactivation
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_submissions';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

register_activation_hook(__FILE__, 'plugin_activation');
register_deactivation_hook(__FILE__, 'plugin_deactivation');
