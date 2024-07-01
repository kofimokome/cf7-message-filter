<?php

namespace km_message_filter;


use KMValidator;

class StatisticsModule extends Module {

	private static $instance;

	public function __construct() {
		parent::__construct();
		self::$instance = $this;
	}

	/**
	 * @since v1.4.9
	 * @author kofimokome
	 */
	public static function getInstance(): StatisticsModule {
		return self::$instance;
	}

	/**
	 * @since v1.6.0
	 * @author kofimokome
	 * Get the most frequent spam word filters
	 */
	public function frequentWords(): array {
		$words = json_decode( get_option( 'kmcfmf_word_stats' ), true );
		uasort( $words, function ( $a, $b ) {
			return $a < $b ? 1 : - 1;
		} );

		if ( sizeof( $words ) > 16 ) {
			$top        = array_slice( $words, 0, 16 );
			$bottom     = array_slice( $words, 16 );
			$bottom_sum = array_reduce( $bottom, function ( $carry, $item ) {
				return $carry + $item;
			}, 0 );

			$top["other words"] = $bottom_sum;
			$words              = $top;
		}

		return $words;
	}

	/**
	 * @since v1.6.0
	 * @author kofimokome
	 * Get the most frequent spam email filters
	 */
	public function frequentEmails(): array {
		$emails = json_decode( get_option( 'kmcfmf_email_stats' ), true );
		uasort( $emails, function ( $a, $b ) {
			return $a < $b ? 1 : - 1;
		} );

		if ( sizeof( $emails ) > 16 ) {
			$top        = array_slice( $emails, 0, 16 );
			$bottom     = array_slice( $emails, 16 );
			$bottom_sum = array_reduce( $bottom, function ( $carry, $item ) {
				return $carry + $item;
			}, 0 );

			$top["other emails"] = $bottom_sum;
			$emails              = $top;
		}

		return $emails;
	}

	/**
	 * @since v1.4.9
	 * @author kofimokome
	 * Get the statistics from the database
	 */
	public function getStats() {
		$validator = KMValidator::make( [ 'mode' => 'required', ], $_POST );

		if ( $validator->validate() ) {
			$mode = sanitize_text_field( $_POST['mode'] );
			switch ( $mode ) {
				case '30d':
//					$stats = Statistic::where( 'date', '>=', date( 'Y-m-d', strtotime( '-30 days' ) ) )->get();
					$stats = Statistic::orderBy( 'date', 'desc' )->take( 30 );
					$data  = self::formatData( $stats, '30d' );
					break;
				case '1y':
					$data = self::getYearlyStats();
//					$stats = Statistic::where( 'date', '>=', date( 'Y-m-d', strtotime( '-1 year' ) ) )->get();
					break;
				default:
					$stats = Statistic::orderBy( 'date', 'desc' )->take( 7 );
//					$stats = Statistic::where( 'date', '>=', date( 'Y-m-d', strtotime( '-7 days' ) ) )->get();
					$data = self::formatData( $stats, '7d' );
					break;
			}
			wp_send_json_success( $data );
		}
		wp_die();

	}

	/**
	 * @since v1.4.9
	 * @author kofimokome
	 */
	private function formatData( $stats, $mode = '7d' ): array {
		$x_axis = [];
		$y_axis = [];
		foreach ( $stats as $stat ) {
			$x_axis[] = $mode == '1y' ? date( 'M Y', strtotime( $stat->date ) ) : date( 'd M', strtotime( $stat->date ) );
			$y_axis[] = $stat->messages_blocked;
		}
		$data = [
			'x_axis' => array_reverse( $x_axis ),
			'y_axis' => array_reverse( $y_axis ),
		];

		return $data;
	}

	/**
	 * @since v1.4.9
	 * @author kofimokome
	 */
	public function getYearlyStats(): array {
		$stats = Statistic::groupBy( 'MONTH(date),YEAR(date)' )->paginate( 12, 1 )->orderBy( 'date', 'desc' )->get( [
			"DATE_FORMAT(date, '%d-%m-%Y') AS full_date",
			"DATE_FORMAT(date, '%m-%Y') AS stat_month",
			'SUM(messages_blocked) AS count'
		] );
		$stats = $stats['data'];
		foreach ( $stats as $stat ) {
			$stat->messages_blocked = $stat->count;
			$stat->date             = $stat->full_date;
		}

		return self::formatData( $stats, '1y' );
	}

	protected function addActions() {
		parent::addActions();
		add_action( 'wp_ajax_kmcf7_get_stats', [ $this, 'getStats' ] );

	}

}
