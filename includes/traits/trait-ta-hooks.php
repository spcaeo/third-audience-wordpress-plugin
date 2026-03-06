<?php
/**
 * Hooks Trait
 *
 * Provides WordPress hooks management functionality.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TA_Trait_Hooks
 *
 * Provides hooks registration and management.
 *
 * @since 1.2.0
 */
trait TA_Trait_Hooks {

	/**
	 * Registered actions.
	 *
	 * @var array
	 */
	private $registered_actions = array();

	/**
	 * Registered filters.
	 *
	 * @var array
	 */
	private $registered_filters = array();

	/**
	 * Add an action hook.
	 *
	 * @since 1.2.0
	 * @param string   $hook          The hook name.
	 * @param callable $callback      The callback.
	 * @param int      $priority      Optional. Priority. Default 10.
	 * @param int      $accepted_args Optional. Number of arguments. Default 1.
	 * @return $this
	 */
	protected function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_action( $hook, $callback, $priority, $accepted_args );

		$this->registered_actions[] = array(
			'hook'          => $hook,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $this;
	}

	/**
	 * Add a filter hook.
	 *
	 * @since 1.2.0
	 * @param string   $hook          The hook name.
	 * @param callable $callback      The callback.
	 * @param int      $priority      Optional. Priority. Default 10.
	 * @param int      $accepted_args Optional. Number of arguments. Default 1.
	 * @return $this
	 */
	protected function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook, $callback, $priority, $accepted_args );

		$this->registered_filters[] = array(
			'hook'          => $hook,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $this;
	}

	/**
	 * Remove all registered actions.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	protected function remove_all_actions() {
		foreach ( $this->registered_actions as $action ) {
			remove_action( $action['hook'], $action['callback'], $action['priority'] );
		}
		$this->registered_actions = array();
	}

	/**
	 * Remove all registered filters.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	protected function remove_all_filters() {
		foreach ( $this->registered_filters as $filter ) {
			remove_filter( $filter['hook'], $filter['callback'], $filter['priority'] );
		}
		$this->registered_filters = array();
	}

	/**
	 * Remove all registered hooks.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function unregister_hooks() {
		$this->remove_all_actions();
		$this->remove_all_filters();
	}

	/**
	 * Get all registered actions.
	 *
	 * @since 1.2.0
	 * @return array The registered actions.
	 */
	public function get_registered_actions() {
		return $this->registered_actions;
	}

	/**
	 * Get all registered filters.
	 *
	 * @since 1.2.0
	 * @return array The registered filters.
	 */
	public function get_registered_filters() {
		return $this->registered_filters;
	}

	/**
	 * Add action that runs only once.
	 *
	 * @since 1.2.0
	 * @param string   $hook          The hook name.
	 * @param callable $callback      The callback.
	 * @param int      $priority      Optional. Priority. Default 10.
	 * @param int      $accepted_args Optional. Number of arguments. Default 1.
	 * @return $this
	 */
	protected function add_action_once( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$wrapped_callback = function ( ...$args ) use ( $hook, $callback, $priority ) {
			remove_action( $hook, __FUNCTION__, $priority );
			return call_user_func_array( $callback, $args );
		};

		return $this->add_action( $hook, $wrapped_callback, $priority, $accepted_args );
	}

	/**
	 * Add filter that runs only once.
	 *
	 * @since 1.2.0
	 * @param string   $hook          The hook name.
	 * @param callable $callback      The callback.
	 * @param int      $priority      Optional. Priority. Default 10.
	 * @param int      $accepted_args Optional. Number of arguments. Default 1.
	 * @return $this
	 */
	protected function add_filter_once( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$wrapped_callback = function ( ...$args ) use ( $hook, $callback, $priority ) {
			remove_filter( $hook, __FUNCTION__, $priority );
			return call_user_func_array( $callback, $args );
		};

		return $this->add_filter( $hook, $wrapped_callback, $priority, $accepted_args );
	}

	/**
	 * Schedule an action for later execution.
	 *
	 * @since 1.2.0
	 * @param string $hook      The hook name.
	 * @param int    $timestamp When to run.
	 * @param array  $args      Optional. Arguments to pass.
	 * @return bool Whether the event was scheduled.
	 */
	protected function schedule_action( $hook, $timestamp, $args = array() ) {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			return wp_schedule_single_event( $timestamp, $hook, $args );
		}
		return false;
	}

	/**
	 * Schedule a recurring action.
	 *
	 * @since 1.2.0
	 * @param string $hook       The hook name.
	 * @param int    $timestamp  First run timestamp.
	 * @param string $recurrence The recurrence (hourly, daily, twicedaily).
	 * @param array  $args       Optional. Arguments to pass.
	 * @return bool Whether the event was scheduled.
	 */
	protected function schedule_recurring_action( $hook, $timestamp, $recurrence = 'daily', $args = array() ) {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			return wp_schedule_event( $timestamp, $recurrence, $hook, $args );
		}
		return false;
	}

	/**
	 * Unschedule an action.
	 *
	 * @since 1.2.0
	 * @param string $hook The hook name.
	 * @param array  $args Optional. Arguments.
	 * @return void
	 */
	protected function unschedule_action( $hook, $args = array() ) {
		wp_clear_scheduled_hook( $hook, $args );
	}
}
