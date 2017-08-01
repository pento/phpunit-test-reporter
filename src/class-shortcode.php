<?php

namespace WPUTR;

use WP_Query;

class Shortcode {

	/**
	 * Register the shortcode.
	 */
	public static function action_init_register() {
		add_shortcode( 'wputr-results', array( __CLASS__, 'render_results' ) );
	}

	/**
	 * Render the test results.
	 */
	public static function render_results( $atts ) {

		// Shortcodes must return, not echo.
		// But, echo'ing is easier than concatenating strings.
		ob_start();

		echo '<h3>PHPUnit Test Results</h3>' . PHP_EOL;

		$query_args = array(
			'posts_per_page'   => 10,
			'post_type'        => 'result',
			'post_parent'      => 0,
			'orderby'          => 'post_name',
			'order'            => 'DESC',
		);
		$rev_query = new WP_Query( $query_args );
		if ( empty( $rev_query->posts ) ) {
			echo '<p>No revisions found</p>';
			return ob_get_clean();
		}
		?>
		<style>
			.wputr-status-badge {
				color: #FFF;
				display: inline-block;
				padding-left: 8px;
				padding-right: 8px;
				padding-top: 3px;
				padding-bottom: 3px;
				border-radius: 3px;
				font-weight: normal;
			}
			.wputr-status-badge-passed {
				background-color: #39BC00;
			}
			.wputr-status-badge-failed {
				background-color: #CD543A;
			}
			.wputr-status-badge-errored {
				background-color: #909090;
			}
		</style>
		<table>
			<thead>
				<tr>
					<th style="width:100px">Revision</th>
					<th style="width:100px">Status</th>
					<th>Host</th>
					<th>PHP Version</th>
					<th>PHP Extensions</th>
					<th>Database Version</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$total_cols = 6;
				foreach( $rev_query->posts as $revision ) :
					$rev_id = (int) $revision->post_name;
				?>
					<tr>
						<th><a href="<?php echo esc_url( sprintf( 'https://core.trac.wordpress.org/changeset/%d', $rev_id ) ); ?>"><?php echo (int) $rev_id; ?></a></th>
						<th colspan="<?php echo (int) $total_cols - 1; ?>"><?php echo wp_kses_post( apply_filters( 'the_title', $revision->post_title ) ); ?></th>
					</tr>
					<?php
					$query_args = array(
						'posts_per_page'   => 10,
						'post_type'        => 'result',
						'post_parent'      => $rev_id,
						'orderby'          => 'post_title',
						'order'            => 'ASC',
					);
					$report_query = new WP_Query( $query_args );
					if ( ! empty( $report_query->posts ) ) :
						foreach( $report_query->posts as $report ) :
							$status = 'Passed';
							$host = 'DreamHost';
							$php_version = 'PHP 5.6';
							$mysql_version = 'MySQL 1234';
							?>
						<tr>
							<td></td>
							<td><a href="#" class="<?php echo esc_attr( 'wputr-status-badge wputr-status-badge-' . strtolower( $status ) ); ?>"><?php echo esc_html( $status ); ?></a></td>
							<td><?php echo esc_html( $host ); ?></td>
							<td><?php echo esc_html( $php_version ); ?></td>
							<td>Imagick, pcre</td>
							<td><?php echo esc_html( $mysql_version ); ?></td>
						</tr>
					<?php
						endforeach;
					else : ?>
						<tr>
							<td></td>
							<td colspan="<?php echo (int) $total_cols - 1; ?>">
								No reports for changeset.
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

}

add_action( 'init', array( 'WPUTR\Shortcode', 'action_init_register' ) );
