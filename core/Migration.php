<?php

namespace kmcf7_message_filter;

class Migration {
	private $table_name;
	private $columns;
	private static $migrations = array();
	private $is_update;
	private static $revisions = 0;
	private $revision_id = 0;

	public function __construct( $table_name, $is_update = false ) {
		$this->table_name = KMCF7MS_TABLE_PREFIX . $table_name;
		$this->is_update  = $is_update;
		$this->columns    = array();
		if ( $is_update ) {
			$this->revision_id = self::$revisions + 1;
			self::$revisions ++;
		}
		array_push( self::$migrations, $this );

		return $this;
	}

	/**
	 * @returns Column
	 */
	public function string( $name, $size = 255 ) {
		$column = new Column( $name, array( 'VARCHAR(' . $size . ')' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;

	}

	/**
	 * @returns Column
	 */
	public function text( $name ) {
		$column = new Column( $name, array( 'TEXT' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}

	/**
	 * @returns Column
	 */
	public function integer( $name ) {
		$column = new Column( $name, array( 'INTEGER', 'SIGNED' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}

	/**
	 * @returns Column
	 */
	public function bigInt( $name ) {
		$column = new Column( $name, array( 'BIGINT', 'SIGNED' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}

	/**
	 * @returns Column
	 */
	public function boolean( $name ) {
		$column = new Column( $name, array( 'BOOL' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}

	/**
	 * @returns Column
	 */
	public function id() {
		$column = new Column( 'id', array(
			'BIGINT',
			'UNSIGNED',
			'AUTO_INCREMENT',
			'PRIMARY KEY'
		), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}

	public function timestamps() {
		$this->dateTime( 'created_at' )->nullable();
		$this->dateTime( 'updated_at' )->nullable();
	}

	public function softDelete() {
		$this->boolean( 'deleted' )->nullable()->default( 0 );
	}

	/*public function longText( string $name ) {
		$column = new Column( $name, array( 'TEXT' ) );
		array_push( $this->columns, $column );

		return $column;
	}*/

	/**
	 * @returns Column
	 */
	public function dateTime( $name ) {
		$column = new Column( $name, array( 'DATETIME' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}

	/**
	 * @returns Column
	 */
	public function date( $name ) {
		$column = new Column( $name, array( 'DATE' ), array( 'is_update' => $this->is_update ) );
		array_push( $this->columns, $column );

		return $column;
	}


	public function dropColumn( $name ) {
		$column = new Column( $name, array(), array( 'is_delete' => true ) );
		array_push( $this->columns, $column );
	}

	/*public function change( $name, $new_name ): Column {
		$column = new Column( $name, array(), array( 'new_name' => $new_name, 'is_change' => true ) );
		array_push( $this->columns, $column );

		return $column;
	}*/

	public function rename( $name, $new_name ) {
		$column = new Column( $name, array(), array( 'new_name' => $new_name, 'is_rename' => true ) );
		array_push( $this->columns, $column );
	}

	/**
	 * checks if a migration has a column
	 *
	 * @param string $field
	 *
	 * @return boolean
	 * @since v1.0.0
	 */
	public function hasColumn( $field ) {
		foreach ( $this->columns as $column ) {
			if ( $column->getName() == $field ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @since v1.0.0
	 * Creates a table
	 */
	public function up() {
		global $wpdb;
		if ( $this->is_update ) {
			$this->update();
		} else {
			$query = "CREATE TABLE IF NOT EXISTS `" . $this->table_name . "`(";
			foreach ( $this->columns as $column ) {
				$query .= $column->toString() . ',';
			}
			$query = rtrim( $query, ", " );
			$query .= ')';
			$wpdb->query( $query );

		}
	}

	/**
	 * @since v1.0.0
	 * Updates a table
	 */
	public function update() {
		global $wpdb;
		$last_revision_run = get_option( KMCF7MS_TABLE_PREFIX . '_last_revision', 0 );
		if ( $last_revision_run < $this->revision_id ) {
			foreach ( $this->columns as $column ) {
				$query = "ALTER TABLE `" . $this->table_name . '`' . $column->toString();
				$wpdb->query( $query );
			}
			update_option( KMCF7MS_TABLE_PREFIX . '_last_revision', $this->revision_id );
		}
	}

	public function getTableName() {
		return $this->table_name;
	}

	/**
	 * @since v1.0.0
	 * Deletes a table
	 */
	public function down() {
		global $wpdb;
		if ( ! $this->is_update ) {
			$wpdb->query( "DROP TABLE IF EXISTS " . $this->table_name );
		}
	}


	/**
	 * @param string $table
	 * @param string $field
	 * @param string $type
	 * @param string $default
	 *
	 * @return void
	 */
	public static function addColumn( $table, $field, $type, $default = '' ) {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table' AND column_name = '$field'" );
		if ( empty( $results ) ) {
			$default_string = is_numeric( $default ) ? "DEFAULT $default" : "DEFAULT " . "'$default'";
			$wpdb->query( "ALTER TABLE  {$table}  ADD  {$field}  {$type}  NOT NULL {$default_string}" );
		}
	}

	/**
	 * @since v1.0.0
	 * Run all migrations
	 */
	public static function runMigrations() {
		foreach ( self::$migrations as $migration ) {
			$migration->up();
		}
	}

	/**
	 * @since v1.0.0
	 * Run update migrations
	 */
	public static function runUpdateMigrations() {
		$migrations = array_filter( self::$migrations, function ( $migration ) {
			return $migration->is_update;
		} );
		foreach ( $migrations as $migration ) {
			$migration->update();
		}
	}

	/**
	 * @param string $table_name Name of the table
	 *
	 * @since v1.0.0
	 * Creates a table
	 */
	public static function runMigration( $table_name ) {
		$table_name = KMCF7MS_TABLE_PREFIX . trim( $table_name );
		foreach ( self::$migrations as $migration ) {
			if ( $migration->getTableName() == $table_name ) {
				$migration->up();
			}
		}
	}

	/**
	 * @param string $table_name Name of the table without the prefix
	 *
	 * @since v1.0.0
	 * Delete a particular table
	 */
	public static function drop( $table_name ) {
		$table_name = KMCF7MS_TABLE_PREFIX . trim( $table_name );
		foreach ( self::$migrations as $migration ) {
			if ( $migration->getTableName() == $table_name ) {
				$migration->down();
			}
		}
	}

	/**
	 * @since v1.0.0
	 * Deletes all tables
	 */
	public static function dropAll() {
		foreach ( self::$migrations as $migration ) {
			$migration->down();
		}
		update_option( KMCF7MS_TABLE_PREFIX . '_last_revision', 0 );

	}


	/**
	 * @since v1.0.0
	 * Deletes and recreate database tables
	 */
	public static function refresh() {
		self::dropAll();
		self::runMigrations();
	}

	/**
	 * Returns a migration instance
	 * @since v1.0.0
	 */
	public static function getMigration( $table_name, $is_full_table_name = false ) {
		$table_name = $is_full_table_name ? $table_name : KMCF7MS_TABLE_PREFIX . trim( $table_name );
		foreach ( self::$migrations as $migration ) {
			if ( $migration->getTableName() == $table_name ) {
				return $migration;
			}
		}

		return false;
	}
}

class Column {
	private $name;
	private $attributes;
	private $is_update;
	private $is_delete;
	private $is_change;
	private $is_rename;
	private $new_name;

	public function __construct( $name, $attributes = array(), $extras = array() ) {
		$this->name      = $name;
		$default_extras  = array(
			'is_update' => false,
			'is_delete' => false,
			'is_change' => false,
			'is_rename' => false,
			'new_name'  => ''
		);
		$extras          = array_merge( $default_extras, $extras );
		$this->is_update = $extras['is_update'];
		$this->is_rename = $extras['is_rename'];
		$this->is_delete = $extras['is_delete'];
		$this->is_change = $extras['is_change'];
		$this->new_name  = $extras['new_name'];

		array_push( $attributes, 'NOT NULL' );
		$this->attributes = $attributes;
	}

	/**
	 * @returns Column
	 */
	public function nullable() {
		if ( ( $key = array_search( 'NOT NULL', $this->attributes ) ) !== false ) {
			unset( $this->attributes[ $key ] );
			$this->attributes = array_values( $this->attributes );
		}
		array_push( $this->attributes, 'NULL' );

		return $this;
	}

	/**
	 * @returns Column
	 */
	public function unsigned() {
		if ( ( $key = array_search( 'SIGNED', $this->attributes ) ) !== false ) {
			unset( $this->attributes[ $key ] );
			$this->attributes = array_values( $this->attributes );
		}
		array_splice( $this->attributes, 1, 0, 'UNSIGNED' );

		return $this;
	}

	/**
	 * @returns Column
	 */
	public function primary() {
		array_push( $this->attributes, 'PRIMARY KEY' );

		return $this;
	}

	/**
	 * @returns Column
	 */
	public function autoIncrement() {
		array_push( $this->attributes, 'AUTO_INCREMENT' );

		return $this;
	}

	/**
	 * @returns Column
	 */
	public function setDefault( $value ) {
		array_push( $this->attributes, 'DEFAULT' );
		array_push( $this->attributes, $value );

		return $this;
	}

	public function toString() {
		$attributes = implode( ' ', $this->attributes );

		if ( $this->is_delete ) {
			return ' DROP COLUMN `' . $this->name . '`';
		} else if ( $this->is_update ) {
			return ' ADD `' . $this->name . '` ' . $attributes;
		} else if ( $this->is_change ) {
			return ' CHANGE `' . $this->name . '` `' . $this->new_name . '` ' . $attributes;
		} else if ( $this->is_rename ) {
			return ' RENAME COLUMN `' . $this->name . '` TO `' . $this->new_name . '`';
		} else {
			return '`' . $this->name . '` ' . $attributes;
		}
	}

	public function getName() {
		return $this->name;
	}
}
