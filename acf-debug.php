<?php
/*
Plugin Name: Advanced Custom Fields Debug Fields
Plugin URI: #
Description: Ability to see all ACF field groups, labels and slugs
Version: 11.0
Author: MR
Author URI: #
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('acf_debug_fields') ) {
	class acf_debug_fields {
		
		function __construct() {
			add_action( 'init', array( $this, 'admin_menu' ) );
		}
		
		function admin_menu () {
			add_submenu_page( 'tools.php', 'ACF Debug', 'ACF Debug', 'manage_options', 'acf_debug', array( $this, 'admin_page' ) );
		}
		 
		function admin_page() {
?>
	<h2>ACF Debug</h2>
	<div class="row">
		<strong>Show:</strong>
		<input type="checkbox" checked="checked" /> Type 
		<input type="checkbox" checked="checked" /> Choices 
		<input type="checkbox" checked="checked" /> Instructions
	</div>
<?php
			if( ! class_exists('ACF') ) {
				echo "You don't appear to have Advanced Custom Fields installed!";
			} else {
				$groups = acf_get_field_groups();
			?>
			<style type="text/css">
				.postbox-container {width:98%}
				.acf_debug_field td {vertical-align:middle}
				.acf_debug_field tbody tr:nth-child(even) {background-color:#eee}
				.acf_debug_field tbody table tbody tr:nth-child(odd) {background-color:#eee}
				.acf_debug_field tbody table tbody tr:nth-child(even) {background-color:#FFF}
			</style>
			<div class="postbox-container" id="poststuff">
			<?php	
				if( $groups ) {
					foreach( $groups as $group ) {
	?>
				<div class='acf_debug_field postbox'>
					<h2 class='hdnle'><?php echo $group['title']; ?> - <a href='#'>(Edit)</a></h2>
					<div class='inside'>
					<?php
						// Locations (where ACF field groups are displayed)
						$locations = $group['location'];
						if( $locations ) {
							echo '<p><strong>Locations:</strong> <em>';
							foreach( $locations as $location ) {
								echo $location[0]['param'] . ' ' . $location[0]['operator'] . ' ' . $location[0]['value'] .', ';
							}
							echo '</em></p>';
						}
						
						// Get the fields for the group
						$fields = acf_get_fields( $group['key'] );
						
						if( !empty( $fields ) ) {
							$has_choices      = false;
							$has_instructions = false;
							$has_sub_fields   = false;
							$has_layouts      = false;
							
							foreach( $fields as $field ) {
								if( !empty( $field['choices'] ) )       $has_choices = true;
								if( !empty( $field['instructions'] ) )  $has_instructions = true;
								if( !empty( $field['sub_fields'] ) )    $has_sub_fields = true;
								if( !empty( $field['layouts'] ) )       $has_layouts = true;
							}
	?>
						<table class="widefat" width="100%">
							<thead>
								<tr>
									<th>Label</th>
									<th>Slug</th>
									<th>Type</th>
									<?php
										if( $has_choices )      echo '<th>Choices</th>';
										if( $has_instructions ) echo '<th>Instructions</th>';
										if( $has_sub_fields )   echo '<th>Sub Fields</th>';
										if( $has_layouts )      echo '<th>Layouts</th>';
									?>
								</tr>
							</thead>
							<tbody>
							<?php 
								foreach( $fields as $field ) {
									
									// Turn choices from an array into a simple list, if exist
									$choices = ( isset( $field['choices'] ) ) ? $this->get_choices( $field['choices'] ) : '';
									
									// Output the values
									echo "<tr>
										<th>{$field['label']}</th>
										<td>{$field['name']}</td>
										<td>{$field['type']}</td>
									";
									
									if( $has_choices ) echo "<td>$choices</td>";
									if( $has_instructions ) echo "<td>{$field['instructions']}</td>";
									
									if( $has_layouts ) {
										echo '<td>';
									
										if( isset( $field['layouts'] ) ) {
											foreach( $field['layouts'] as $id => $layout ) {
												echo "<p><strong>{$layout['label']}</strong>: {$layout['name']}</p>";
												
												if( isset( $layout['sub_fields'] ) ) {
													$this->get_sub_fields( $layout['sub_fields'] );
												}
											}
										}
										
										echo '</td>';
									}
									
									// If this field has sub-fields, do the same thing again
									if( $has_sub_fields ) {
										echo '<td>';
										
										if( isset( $field['sub_fields'] ) ) {
											$this->get_sub_fields( $field['sub_fields'] );
										}
										
										echo '</td>';
									}
								
									echo '</tr>';
								}
						echo '</tbody>
						</table>';
						}
			
					echo '</div>
				</div>';
				}
			} else {
				echo "No Advanced Custom Field groups found";
			}
		}
	?>
	</div>
<?php
		}
		
		function get_choices( $choices ) {
			$string = '';
			if( !empty( $choices ) ) {
				foreach( $choices as $key => $value ) {
					$string .= "$key : $value<br>";
				}
			}
			return rtrim( $string, '<br>' );
		}
		
		function get_sub_fields( $fields ) {
			if( isset( $fields ) ) {
				$has_choices      = false;
				$has_instructions = false;
				$has_sub_fields   = false;
				$has_layouts      = false;
				
				foreach( $fields as $field ) {
					if( !empty( $field['choices'] ) )       $has_choices = true;
					if( !empty( $field['instructions'] ) )  $has_instructions = true;
					if( !empty( $field['sub_fields'] ) )    $has_sub_fields = true;
					if( !empty( $field['layouts'] ) )       $has_layouts = true;
				}
	?>
			<table class="widefat" width="100%">
				<thead>
					<tr>
						<th>Label</th>
						<th>Slug</th>
						<th>Type</th>
						<?php
							if( $has_choices )      echo '<th>Choices</th>';
							if( $has_instructions ) echo '<th>Instructions</th>';
							if( $has_sub_fields )   echo '<th>Sub Fields</th>';
							if( $has_layouts )      echo '<th>Layouts</th>';
						?>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach( $fields as $sub_field ) {
							
							// Turn choices from an array into a simple list, if exist
							$choices = $this->get_choices( $sub_field['choices'] );

							echo "<tr>
								<th>{$sub_field['label']}</th>
								<td>{$sub_field['name']}</td>
								<td>{$sub_field['type']}</td>";
							
							if( $has_choices ) echo "<td>$choices</td>";
							if( $has_instructions ) echo "<td>{$sub_field['instructions']}</td>";
							
							if( $has_layouts ) {
								echo '<td>';
							
								if( isset( $sub_field['layouts'] ) ) {
									foreach( $sub_field['layouts'] as $id => $layout ) {
										echo "<p><strong>{$layout['label']}</strong>: {$layout['name']}</p>";
										
										if( isset( $layout['sub_fields'] ) ) {
											$this->get_sub_fields( $layout['sub_fields'] );
										}
									}
								}
								
								echo '</td>';
							}
							
							// If this field has sub-fields, do the same thing again
							if( $has_sub_fields ) {
								echo '<td>';
								
								if( isset( $sub_field['sub_fields'] ) ) {
									$this->get_sub_fields( $sub_field['sub_fields'] );
								}
								
								echo '</td>';
							}
						}
					?>
				</tbody>
			</table>
<?php
			}
		}
	}
}

$acf_debug_fields = new acf_debug_fields();