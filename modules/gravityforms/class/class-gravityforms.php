<?php
/**
 * Gravity Forms Integration for WPshop
 *
 * @package WPshop
 */

namespace wpshop;

use eoxia\Singleton_Util;

defined( 'ABSPATH' ) || exit;

/**
 * Gravity Forms Integration Class
 */
class GravityForms extends Singleton_Util {

	/**
	 * Initialize hooks
	 */
	public function construct() {
		add_filter( 'gform_entry_list_columns', array( $this, 'add_easycrm_column' ) );
		add_filter( 'gform_entries_column_filter', array( $this, 'populate_easycrm_column' ), 10, 4 );
		add_filter( 'gform_entry_list_bulk_actions', array( $this, 'add_bulk_action' ), 10, 2 );
		add_action( 'gform_entry_list_action', array( $this, 'handle_bulk_action' ), 10, 3 );
	}

	/**
	 * Add EasyCRM status column to entry list
	 */
	public function add_easycrm_column( $columns ) {
		$position = count( $columns ) - 1;

		return array_slice( $columns, 0, $position, true )
			+ array( 'easycrm' => 'Ajout EasyCRM' )
			+ array_slice( $columns, $position, null, true );
	}

	/**
	 * Populate EasyCRM status column
	 */
	public function populate_easycrm_column( $value, $form_id, $field_id, $entry ) {
		if ( $field_id === 'easycrm' ) {
			if ( ! empty( gform_get_meta( $entry['id'], 'easycrm_project_id' ) ) ) {
				return '<input type="checkbox" checked disabled style="margin-right: 5px; vertical-align: middle;"><span style="vertical-align: middle;">Importé</span>';
			} else {
				return '<input type="checkbox" disabled style="margin-right: 5px; vertical-align: middle;"><span style="vertical-align: middle;">Non importé</span>';
			}
		}
		return $value;
	}

	/**
	 * Add custom bulk action
	 */
	public function add_bulk_action( $actions, $form_id ) {
		return array_merge( array( 'send_dolibarr' => 'Envoyer dans Dolibarr' ), $actions );
	}

	/**
	 * Handle bulk action execution
	 */
	public function handle_bulk_action( $action, $entries, $form_id ) {
		if ( $action === 'send_dolibarr' ) {
			$form = \GFAPI::get_form( $form_id );

			$fields = array();

			foreach ( $form['fields'] as $field ) {
				if ( ! empty( $field->inputs ) ) {
					foreach ( $field->inputs as $input ) {
						$fields[ $input['id'] ] = $input['label'];
					}
				} else {
					$fields[ $field->id ] = $field->label;
				}
			}

			$projects = array();
			$success_count = 0;
			$error_count = 0;
			$errors = array();

			foreach ( $entries as $entry_id ) {
				$entry = \GFAPI::get_entry( $entry_id );
				$test_meta = gform_get_meta( $entry_id, 'easycrm_project_id' );

				if ( ! empty( $test_meta ) ) {
					continue; // Skip entries with test_meta
				}

				$projects[ $entry_id ] = array();

				foreach ( $fields as $field_id => $field_label ) {
					if ( isset( $entry[ $field_id ] ) ) {
						$projects[ $entry_id ][ $field_label ] = $entry[ $field_id ];
					}
				}

				$result = Request_Util::post(
					'easycrm/createProject',
					array(
						'title'     => $projects[ $entry_id ]['Société'],
						'lastname'  => $projects[ $entry_id ]['Nom'],
						'firstname' => $projects[ $entry_id ]['Prénom'],
						'email'     => $projects[ $entry_id ]['E-mail'],
						'phone'     => $projects[ $entry_id ]['Téléphone'],
					)
				);

				if ( $result == false ) {
					$error_count++;
					$errors[] = 'Entrée ID ' . $entry_id . ': Erreur lors de l\'envoi vers Dolibarr.';
					error_log( 'Error sending project to Dolibarr for entry ID ' . $entry_id );
				} else {
					$success_count++;
					gform_update_meta( $entry_id, 'easycrm_project_id', $result->project_id );
				}
			}

			// Rediriger avec les messages de feedback
			$redirect_url = add_query_arg(
				array(
					'success_count' => $success_count,
					'error_count'   => $error_count,
					'errors'        => $error_count > 0 ? base64_encode( json_encode( $errors ) ) : '',
				),
				wp_get_referer()
			);

			wp_redirect( $redirect_url );
			exit;
		}
	}
}

GravityForms::g();