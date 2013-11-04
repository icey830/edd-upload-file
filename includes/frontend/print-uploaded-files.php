<?php

function edd_fu_confirmation_print_uploaded_files( $payment, $edd_receipt_args ) {

	// Print uploaded files
	EDD_FU_File_Manager::instance()->print_uploaded_files( $payment->ID );

}

// Print uploaded files at receipt page
add_action( 'edd_payment_receipt_after_table', 'edd_fu_confirmation_print_uploaded_files', 11, 2 );
